<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';
require_auth();
$page_title = 'หน้าหลัก';
require_once __DIR__ . '/includes/header.php';
?>

<div x-data="dashboard()" x-init="init()" x-cloak>

  <!-- ── Month Picker ───────────────────────────────────────────── -->
  <div class="flex items-center justify-between mb-4">
    <div class="flex items-center gap-1">
      <button @click="prevMonth()" class="btn btn-ghost btn-sm btn-circle">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
      </button>
      <input type="month" x-model="month" @change="loadData()"
             class="input input-sm input-bordered w-36 text-center text-sm font-semibold">
      <button @click="nextMonth()" class="btn btn-ghost btn-sm btn-circle">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
        </svg>
      </button>
    </div>
    <button @click="loadData()" class="btn btn-ghost btn-sm btn-circle" :class="loading ? 'loading' : ''">
      <svg x-show="!loading" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
      </svg>
    </button>
  </div>

  <!-- ── Loading Skeleton ───────────────────────────────────────── -->
  <template x-if="loading">
    <div class="space-y-4">
      <div class="grid grid-cols-2 gap-3">
        <div class="skeleton h-20 rounded-xl"></div>
        <div class="skeleton h-20 rounded-xl"></div>
        <div class="skeleton h-20 rounded-xl"></div>
        <div class="skeleton h-20 rounded-xl"></div>
      </div>
      <div class="skeleton h-10 rounded-xl"></div>
      <div class="skeleton h-48 rounded-xl"></div>
      <div class="skeleton h-36 rounded-xl"></div>
      <div class="space-y-2">
        <div class="skeleton h-14 rounded-xl"></div>
        <div class="skeleton h-14 rounded-xl"></div>
        <div class="skeleton h-14 rounded-xl"></div>
      </div>
    </div>
  </template>

  <!-- ── Content (after load) ───────────────────────────────────── -->
  <div x-show="!loading" class="space-y-4">

    <!-- Summary Cards 2×2 -->
    <div class="grid grid-cols-2 gap-3">
      <!-- Income -->
      <div class="card bg-success/10 border border-success/20">
        <div class="card-body p-3">
          <p class="text-xs text-success/80 font-medium">รายรับ</p>
          <p class="text-lg font-bold text-success leading-tight mt-1" x-text="fmt(stats.income)">฿0</p>
        </div>
      </div>
      <!-- Expense -->
      <div class="card bg-error/10 border border-error/20">
        <div class="card-body p-3">
          <p class="text-xs text-error/80 font-medium">รายจ่าย</p>
          <p class="text-lg font-bold text-error leading-tight mt-1" x-text="fmt(stats.expense)">฿0</p>
        </div>
      </div>
      <!-- Net -->
      <div class="card border"
           :class="stats.net >= 0 ? 'bg-primary/10 border-primary/20' : 'bg-error/10 border-error/20'">
        <div class="card-body p-3">
          <p class="text-xs font-medium" :class="stats.net >= 0 ? 'text-primary/80' : 'text-error/80'">ยอดสุทธิ</p>
          <p class="text-lg font-bold leading-tight mt-1"
             :class="stats.net >= 0 ? 'text-primary' : 'text-error'"
             x-text="fmt(stats.net)">฿0</p>
        </div>
      </div>
      <!-- Total Balance -->
      <div class="card bg-base-200 border border-base-300">
        <div class="card-body p-3">
          <p class="text-xs text-base-content/60 font-medium">รวมทุกบัญชี</p>
          <p class="text-lg font-bold text-base-content leading-tight mt-1" x-text="fmt(stats.total_balance)">฿0</p>
        </div>
      </div>
    </div>

    <!-- Net Progress Bar -->
    <div class="card bg-base-100 border border-base-200">
      <div class="card-body p-3">
        <div class="flex justify-between text-xs text-base-content/60 mb-2">
          <span>รายรับ</span>
          <span x-text="stats.income > 0 ? Math.round(stats.expense/stats.income*100) + '% ของรายรับ' : 'ยังไม่มีข้อมูล'"></span>
          <span>รายจ่าย</span>
        </div>
        <div class="w-full bg-success/20 rounded-full h-2.5 overflow-hidden">
          <div class="h-full rounded-full transition-all duration-500"
               :class="(stats.income > 0 && stats.expense/stats.income >= 1) ? 'bg-error' : 'bg-success'"
               :style="'width:' + (stats.income > 0 ? Math.min(stats.expense/stats.income*100,100) : 0) + '%'">
          </div>
        </div>
      </div>
    </div>

    <!-- Line Chart: รายรับ-จ่ายรายวัน -->
    <div class="card bg-base-100 border border-base-200">
      <div class="card-body p-3">
        <p class="text-sm font-semibold mb-2">รายรับ vs รายจ่าย</p>
        <template x-if="hasChartData()">
          <canvas id="lineChart" height="140"></canvas>
        </template>
        <template x-if="!hasChartData()">
          <div class="flex flex-col items-center justify-center py-8 text-base-content/30">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            <p class="text-xs">ไม่มีข้อมูลในเดือนนี้</p>
          </div>
        </template>
      </div>
    </div>

    <!-- Doughnut Chart: สัดส่วนรายจ่าย -->
    <div class="card bg-base-100 border border-base-200">
      <div class="card-body p-3">
        <p class="text-sm font-semibold mb-2">สัดส่วนรายจ่าย</p>
        <template x-if="stats.category_pie && stats.category_pie.length > 0">
          <div class="max-w-[220px] mx-auto">
            <canvas id="pieChart" height="220"></canvas>
          </div>
        </template>
        <template x-if="!stats.category_pie || stats.category_pie.length === 0">
          <div class="flex flex-col items-center justify-center py-8 text-base-content/30">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
            </svg>
            <p class="text-xs">ยังไม่มีรายจ่ายในเดือนนี้</p>
          </div>
        </template>
      </div>
    </div>

    <!-- Top 5 Categories -->
    <template x-if="stats.top5 && stats.top5.length > 0">
      <div class="card bg-base-100 border border-base-200">
        <div class="card-body p-3">
          <p class="text-sm font-semibold mb-3">หมวดหมู่ที่ใช้มากสุด</p>
          <div class="space-y-2">
            <template x-for="(cat, i) in stats.top5" :key="i">
              <div class="flex items-center gap-2">
                <span class="text-base w-6 text-center" x-text="cat.icon"></span>
                <div class="flex-1 min-w-0">
                  <div class="flex justify-between text-xs mb-0.5">
                    <span class="truncate font-medium" x-text="cat.name"></span>
                    <span class="text-error font-semibold ml-2 shrink-0" x-text="fmt(cat.amount)"></span>
                  </div>
                  <div class="w-full bg-base-200 rounded-full h-1.5">
                    <div class="h-1.5 rounded-full transition-all duration-500"
                         :style="'width:' + (stats.top5[0].amount > 0 ? cat.amount/stats.top5[0].amount*100 : 0) + '%; background:' + (cat.color || '#6366f1')"></div>
                  </div>
                </div>
              </div>
            </template>
          </div>
        </div>
      </div>
    </template>

    <!-- Recent Transactions -->
    <div class="card bg-base-100 border border-base-200">
      <div class="card-body p-3">
        <div class="flex items-center justify-between mb-3">
          <p class="text-sm font-semibold">รายการล่าสุด</p>
          <a href="transactions.php" class="text-xs text-primary font-medium">ดูทั้งหมด →</a>
        </div>

        <!-- Empty State -->
        <template x-if="!stats.recent || stats.recent.length === 0">
          <div class="flex flex-col items-center justify-center py-8 text-base-content/30">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <p class="text-xs mb-3">ยังไม่มีรายการในเดือนนี้</p>
            <button @click="openSheet()" class="btn btn-primary btn-sm">+ บันทึกรายการแรก</button>
          </div>
        </template>

        <!-- Transaction Cards -->
        <div class="divide-y divide-base-200">
          <template x-for="t in (stats.recent || [])" :key="t.id">
            <div class="tx-card flex items-center gap-3 py-3">
              <!-- Icon -->
              <div class="w-9 h-9 rounded-full flex items-center justify-center shrink-0 text-base"
                   :class="t.type === 'income' ? 'bg-success/15' : t.type === 'expense' ? 'bg-error/15' : 'bg-info/15'">
                <span x-text="t.category_icon || (t.type === 'transfer' ? '↔' : '💰')"></span>
              </div>
              <!-- Info -->
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium truncate" x-text="t.category_name || (t.type === 'transfer' ? 'โอนเงิน' : 'รายการ')"></p>
                <p class="text-xs text-base-content/50 truncate" x-text="(t.account_name || '') + (t.note ? ' · ' + t.note : '')"></p>
              </div>
              <!-- Amount + Date -->
              <div class="text-right shrink-0">
                <p class="text-sm font-bold"
                   :class="t.type === 'income' ? 'text-success' : t.type === 'expense' ? 'text-error' : 'text-info'"
                   x-text="(t.type === 'income' ? '+' : t.type === 'expense' ? '-' : '') + fmt(t.amount)"></p>
                <p class="text-xs text-base-content/40" x-text="formatDate(t.date)"></p>
              </div>
            </div>
          </template>
        </div>

      </div>
    </div>

  </div><!-- /content -->
