<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';
require_auth();
$page_title = 'งบประมาณ';
require_once __DIR__ . '/includes/header.php';
?>

<div x-data="budgetsPage()" x-init="init()" x-cloak>

  <!-- Month Picker -->
  <div class="flex items-center gap-1 mb-4">
    <button @click="prevMonth()" class="btn btn-ghost btn-xs btn-circle">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
    </button>
    <input type="month" x-model="month" @change="load()" class="input input-xs input-bordered flex-1 text-center text-sm font-semibold">
    <button @click="nextMonth()" class="btn btn-ghost btn-xs btn-circle">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    </button>
  </div>

  <!-- Summary Strip -->
  <div class="flex gap-2 mb-4 text-xs">
    <div class="flex-1 bg-base-200 rounded-lg px-3 py-2 text-center">
      <p class="text-base-content/50">งบรวม</p>
      <p class="font-bold" x-text="fmt(totalBudget())">฿0</p>
    </div>
    <div class="flex-1 bg-error/10 rounded-lg px-3 py-2 text-center">
      <p class="text-error/70">ใช้ไป</p>
      <p class="font-bold text-error" x-text="fmt(totalSpent())">฿0</p>
    </div>
    <div class="flex-1 rounded-lg px-3 py-2 text-center"
         :class="totalRemain()>=0?'bg-success/10':'bg-error/10'">
      <p :class="totalRemain()>=0?'text-success/70':'text-error/70'">คงเหลือ</p>
      <p class="font-bold" :class="totalRemain()>=0?'text-success':'text-error'"
         x-text="fmt(totalRemain())">฿0</p>
    </div>
  </div>

  <!-- Loading -->
  <template x-if="loading">
    <div class="space-y-3">
      <template x-for="i in [1,2,3,4]" :key="i"><div class="skeleton h-20 rounded-xl"></div></template>
    </div>
  </template>

  <div x-show="!loading" class="space-y-3">

    <!-- Budget Cards -->
    <template x-if="budgets.length > 0">
      <div class="space-y-2">
        <p class="text-xs font-semibold text-base-content/50 px-1">งบประมาณที่ตั้งไว้</p>
        <template x-for="b in budgets" :key="b.id">
          <div class="card bg-base-100 border border-base-200 cursor-pointer tx-card" @click="openSheet(b, false)">
            <div class="card-body p-3">
              <div class="flex items-center gap-2 mb-2">
                <span class="text-lg" x-text="b.category_icon||'📦'"></span>
                <p class="text-sm font-medium flex-1 truncate" x-text="b.category_name"></p>
                <!-- Over badge -->
                <span x-show="pct(b) >= 100" class="badge badge-error badge-xs">เกินงบ</span>
                <span x-show="pct(b) >= 80 && pct(b) < 100" class="badge badge-warning badge-xs">ใกล้เต็ม</span>
                <span class="text-xs text-base-content/50" x-text="pct(b) + '%'"></span>
              </div>
              <!-- Progress bar -->
              <div class="w-full bg-base-200 rounded-full h-2 overflow-hidden">
                <div class="h-full rounded-full transition-all duration-500"
                     :style="'width:' + Math.min(pct(b),100) + '%'"
                     :class="pct(b) >= 100 ? 'bg-error' : pct(b) >= 80 ? 'bg-warning' : 'bg-success'"></div>
              </div>
              <!-- Amount row -->
              <div class="flex justify-between text-xs text-base-content/50 mt-1">
                <span>ใช้ <span class="font-semibold text-base-content" x-text="fmt(b.spent)"></span></span>
                <span>จาก <span class="font-semibold text-base-content" x-text="fmt(b.amount)"></span></span>
              </div>
            </div>
          </div>
        </template>
      </div>
    </template>

    <!-- Categories without budget -->
    <template x-if="unbudgeted.length > 0">
      <div class="space-y-2">
        <p class="text-xs font-semibold text-base-content/50 px-1">ยังไม่ตั้งงบ</p>
        <div class="card bg-base-100 border border-dashed border-base-300 overflow-hidden divide-y divide-base-200">
          <template x-for="c in unbudgeted" :key="c.id">
            <div class="flex items-center gap-3 px-4 py-3 tx-card" @click="openSheet(null, c)">
              <span class="text-lg" x-text="c.icon||'📦'"></span>
              <p class="flex-1 text-sm text-base-content/60" x-text="c.name"></p>
              <span class="text-xs text-primary">+ ตั้งงบ</span>
            </div>
          </template>
        </div>
      </div>
    </template>

    <!-- All done empty -->
    <template x-if="budgets.length === 0 && unbudgeted.length === 0">
      <div class="flex flex-col items-center py-12 text-base-content/30">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
        </svg>
        <p class="text-sm">ยังไม่มีหมวดหมู่รายจ่าย</p>
      </div>
    </template>

  </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════
     BUDGET SHEET
