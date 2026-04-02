<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';
require_auth();
$page_title = 'รายการ';
require_once __DIR__ . '/includes/header.php';
?>

<div x-data="txPage()" x-init="init()" x-cloak>

  <!-- ── Filter Bar ─────────────────────────────────────────────── -->
  <div class="space-y-2 mb-4">

    <!-- Month row -->
    <div class="flex items-center gap-1">
      <button @click="prevMonth()" class="btn btn-ghost btn-xs btn-circle shrink-0">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
      </button>
      <input type="month" x-model="filter.month" @change="load()"
             class="input input-xs input-bordered flex-1 text-center text-sm font-semibold">
      <button @click="nextMonth()" class="btn btn-ghost btn-xs btn-circle shrink-0">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
      </button>
    </div>

    <!-- Type chips -->
    <div class="filter-scroll">
      <button @click="setType('')"  class="badge badge-lg gap-1 shrink-0 cursor-pointer"
              :class="filter.type==='' ? 'badge-primary' : 'badge-ghost'">ทั้งหมด</button>
      <button @click="setType('income')"  class="badge badge-lg gap-1 shrink-0 cursor-pointer"
              :class="filter.type==='income'   ? 'badge-success' : 'badge-ghost'">💰 รายรับ</button>
      <button @click="setType('expense')" class="badge badge-lg gap-1 shrink-0 cursor-pointer"
              :class="filter.type==='expense'  ? 'badge-error'   : 'badge-ghost'">💸 รายจ่าย</button>
      <button @click="setType('transfer')" class="badge badge-lg gap-1 shrink-0 cursor-pointer"
              :class="filter.type==='transfer' ? 'badge-info'    : 'badge-ghost'">↔ โอน</button>
    </div>

    <!-- Account chips -->
    <div class="filter-scroll" x-show="accounts.length > 1">
      <button @click="setAccount('')" class="badge badge-lg shrink-0 cursor-pointer"
              :class="filter.account_id==='' ? 'badge-neutral' : 'badge-ghost'">ทุกบัญชี</button>
      <template x-for="a in accounts" :key="a.id">
        <button @click="setAccount(a.id)" class="badge badge-lg shrink-0 cursor-pointer"
                :class="filter.account_id==a.id ? 'badge-neutral' : 'badge-ghost'"
                x-text="a.icon + ' ' + a.name"></button>
      </template>
    </div>
  </div>

  <!-- ── Summary Strip ──────────────────────────────────────────── -->
  <div class="flex gap-2 mb-4 text-xs">
    <div class="flex-1 bg-success/10 rounded-lg px-3 py-2 text-center">
      <p class="text-success/70">รายรับ</p>
      <p class="font-bold text-success" x-text="fmt(summary.income)">฿0</p>
    </div>
    <div class="flex-1 bg-error/10 rounded-lg px-3 py-2 text-center">
      <p class="text-error/70">รายจ่าย</p>
      <p class="font-bold text-error" x-text="fmt(summary.expense)">฿0</p>
    </div>
    <div class="flex-1 bg-base-200 rounded-lg px-3 py-2 text-center">
      <p class="text-base-content/50">สุทธิ</p>
      <p class="font-bold" :class="summary.net>=0?'text-success':'text-error'" x-text="fmt(summary.net)">฿0</p>
    </div>
  </div>

  <!-- ── Loading ────────────────────────────────────────────────── -->
  <template x-if="loading">
    <div class="space-y-2">
      <template x-for="i in [1,2,3,4,5]" :key="i">
        <div class="skeleton h-16 rounded-xl"></div>
      </template>
    </div>
  </template>

  <!-- ── Transaction List ───────────────────────────────────────── -->
  <div x-show="!loading">

    <!-- Empty State -->
    <template x-if="txList.length === 0">
      <div class="flex flex-col items-center justify-center py-16 text-base-content/30">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-14 h-14 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
        <p class="text-sm mb-4">ไม่มีรายการในช่วงเวลานี้</p>
        <button @click="openSheet(null)" class="btn btn-primary btn-sm">+ บันทึกรายการ</button>
      </div>
    </template>

    <!-- Grouped by date -->
    <template x-if="txList.length > 0">
      <div class="space-y-1">
        <template x-for="group in groupedTx()" :key="group.date">
          <div>
            <!-- Date Header -->
            <div class="flex items-center justify-between px-1 py-2">
              <span class="text-xs font-semibold text-base-content/50" x-text="group.dateLabel"></span>
              <span class="text-xs text-base-content/40" x-text="group.dayTotal"></span>
            </div>
            <!-- Cards -->
            <div class="card bg-base-100 border border-base-200 overflow-hidden divide-y divide-base-200">
              <template x-for="t in group.items" :key="t.id">
                <div class="tx-card flex items-center gap-3 px-3 py-3" @click="openSheet(t)">
                  <!-- Icon circle -->
                  <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 text-lg"
                       :class="t.type==='income'?'bg-success/15':t.type==='expense'?'bg-error/15':'bg-info/15'">
                    <span x-text="t.category_icon||(t.type==='transfer'?'↔':'💰')"></span>
                  </div>
                  <!-- Info -->
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium truncate"
                       x-text="t.category_name||(t.type==='transfer'?'โอนเงิน':'รายการ')"></p>
                    <p class="text-xs text-base-content/40 truncate"
                       x-text="(t.account_name||'')+(t.to_account_name?' → '+t.to_account_name:'')+(t.note?' · '+t.note:'')"></p>
                  </div>
                  <!-- Amount -->
                  <div class="text-right shrink-0">
                    <p class="text-sm font-bold"
                       :class="t.type==='income'?'text-success':t.type==='expense'?'text-error':'text-info'"
                       x-text="(t.type==='income'?'+':t.type==='expense'?'-':'') + fmt(t.amount)"></p>
                  </div>
                  <!-- Delete btn -->
                  <button @click.stop="confirmDelete(t)"
                          class="btn btn-ghost btn-xs btn-circle text-base-content/30 hover:text-error ml-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                  </button>
                </div>
              </template>
            </div>
          </div>
        </template>
      </div>
    </template>
  </div>