</div><!-- /dashboard -->


<!-- ═══════════════════════════════════════════════════════════════
     ADD TRANSACTION BOTTOM SHEET
═══════════════════════════════════════════════════════════════════ -->
<div x-data="addTxSheet()" x-cloak id="add-tx-root">

  <!-- Overlay -->
  <div x-show="open" class="sheet-overlay" @click="close()"></div>

  <!-- Sheet -->
  <div x-show="open" class="sheet">
    <div class="sheet-handle"></div>

    <!-- Header -->
    <div class="flex items-center justify-between px-4 py-3 border-b border-base-200">
      <h3 class="font-bold text-base">บันทึกรายการ</h3>
      <button @click="close()" class="btn btn-ghost btn-sm btn-circle">✕</button>
    </div>

    <!-- Type Selector -->
    <div class="flex gap-2 px-4 pt-4 pb-2">
      <button @click="form.type='expense'"
              class="type-chip type-chip-expense flex-1 justify-center"
              :class="form.type==='expense' ? 'active-expense' : ''">
        <span>💸</span> รายจ่าย
      </button>
      <button @click="form.type='income'"
              class="type-chip type-chip-income flex-1 justify-center"
              :class="form.type==='income' ? 'active-income' : ''">
        <span>💰</span> รายรับ
      </button>
      <button @click="form.type='transfer'"
              class="type-chip type-chip-transfer flex-1 justify-center"
              :class="form.type==='transfer' ? 'active-transfer' : ''">
        <span>↔</span> โอน
      </button>
    </div>

    <!-- Amount -->
    <div class="px-4 py-3 border-b border-base-200">
      <label class="text-xs text-base-content/50 font-medium">จำนวนเงิน</label>
      <div class="flex items-center mt-1">
        <span class="text-2xl font-bold text-base-content/40 mr-2">฿</span>
        <input type="number" inputmode="decimal" placeholder="0.00" x-model="form.amount"
               class="amount-input text-2xl"
               :class="form.type==='income' ? 'text-success' : form.type==='expense' ? 'text-error' : 'text-info'">
      </div>
      <p x-show="errors.amount" class="text-xs text-error mt-1" x-text="errors.amount"></p>
    </div>

    <div class="px-4 py-3 space-y-3">

      <!-- Account (From) -->
      <div>
        <label class="text-xs text-base-content/50 font-medium block mb-1">
          <span x-text="form.type === 'transfer' ? 'บัญชีต้นทาง' : 'บัญชี'"></span>
        </label>
        <select x-model="form.account_id" class="select select-bordered select-sm w-full">
          <option value="">เลือกบัญชี</option>
          <template x-for="a in accounts" :key="a.id">
            <option :value="a.id" x-text="a.icon + ' ' + a.name + ' (' + fmt(a.balance) + ')'"></option>
          </template>
        </select>
        <p x-show="errors.account_id" class="text-xs text-error mt-1" x-text="errors.account_id"></p>
      </div>

      <!-- To Account (transfer only) -->
      <div x-show="form.type === 'transfer'">
        <label class="text-xs text-base-content/50 font-medium block mb-1">บัญชีปลายทาง</label>
        <select x-model="form.to_account_id" class="select select-bordered select-sm w-full">
          <option value="">เลือกบัญชี</option>
          <template x-for="a in accounts" :key="a.id">
            <option :value="a.id" x-text="a.icon + ' ' + a.name"></option>
          </template>
        </select>
        <p x-show="errors.to_account_id" class="text-xs text-error mt-1" x-text="errors.to_account_id"></p>
      </div>

      <!-- Category (income/expense only) -->
      <div x-show="form.type !== 'transfer'">
        <label class="text-xs text-base-content/50 font-medium block mb-1">หมวดหมู่</label>
        <select x-model="form.category_id" class="select select-bordered select-sm w-full">
          <option value="">เลือกหมวดหมู่</option>
          <template x-for="c in filteredCategories()" :key="c.id">
            <option :value="c.id" x-text="c.icon + ' ' + c.name"></option>
          </template>
        </select>
      </div>

      <!-- Date -->
      <div>
        <label class="text-xs text-base-content/50 font-medium block mb-1">วันที่</label>
        <input type="date" x-model="form.date" class="input input-bordered input-sm w-full">
        <p x-show="errors.date" class="text-xs text-error mt-1" x-text="errors.date"></p>
      </div>

      <!-- Note -->
      <div>
        <label class="text-xs text-base-content/50 font-medium block mb-1">หมายเหตุ (ไม่บังคับ)</label>
        <input type="text" x-model="form.note" placeholder="เช่น ค่าข้าวเที่ยง"
               class="input input-bordered input-sm w-full">
      </div>

    </div>

    <!-- Save Button -->
    <div class="px-4 pb-4 pt-2">
      <button @click="save()" :disabled="saving"
              class="btn btn-primary w-full"
              :class="form.type === 'income' ? 'btn-success' : form.type === 'expense' ? 'btn-error' : 'btn-primary'">
        <span x-show="saving" class="loading loading-spinner loading-sm"></span>
        <span x-show="!saving" x-text="form.type === 'income' ? '+ บันทึกรายรับ' : form.type === 'expense' ? '- บันทึกรายจ่าย' : '↔ บันทึกการโอน'"></span>
      </button>
    </div>

  </div><!-- /sheet -->