═══════════════════════════════════════════════════════════════════ -->
<div x-data="budgetSheet()" x-cloak>
  <div x-show="open" class="sheet-overlay" @click="close()"></div>
  <div x-show="open" class="sheet">
    <div class="sheet-handle"></div>
    <div class="flex items-center justify-between px-4 py-3 border-b border-base-200">
      <h3 class="font-bold" x-text="editId ? 'แก้ไขงบประมาณ' : 'ตั้งงบประมาณ'"></h3>
      <button @click="close()" class="btn btn-ghost btn-sm btn-circle">✕</button>
    </div>
    <div class="px-4 py-4 space-y-4">
      <!-- Category info -->
      <div class="flex items-center gap-2 bg-base-200 rounded-xl px-3 py-2">
        <span class="text-xl" x-text="form.icon"></span>
        <p class="font-semibold text-sm" x-text="form.cat_name"></p>
      </div>
      <!-- Amount -->
      <div>
        <label class="text-xs text-base-content/50 font-medium">งบประมาณ (บาท)</label>
        <div class="flex items-center mt-1">
          <span class="text-base-content/40 mr-1 font-bold">฿</span>
          <input type="number" inputmode="decimal" x-model="form.amount" placeholder="0"
                 class="input input-bordered input-sm flex-1">
        </div>
        <p x-show="errors.amount" class="text-xs text-error mt-1" x-text="errors.amount"></p>
      </div>
    </div>
    <div class="px-4 pb-4 space-y-2">
      <button @click="save()" :disabled="saving" class="btn btn-primary w-full">
        <span x-show="saving" class="loading loading-spinner loading-sm"></span>
        <span x-show="!saving" x-text="editId ? 'บันทึกการแก้ไข' : 'ตั้งงบประมาณ'"></span>
      </button>
      <button x-show="editId" @click="remove()" class="btn btn-ghost btn-sm w-full text-error">
        ลบงบประมาณนี้
      </button>
    </div>
  </div>
</div>

<!-- Delete confirm -->
<dialog id="bud-delete-dialog" class="modal modal-bottom">
  <div class="modal-box rounded-t-2xl rounded-b-none max-w-[480px] mx-auto">
    <h3 class="font-bold text-lg mb-2">ลบงบประมาณ?</h3>
    <div class="flex gap-3">
      <form method="dialog" class="flex-1"><button class="btn btn-ghost w-full">ยกเลิก</button></form>
      <button id="bud-delete-btn" class="btn btn-error flex-1">ลบ</button>
    </div>
  </div>
  <form method="dialog" class="modal-backdrop"><button>ปิด</button></form>
</dialog>