</div><!-- /txPage -->


<!-- ═══════════════════════════════════════════════════════════════
     ADD / EDIT BOTTOM SHEET
═══════════════════════════════════════════════════════════════════ -->
<div x-data="txSheet()" x-cloak id="tx-sheet-root">

  <div x-show="open" class="sheet-overlay" @click="close()"></div>

  <div x-show="open" class="sheet">
    <div class="sheet-handle"></div>

    <!-- Header -->
    <div class="flex items-center justify-between px-4 py-3 border-b border-base-200">
      <h3 class="font-bold text-base" x-text="editId ? 'แก้ไขรายการ' : 'บันทึกรายการ'"></h3>
      <button @click="close()" class="btn btn-ghost btn-sm btn-circle">✕</button>
    </div>

    <!-- Type chips -->
    <div class="flex gap-2 px-4 pt-3 pb-2">
      <button @click="form.type='expense'; form.category_id=''"
              class="type-chip type-chip-expense flex-1 justify-center"
              :class="form.type==='expense'?'active-expense':''">💸 รายจ่าย</button>
      <button @click="form.type='income'; form.category_id=''"
              class="type-chip type-chip-income flex-1 justify-center"
              :class="form.type==='income'?'active-income':''">💰 รายรับ</button>
      <button @click="form.type='transfer'; form.category_id=''"
              class="type-chip type-chip-transfer flex-1 justify-center"
              :class="form.type==='transfer'?'active-transfer':''">↔ โอน</button>
    </div>

    <!-- Amount -->
    <div class="px-4 py-3 border-b border-base-200">
      <div class="flex items-center">
        <span class="text-2xl font-bold text-base-content/30 mr-1">฿</span>
        <input type="number" inputmode="decimal" placeholder="0.00"
               x-model="form.amount"
               class="amount-input"
               :class="form.type==='income'?'text-success':form.type==='expense'?'text-error':'text-info'">
      </div>
      <p x-show="errors.amount" class="text-xs text-error mt-1" x-text="errors.amount"></p>
    </div>

    <div class="px-4 py-3 space-y-3">

      <!-- Account -->
      <div>
        <label class="text-xs text-base-content/50 font-medium" x-text="form.type==='transfer'?'บัญชีต้นทาง':'บัญชี'"></label>
        <select x-model="form.account_id" class="select select-bordered select-sm w-full mt-1">
          <option value="">เลือกบัญชี</option>
          <template x-for="a in accounts" :key="a.id">
            <option :value="a.id" x-text="a.icon+' '+a.name+' ('+fmt(a.balance)+')'"></option>
          </template>
        </select>
        <p x-show="errors.account_id" class="text-xs text-error mt-1" x-text="errors.account_id"></p>
      </div>

      <!-- To Account -->
      <div x-show="form.type==='transfer'">
        <label class="text-xs text-base-content/50 font-medium">บัญชีปลายทาง</label>
        <select x-model="form.to_account_id" class="select select-bordered select-sm w-full mt-1">
          <option value="">เลือกบัญชี</option>
          <template x-for="a in accounts" :key="a.id">
            <option :value="a.id" x-text="a.icon+' '+a.name"></option>
          </template>
        </select>
        <p x-show="errors.to_account_id" class="text-xs text-error mt-1" x-text="errors.to_account_id"></p>
      </div>

      <!-- Category -->
      <div x-show="form.type!=='transfer'">
        <label class="text-xs text-base-content/50 font-medium">หมวดหมู่</label>
        <select x-model="form.category_id" class="select select-bordered select-sm w-full mt-1">
          <option value="">เลือกหมวดหมู่ (ไม่บังคับ)</option>
          <template x-for="c in catByType()" :key="c.id">
            <option :value="c.id" x-text="c.icon+' '+c.name"></option>
          </template>
        </select>
      </div>

      <!-- Date -->
      <div>
        <label class="text-xs text-base-content/50 font-medium">วันที่</label>
        <input type="date" x-model="form.date" class="input input-bordered input-sm w-full mt-1">
        <p x-show="errors.date" class="text-xs text-error mt-1" x-text="errors.date"></p>
      </div>

      <!-- Note -->
      <div>
        <label class="text-xs text-base-content/50 font-medium">หมายเหตุ</label>
        <input type="text" x-model="form.note" placeholder="เช่น ค่าอาหาร, ค่าน้ำมัน"
               class="input input-bordered input-sm w-full mt-1">
      </div>

    </div>

    <!-- Save -->
    <div class="px-4 pb-4 pt-1">
      <button @click="save()" :disabled="saving" class="btn w-full"
              :class="form.type==='income'?'btn-success':form.type==='expense'?'btn-error':'btn-primary'">
        <span x-show="saving" class="loading loading-spinner loading-sm"></span>
        <span x-show="!saving" x-text="editId ? 'บันทึกการแก้ไข' : (form.type==='income'?'+ บันทึกรายรับ':form.type==='expense'?'- บันทึกรายจ่าย':'↔ บันทึกการโอน')"></span>
      </button>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════
     DELETE CONFIRM DIALOG
