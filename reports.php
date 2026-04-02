<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';
require_auth();
$page_title = 'รายงาน';
require_once __DIR__ . '/includes/header.php';

// Query 12 months income/expense for line chart
$pdo   = get_db();
$year  = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
$monthly = array();
for ($m = 1; $m <= 12; $m++) {
    $mon = str_pad($m, 2, '0', STR_PAD_LEFT);
    $stmt = $pdo->prepare("SELECT
        COALESCE(SUM(CASE WHEN type='income'  THEN amount ELSE 0 END),0) AS income,
        COALESCE(SUM(CASE WHEN type='expense' THEN amount ELSE 0 END),0) AS expense
        FROM transactions WHERE strftime('%Y-%m',date)=?");
    $stmt->execute(array($year . '-' . $mon));
    $row = $stmt->fetch();
    $monthly[] = array('month' => $m, 'income' => (float)$row['income'], 'expense' => (float)$row['expense']);
}

// Category breakdown current month for bar chart
$cur_month = $year . '-' . date('m');
$cat_stmt = $pdo->prepare("SELECT c.name, c.icon, c.color, COALESCE(SUM(t.amount),0) AS amount
    FROM transactions t JOIN categories c ON t.category_id=c.id
    WHERE t.type='expense' AND strftime('%Y-%m',t.date)=?
    GROUP BY c.id ORDER BY amount DESC LIMIT 8");
$cat_stmt->execute(array($cur_month));
$cat_data = $cat_stmt->fetchAll();

$month_labels = array('ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.');
?>

<div x-data="{}" x-cloak>

  <!-- Year Picker -->
  <div class="flex items-center justify-between mb-4">
    <a href="reports.php?year=<?php echo $year - 1; ?>" class="btn btn-ghost btn-sm btn-circle">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
    </a>
    <p class="font-bold text-base"><?php echo $year; ?></p>
    <a href="reports.php?year=<?php echo $year + 1; ?>" class="btn btn-ghost btn-sm btn-circle">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    </a>
  </div>

  <!-- Annual Summary -->
  <?php
  $total_inc = array_sum(array_column($monthly, 'income'));
  $total_exp = array_sum(array_column($monthly, 'expense'));
  ?>
  <div class="flex gap-2 mb-4 text-xs">
    <div class="flex-1 bg-success/10 rounded-lg px-3 py-2 text-center">
      <p class="text-success/70">รายรับรวม</p>
      <p class="font-bold text-success"><?php echo format_money($total_inc); ?></p>
    </div>
    <div class="flex-1 bg-error/10 rounded-lg px-3 py-2 text-center">
      <p class="text-error/70">รายจ่ายรวม</p>
      <p class="font-bold text-error"><?php echo format_money($total_exp); ?></p>
    </div>
    <div class="flex-1 bg-base-200 rounded-lg px-3 py-2 text-center">
      <p class="text-base-content/50">สุทธิ</p>
      <p class="font-bold <?php echo ($total_inc - $total_exp) >= 0 ? 'text-success' : 'text-error'; ?>">
        <?php echo format_money($total_inc - $total_exp); ?></p>
    </div>
  </div>

  <!-- Line Chart: 12 months -->
  <div class="card bg-base-100 border border-base-200 mb-4">
    <div class="card-body p-3">
      <p class="text-sm font-semibold mb-2">รายรับ-จ่ายรายเดือน</p>
      <canvas id="yearLineChart" height="160"></canvas>
    </div>
  </div>

  <!-- Bar Chart: Category breakdown -->
  <?php if (count($cat_data) > 0): ?>
  <div class="card bg-base-100 border border-base-200 mb-4">
    <div class="card-body p-3">
      <p class="text-sm font-semibold mb-2">รายจ่ายตามหมวดหมู่ (เดือนนี้)</p>
      <canvas id="catBarChart" height="<?php echo count($cat_data) * 32 + 40; ?>"></canvas>
    </div>
  </div>
  <?php endif; ?>

  <!-- Doughnut Chart -->
  <?php if (count($cat_data) > 0): ?>
  <div class="card bg-base-100 border border-base-200 mb-4">
    <div class="card-body p-3">
      <p class="text-sm font-semibold mb-2">สัดส่วนรายจ่าย (เดือนนี้)</p>
      <div class="max-w-[220px] mx-auto">
        <canvas id="catPieChart"></canvas>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Monthly Table -->
  <div class="card bg-base-100 border border-base-200">
    <div class="card-body p-3">
      <p class="text-sm font-semibold mb-3">สรุปรายเดือน <?php echo $year; ?></p>
      <?php $max_value = max(array_merge(array_column($monthly, 'income'), array_column($monthly, 'expense'))); ?>
      <?php if (!$max_value) { $max_value = 1; } ?>
      <div class="space-y-1">
        <?php foreach ($monthly as $i => $m): ?>
        <?php $net = $m['income'] - $m['expense']; ?>
        <div class="flex items-center gap-2 py-1.5 <?php echo $m['income'] == 0 && $m['expense'] == 0 ? 'opacity-30' : ''; ?>">
          <span class="text-xs text-base-content/50 w-10 shrink-0"><?php echo $month_labels[$i]; ?></span>
          <div class="flex-1 flex gap-1 min-w-0">
            <div class="flex-1 bg-success/20 rounded h-1.5 overflow-hidden" style="max-width:50%">
              <div class="h-full bg-success rounded" style="width:<?php echo min($m['income']/$max_value*100,100); ?>%"></div>
            </div>
            <div class="flex-1 bg-error/20 rounded h-1.5 overflow-hidden" style="max-width:50%">
              <div class="h-full bg-error rounded" style="width:<?php echo min($m['expense']/$max_value*100,100); ?>%"></div>
            </div>
          </div>
          <span class="text-xs font-semibold w-20 text-right shrink-0 <?php echo $net >= 0 ? 'text-success' : 'text-error'; ?>">
            <?php echo ($net >= 0 ? '+' : '') . number_format($net, 0); ?>
          </span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

</div>

<script>
// ─── Report Charts ───────────────────────────────────────────────
window.addEventListener('load', function() {
  if (typeof renderLineChart !== 'function') {
    console.error('renderLineChart is not available');
    return;
  }

  var monthlyData = <?php echo json_encode($monthly); ?>;
  var monthLabels = <?php echo json_encode($month_labels); ?>;

  renderLineChart(
    'yearLineChart',
    monthLabels,
    monthlyData.map(function(m) { return m.income; }),
    monthlyData.map(function(m) { return m.expense; })
  );

  <?php if (count($cat_data) > 0): ?>
  if (typeof renderBarChart === 'function') {
    var catLabels = <?php echo json_encode(array_column($cat_data, 'name')); ?>;
    var catAmounts = <?php echo json_encode(array_column($cat_data, 'amount')); ?>;
    var catColors  = <?php echo json_encode(array_column($cat_data, 'color')); ?>;

    renderBarChart('catBarChart', catLabels, catAmounts, catColors);
    renderPieChart('catPieChart', catLabels, catAmounts, catColors);
  }
  <?php endif; ?>
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