<script>
function budgetsPage() {
  return {
    budgets: [], unbudgeted: [], loading: true,
    month: '<?php echo date('Y-m'); ?>',

    init: function() {
      var self = this;
      this.load();
      window.addEventListener('budget-saved', function() { self.load(); });
    },

    load: async function() {
      this.loading = true;
      try {
        // Load budgets
        var r1 = await fetch('api/budgets.php?month=' + this.month);
        var j1 = await r1.json();
        this.budgets = j1.success ? j1.data : [];

        // Load expense categories to find unbudgeted
        var r2 = await fetch('api/categories.php?type=expense');
        var j2 = await r2.json();
        var allCats = j2.success ? j2.data : [];
        var budgetedIds = this.budgets.map(function(b){ return parseInt(b.category_id); });
        this.unbudgeted = allCats.filter(function(c){ return budgetedIds.indexOf(parseInt(c.id)) === -1; });
      } catch(e) {}
      finally { this.loading = false; }
    },

    prevMonth: function() {
      var p = this.month.split('-');
      var d = new Date(parseInt(p[0]), parseInt(p[1])-2, 1);
      this.month = d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0');
      this.load();
    },
    nextMonth: function() {
      var p = this.month.split('-');
      var d = new Date(parseInt(p[0]), parseInt(p[1]), 1);
      this.month = d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0');
      this.load();
    },

    pct: function(b) {
      if (!b.amount || b.amount <= 0) return 0;
      return Math.round(parseFloat(b.spent) / parseFloat(b.amount) * 100);
    },
    totalBudget:  function() { return this.budgets.reduce(function(s,b){ return s+parseFloat(b.amount||0); }, 0); },
    totalSpent:   function() { return this.budgets.reduce(function(s,b){ return s+parseFloat(b.spent||0);  }, 0); },
    totalRemain:  function() { return this.totalBudget() - this.totalSpent(); },

    openSheet: function(bud, cat) {
      window.dispatchEvent(new CustomEvent('open-budget-sheet', { detail: { bud: bud, cat: cat, month: this.month } }));
    },

    fmt: function(n) {
      return '฿' + parseFloat(n||0).toLocaleString('th-TH', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }
  };
}

function budgetSheet() {
  return {
    open: false, saving: false, editId: null, errors: {},
    form: { cat_name:'', icon:'📦', category_id:'', amount:'', month:'' },

    init: function() {
      var self = this;
      window.addEventListener('open-budget-sheet', function(e){ self.openSheet(e.detail); });
      document.getElementById('bud-delete-btn').addEventListener('click', function(){ self.doDelete(); });
    },

    openSheet: function(data) {
      this.errors = {};
      var bud = data.bud, cat = data.cat;
      this.form.month = data.month;
      if (bud) {
        // Edit existing budget
        this.editId         = bud.id;
        this.form.cat_name  = bud.category_name;
        this.form.icon      = bud.category_icon || '📦';
        this.form.category_id = bud.category_id;
        this.form.amount    = bud.amount;
      } else if (cat) {
        // New budget for category
        this.editId         = null;
        this.form.cat_name  = cat.name;
        this.form.icon      = cat.icon || '📦';
        this.form.category_id = cat.id;
        this.form.amount    = '';
      }
      this.open = true;
      document.body.style.overflow = 'hidden';
    },

    close: function() { this.open = false; document.body.style.overflow = ''; },

    validate: function() {
      this.errors = {};
      if (!this.form.amount || parseFloat(this.form.amount) <= 0)
        this.errors.amount = 'กรุณาระบุจำนวนงบ';
      return Object.keys(this.errors).length === 0;
    },

    save: async function() {
      if (!this.validate()) return;
      this.saving = true;
      try {
        var body, url, method;
        if (this.editId) {
          body   = { amount: parseFloat(this.form.amount) };
          url    = 'api/budgets.php?id=' + this.editId;
          method = 'PUT';
        } else {
          body   = { category_id: parseInt(this.form.category_id), amount: parseFloat(this.form.amount), month: this.form.month };
          url    = 'api/budgets.php';
          method = 'POST';
        }
        var r = await fetch(url, { method: method, headers:{'Content-Type':'application/json'}, body: JSON.stringify(body) });
        var j = await r.json();
        if (j.success) {
          showToast(this.editId ? 'แก้ไขงบแล้ว' : 'ตั้งงบสำเร็จ', 'success');
          this.close();
          window.dispatchEvent(new CustomEvent('budget-saved'));
        } else { showToast(j.message||'เกิดข้อผิดพลาด','error'); }
      } catch(e) { showToast('เกิดข้อผิดพลาด','error'); }
      finally { this.saving = false; }
    },

    remove: function() { document.getElementById('bud-delete-dialog').showModal(); },

    doDelete: async function() {
      if (!this.editId) return;
      try {
        var r = await fetch('api/budgets.php?id=' + this.editId, { method:'DELETE' });
        var j = await r.json();
        if (j.success) {
          showToast('ลบงบแล้ว','error');
          this.close();
          window.dispatchEvent(new CustomEvent('budget-saved'));
        } else { showToast(j.message||'ลบไม่สำเร็จ','error'); }
      } catch(e) { showToast('เกิดข้อผิดพลาด','error'); }
    }
  };
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