═══════════════════════════════════════════════════════════════════ -->
<dialog id="delete-dialog" class="modal modal-bottom">
  <div class="modal-box rounded-t-2xl rounded-b-none max-w-[480px] mx-auto">
    <h3 class="font-bold text-lg mb-1">ลบรายการ?</h3>
    <p class="text-sm text-base-content/60 mb-4" id="delete-dialog-msg">การกระทำนี้ไม่สามารถยกเลิกได้</p>
    <div class="flex gap-3">
      <form method="dialog" class="flex-1">
        <button class="btn btn-ghost w-full">ยกเลิก</button>
      </form>
      <button id="delete-confirm-btn" class="btn btn-error flex-1">ลบ</button>
    </div>
  </div>
  <form method="dialog" class="modal-backdrop"><button>ปิด</button></form>
</dialog>


<script>
// ─── Main Page Component ──────────────────────────────────────────
function txPage() {
  return {
    txList:    [],
    accounts:  [],
    loading:   true,
    filter: {
      month:      '<?php echo date('Y-m'); ?>',
      type:       '',
      account_id: ''
    },
    summary: { income: 0, expense: 0, net: 0 },
    _deleteTarget: null,

    init: function() {
      var self = this;
      this.loadMeta();
      this.load();
      window.addEventListener('tx-saved',   function() { self.load(); });
      window.addEventListener('open-add-tx', function(e) {
        if (e && e.detail) e.detail.handled = true;
        self.openSheet(null);
      });
      // delete confirm
      document.getElementById('delete-confirm-btn').addEventListener('click', function() {
        if (self._deleteTarget) self.doDelete(self._deleteTarget);
      });
    },

    loadMeta: async function() {
      try {
        var r = await fetch('api/accounts.php');
        var j = await r.json();
        if (j.success) this.accounts = j.data;
      } catch(e) {}
    },

    load: async function() {
      this.loading = true;
      try {
        var params = 'month=' + this.filter.month;
        if (this.filter.type)       params += '&type='       + this.filter.type;
        if (this.filter.account_id) params += '&account_id=' + this.filter.account_id;

        var r = await fetch('api/transactions.php?' + params);
        var j = await r.json();
        if (j.success) {
          this.txList = j.data;
          this.calcSummary();
        }
      } catch(e) { console.error(e); }
      finally { this.loading = false; }
    },

    calcSummary: function() {
      var inc = 0, exp = 0;
      this.txList.forEach(function(t) {
        if (t.type === 'income')  inc += parseFloat(t.amount);
        if (t.type === 'expense') exp += parseFloat(t.amount);
      });
      this.summary = { income: inc, expense: exp, net: inc - exp };
    },

    prevMonth: function() {
      var p = this.filter.month.split('-');
      var d = new Date(parseInt(p[0]), parseInt(p[1])-2, 1);
      this.filter.month = d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0');
      this.load();
    },

    nextMonth: function() {
      var p = this.filter.month.split('-');
      var d = new Date(parseInt(p[0]), parseInt(p[1]), 1);
      this.filter.month = d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0');
      this.load();
    },

    setType: function(t) { this.filter.type = t; this.load(); },
    setAccount: function(id) { this.filter.account_id = id; this.load(); },

    groupedTx: function() {
      var map = {}, order = [];
      this.txList.forEach(function(t) {
        if (!map[t.date]) { map[t.date] = []; order.push(t.date); }
        map[t.date].push(t);
      });
      var DAYS = ['อา','จ','อ','พ','พฤ','ศ','ส'];
      return order.map(function(date) {
        var items = map[date];
        var p = date.split('-');
        var d = new Date(parseInt(p[0]), parseInt(p[1])-1, parseInt(p[2]));
        var dayName = DAYS[d.getDay()];
        var dateLabel = dayName + ' ' + p[2] + '/' + p[1] + '/' + p[0].slice(2);
        var net = 0;
        items.forEach(function(t) {
          if (t.type === 'income')  net += parseFloat(t.amount);
          if (t.type === 'expense') net -= parseFloat(t.amount);
        });
        var dayTotal = net >= 0 ? '+฿'+Math.abs(net).toLocaleString('th-TH',{maximumFractionDigits:0}) : '-฿'+Math.abs(net).toLocaleString('th-TH',{maximumFractionDigits:0});
        return { date: date, dateLabel: dateLabel, dayTotal: dayTotal, items: items };
      });
    },

    openSheet: function(tx) {
      window.dispatchEvent(new CustomEvent('open-tx-sheet', { detail: tx }));
    },

    confirmDelete: function(tx) {
      this._deleteTarget = tx;
      var msg = (tx.category_name || 'รายการ') + ' ' + this.fmt(tx.amount);
      document.getElementById('delete-dialog-msg').textContent = msg + ' — การกระทำนี้ไม่สามารถยกเลิกได้';
      document.getElementById('delete-dialog').showModal();
    },

    doDelete: async function(tx) {
      try {
        var r = await fetch('api/transactions.php?id=' + tx.id, { method: 'DELETE' });
        var j = await r.json();
        if (j.success) {
          showToast('ลบรายการแล้ว', 'error');
          this.load();
        } else {
          showToast(j.message || 'ลบไม่สำเร็จ', 'error');
        }
      } catch(e) { showToast('เกิดข้อผิดพลาด', 'error'); }
      this._deleteTarget = null;
    },

    fmt: function(n) {
      if (n == null) return '฿0';
      return '฿' + parseFloat(n).toLocaleString('th-TH', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }
  };
}

// ─── Sheet Component ──────────────────────────────────────────────
function txSheet() {
  return {
    open:       false,
    saving:     false,
    editId:     null,
    accounts:   [],
    categories: [],
    errors:     {},
    form: {
      type: 'expense', amount: '', account_id: '', to_account_id: '',
      category_id: '', date: '', note: ''
    },

    init: function() {
      var self = this;
      window.addEventListener('open-tx-sheet', function(e) { self.openSheet(e.detail); });
      this.loadMeta();
    },

    loadMeta: async function() {
      try {
        var r1 = await fetch('api/accounts.php');  var j1 = await r1.json();
        if (j1.success) this.accounts = j1.data;
        var r2 = await fetch('api/categories.php'); var j2 = await r2.json();
        if (j2.success) this.categories = j2.data;
      } catch(e) {}
    },

    openSheet: function(tx) {
      this.errors = {};
      var today = new Date().toISOString().slice(0,10);
      if (tx) {
        // Edit mode
        this.editId = tx.id;
        this.form = {
          type:          tx.type,
          amount:        tx.amount,
          account_id:    tx.account_id,
          to_account_id: tx.to_account_id || '',
          category_id:   tx.category_id || '',
          date:          tx.date,
          note:          tx.note || ''
        };
      } else {
        // Add mode
        this.editId = null;
        this.form = {
          type: 'expense', amount: '',
          account_id:    this.accounts.length ? this.accounts[0].id : '',
          to_account_id: '', category_id: '',
          date: today, note: ''
        };
      }
      this.open = true;
      document.body.style.overflow = 'hidden';
    },

    close: function() { this.open = false; document.body.style.overflow = ''; },

    catByType: function() {
      var type = this.form.type;
      return this.categories.filter(function(c) { return c.type === type; });
    },

    validate: function() {
      this.errors = {};
      if (!this.form.amount || parseFloat(this.form.amount) <= 0)
        this.errors.amount = 'กรุณาระบุจำนวนเงิน';
      if (!this.form.account_id)
        this.errors.account_id = 'กรุณาเลือกบัญชี';
      if (this.form.type === 'transfer') {
        if (!this.form.to_account_id)
          this.errors.to_account_id = 'กรุณาเลือกบัญชีปลายทาง';
        if (this.form.account_id == this.form.to_account_id)
          this.errors.to_account_id = 'บัญชีต้องไม่ซ้ำกัน';
      }
      if (!this.form.date) this.errors.date = 'กรุณาระบุวันที่';
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
        var url    = this.editId ? 'api/transactions.php?id=' + this.editId : 'api/transactions.php';
        var method = this.editId ? 'PUT' : 'POST';
        var r = await fetch(url, { method: method, headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(body) });
        var j = await r.json();
        if (j.success) {
          showToast(this.editId ? 'แก้ไขสำเร็จ' : 'บันทึกสำเร็จ', 'success');
          this.close();
          window.dispatchEvent(new CustomEvent('tx-saved'));
        } else {
          showToast(j.message || 'เกิดข้อผิดพลาด', 'error');
        }
      } catch(e) { showToast('เกิดข้อผิดพลาด', 'error'); }
      finally { this.saving = false; }
    },

    fmt: function(n) {
      if (n == null) return '฿0';
      return '฿' + parseFloat(n).toLocaleString('th-TH', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }
  };
}

</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