</div><!-- /add-tx-root -->


<script>
// ─── Dashboard Component ──────────────────────────────────────────
function dashboard() {
  return {
    month: '<?php echo date('Y-m'); ?>',
    loading: true,
    stats: { income: 0, expense: 0, net: 0, total_balance: 0, recent: [], category_pie: [], top5: [], daily_labels: [], daily_income: [], daily_expense: [] },
    lineChart: null,
    pieChart: null,

    init: function() {
      var self = this;
      this.loadData();
      window.addEventListener('reload-dashboard', function() { self.loadData(); });
    },

    prevMonth: function() {
      var parts = this.month.split('-');
      var d = new Date(parseInt(parts[0]), parseInt(parts[1]) - 2, 1);
      this.month = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0');
      this.loadData();
    },

    nextMonth: function() {
      var parts = this.month.split('-');
      var d = new Date(parseInt(parts[0]), parseInt(parts[1]), 1);
      this.month = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0');
      this.loadData();
    },

    loadData: async function() {
      this.loading = true;
      if (this.lineChart) { this.lineChart.destroy(); this.lineChart = null; }
      if (this.pieChart)  { this.pieChart.destroy();  this.pieChart  = null; }
      try {
        var res  = await fetch('api/stats.php?month=' + this.month);
        var json = await res.json();
        if (json.success) {
          this.stats = json.data;
          await this.$nextTick();
          this.renderCharts();
        }
      } catch(e) {
        console.error(e);
      } finally {
        this.loading = false;
      }
    },

    hasChartData: function() {
      if (!this.stats.daily_income) return false;
      return this.stats.daily_income.some(function(v) { return v > 0; }) ||
             this.stats.daily_expense.some(function(v) { return v > 0; });
    },

    renderCharts: function() {
      if (this.hasChartData()) {
        this.lineChart = renderLineChart('lineChart', this.stats.daily_labels, this.stats.daily_income, this.stats.daily_expense);
      }
      if (this.stats.category_pie && this.stats.category_pie.length > 0) {
        this.pieChart = renderPieChart(
          'pieChart',
          this.stats.category_pie.map(function(c) { return c.name; }),
          this.stats.category_pie.map(function(c) { return c.amount; }),
          this.stats.category_pie.map(function(c) { return c.color; })
        );
      }
    },

    openSheet: function() {
      window.dispatchEvent(new CustomEvent('open-add-tx'));
    },

    fmt: function(n) {
      if (n == null) return '฿0';
      var v = parseFloat(n);
      if (isNaN(v)) return '฿0';
      return '฿' + v.toLocaleString('th-TH', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    },

    formatDate: function(d) {
      if (!d) return '';
      var parts = d.split('-');
      return parts[2] + '/' + parts[1];
    }
  };
}

// ─── Add Transaction Sheet ────────────────────────────────────────
function addTxSheet() {
  return {
    open: false,
    saving: false,
    accounts: [],
    categories: [],
    errors: {},
    form: {
      type: 'expense',
      amount: '',
      account_id: '',
      to_account_id: '',
      category_id: '',
      date: '<?php echo date('Y-m-d'); ?>',
      note: ''
    },

    init: function() {
      var self = this;
      window.addEventListener('open-add-tx', function(e) {
        if (e && e.detail) e.detail.handled = true;
        self.openSheet();
      });
      this.loadMeta();
    },

    loadMeta: async function() {
      try {
        var r1 = await fetch('api/accounts.php');
        var j1 = await r1.json();
        if (j1.success) this.accounts = j1.data;

        var r2 = await fetch('api/categories.php');
        var j2 = await r2.json();
        if (j2.success) this.categories = j2.data;
      } catch(e) {}
    },

    openSheet: function() {
      this.errors = {};
      this.form = {
        type: 'expense',
        amount: '',
        account_id: this.accounts.length ? this.accounts[0].id : '',
        to_account_id: '',
        category_id: '',
        date: new Date().toISOString().slice(0,10),
        note: ''
      };
      this.open = true;
      document.body.style.overflow = 'hidden';
    },

    close: function() {
      this.open = false;
      document.body.style.overflow = '';
    },

    filteredCategories: function() {
      var type = this.form.type;
      return this.categories.filter(function(c) { return c.type === type; });
    },

    validate: function() {
      this.errors = {};
      if (!this.form.amount || parseFloat(this.form.amount) <= 0) {
        this.errors.amount = 'กรุณาระบุจำนวนเงิน';
      }
      if (!this.form.account_id) {
        this.errors.account_id = 'กรุณาเลือกบัญชี';
      }
      if (this.form.type === 'transfer' && !this.form.to_account_id) {
        this.errors.to_account_id = 'กรุณาเลือกบัญชีปลายทาง';
      }
      if (this.form.type === 'transfer' && this.form.account_id == this.form.to_account_id) {
        this.errors.to_account_id = 'บัญชีต้นทางและปลายทางต้องไม่ซ้ำกัน';
      }
      if (!this.form.date) {
        this.errors.date = 'กรุณาระบุวันที่';
      }
      return Object.keys(this.errors).length === 0;
    },

    save: async function() {
      if (!this.validate()) return;
      this.saving = true;
      try {
        var body = {
          account_id:    parseInt(this.form.account_id),
          type:          this.form.type,
          amount:        parseFloat(this.form.amount),
          date:          this.form.date,
          note:          this.form.note || null,
          category_id:   this.form.category_id ? parseInt(this.form.category_id) : null,
          to_account_id: this.form.type === 'transfer' ? parseInt(this.form.to_account_id) : null
        };
        var res  = await fetch('api/transactions.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(body) });
        var json = await res.json();
        if (json.success) {
          showToast('บันทึกสำเร็จ', 'success');
          this.close();
          // Reload dashboard data
          window.dispatchEvent(new CustomEvent('reload-dashboard'));
        } else {
          showToast(json.message || 'เกิดข้อผิดพลาด', 'error');
        }
      } catch(e) {
        showToast('เกิดข้อผิดพลาด', 'error');
      } finally {
        this.saving = false;
      }
    },

    fmt: function(n) {
      if (n == null) return '฿0';
      var v = parseFloat(n);
      return '฿' + v.toLocaleString('th-TH', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }
  };
}

// reload-dashboard ถูก listen ใน dashboard() init แล้ว
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
