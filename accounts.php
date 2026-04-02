<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';
require_auth();
$page_title = 'บัญชี';
require_once __DIR__ . '/includes/header.php';
?>

<div x-data="accountsPage()" x-init="init()" x-cloak>

  <!-- Total Balance -->
  <div class="card bg-primary text-primary-content mb-4">
    <div class="card-body p-4 text-center">
      <p class="text-sm opacity-75">ยอดรวมทุกบัญชี</p>
      <p class="text-3xl font-bold mt-1" x-text="fmt(totalBalance())">฿0</p>
    </div>
  </div>

  <!-- Loading -->
  <template x-if="loading">
    <div class="space-y-3">
      <template x-for="i in [1,2,3]" :key="i">
        <div class="skeleton h-20 rounded-xl"></div>
      </template>
    </div>
  </template>

  <!-- Account Cards -->
  <div x-show="!loading" class="space-y-3">

    <template x-if="accounts.length === 0">
      <div class="flex flex-col items-center py-12 text-base-content/30">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
        </svg>
        <p class="text-sm mb-3">ยังไม่มีบัญชี</p>
        <button @click="openSheet(null)" class="btn btn-primary btn-sm">+ เพิ่มบัญชีแรก</button>
      </div>
    </template>

    <template x-for="a in accounts" :key="a.id">
      <div class="card bg-base-100 border border-base-200 active:bg-base-200 cursor-pointer"
           @click="openSheet(a)">
        <div class="card-body p-4">
          <div class="flex items-center gap-3">
            <!-- Icon -->
            <div class="w-12 h-12 rounded-full flex items-center justify-center text-2xl shrink-0"
                 :style="'background:' + (a.color||'#6366f1') + '22'">
              <span x-text="a.icon||'💳'"></span>
            </div>
            <!-- Info -->
            <div class="flex-1 min-w-0">
              <p class="font-semibold text-sm" x-text="a.name"></p>
              <p class="text-xs text-base-content/40 capitalize" x-text="typeLabel(a.type)"></p>
            </div>
            <!-- Balance -->
            <div class="text-right">
              <p class="font-bold text-base"
                 :class="parseFloat(a.balance) >= 0 ? 'text-base-content' : 'text-error'"
                 x-text="fmt(a.balance)"></p>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Add button -->
    <button @click="openSheet(null)"
            class="w-full border-2 border-dashed border-base-300 rounded-xl py-4 text-sm text-base-content/40 hover:border-primary hover:text-primary transition-colors">
      + เพิ่มบัญชีใหม่
    </button>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════
     ACCOUNT SHEET
═══════════════════════════════════════════════════════════════════ -->
<div x-data="accountSheet()" x-cloak>
  <div x-show="open" class="sheet-overlay" @click="close()"></div>
  <div x-show="open" class="sheet">
    <div class="sheet-handle"></div>
    <div class="flex items-center justify-between px-4 py-3 border-b border-base-200">
      <h3 class="font-bold" x-text="editId ? 'แก้ไขบัญชี' : 'เพิ่มบัญชี'"></h3>
      <button @click="close()" class="btn btn-ghost btn-sm btn-circle">✕</button>
    </div>

    <div class="px-4 py-3 space-y-3">

      <!-- Icon + Color preview -->
      <div class="flex items-center gap-3">
        <div class="w-14 h-14 rounded-full flex items-center justify-center text-2xl border-2 border-base-200"
             :style="'background:' + (form.color||'#6366f1') + '22'">
          <span x-text="form.icon||'💳'"></span>
        </div>
        <div class="flex-1 space-y-1">
          <div class="flex gap-2">
            <div class="flex-1">
              <label class="text-xs text-base-content/50">Icon (emoji)</label>
              <input type="text" x-model="form.icon" maxlength="2" placeholder="💳"
                     class="input input-bordered input-sm w-full mt-1 text-center text-lg">
            </div>
            <div class="flex-1">
              <label class="text-xs text-base-content/50">สี</label>
              <input type="color" x-model="form.color"
                     class="w-full h-9 rounded-lg border border-base-200 cursor-pointer mt-1 p-0.5">
            </div>
          </div>
        </div>
      </div>

      <!-- Name -->
      <div>
        <label class="text-xs text-base-content/50 font-medium">ชื่อบัญชี *</label>
        <input type="text" x-model="form.name" placeholder="เช่น กระเป๋าสตางค์, ธนาคารกสิกร"
               class="input input-bordered input-sm w-full mt-1">
        <p x-show="errors.name" class="text-xs text-error mt-1" x-text="errors.name"></p>
      </div>

      <!-- Type -->
      <div>
        <label class="text-xs text-base-content/50 font-medium">ประเภทบัญชี</label>
        <select x-model="form.type" class="select select-bordered select-sm w-full mt-1">
          <option value="cash">👛 เงินสด</option>
          <option value="bank">🏦 บัญชีธนาคาร</option>
          <option value="credit">💳 บัตรเครดิต</option>
          <option value="saving">📈 เงินออม</option>
        </select>
      </div>

      <!-- Initial Balance (add only) -->
      <div x-show="!editId">
        <label class="text-xs text-base-content/50 font-medium">ยอดตั้งต้น</label>
        <div class="flex items-center mt-1">
          <span class="text-base-content/40 mr-1 font-bold">฿</span>
          <input type="number" inputmode="decimal" x-model="form.balance" placeholder="0"
                 class="input input-bordered input-sm flex-1">
        </div>
      </div>

    </div>

    <!-- Buttons -->
    <div class="px-4 pb-4 space-y-2">
      <button @click="save()" :disabled="saving" class="btn btn-primary w-full">
        <span x-show="saving" class="loading loading-spinner loading-sm"></span>
        <span x-show="!saving" x-text="editId ? 'บันทึกการแก้ไข' : '+ เพิ่มบัญชี'"></span>
      </button>
      <button x-show="editId" @click="remove()" class="btn btn-ghost btn-sm w-full text-error">
        ลบบัญชีนี้
      </button>
    </div>
  </div>
</div>

<!-- Delete confirm -->
<dialog id="acc-delete-dialog" class="modal modal-bottom">
  <div class="modal-box rounded-t-2xl rounded-b-none max-w-[480px] mx-auto">
    <h3 class="font-bold text-lg mb-1">ลบบัญชี?</h3>
    <p class="text-sm text-base-content/60 mb-4">ต้องไม่มีรายการธุรกรรมผูกอยู่</p>
    <div class="flex gap-3">
      <form method="dialog" class="flex-1"><button class="btn btn-ghost w-full">ยกเลิก</button></form>
      <button id="acc-delete-btn" class="btn btn-error flex-1">ลบ</button>
    </div>
  </div>
  <form method="dialog" class="modal-backdrop"><button>ปิด</button></form>
</dialog>


<script>
function accountsPage() {
  return {
    accounts: [],
    loading: true,

    init: function() {
      var self = this;
      this.load();
      window.addEventListener('account-saved', function() { self.load(); });
    },

    load: async function() {
      this.loading = true;
      try {
        var r = await fetch('api/accounts.php');
        var j = await r.json();
        if (j.success) this.accounts = j.data;
      } catch(e) {}
      finally { this.loading = false; }
    },

    totalBalance: function() {
      return this.accounts.reduce(function(s,a){ return s + parseFloat(a.balance||0); }, 0);
    },

    openSheet: function(a) {
      window.dispatchEvent(new CustomEvent('open-account-sheet', { detail: a }));
    },

    typeLabel: function(t) {
      var m = { cash:'เงินสด', bank:'บัญชีธนาคาร', credit:'บัตรเครดิต', saving:'เงินออม' };
      return m[t] || t;
    },

    fmt: function(n) {
      return '฿' + parseFloat(n||0).toLocaleString('th-TH', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }
  };
}

function accountSheet() {
  return {
    open: false, saving: false, editId: null, errors: {},
    form: { name:'', type:'cash', balance:0, color:'#6366f1', icon:'💳' },

    init: function() {
      var self = this;
      window.addEventListener('open-account-sheet', function(e){ self.openSheet(e.detail); });
      document.getElementById('acc-delete-btn').addEventListener('click', function(){ self.doDelete(); });
    },

    openSheet: function(a) {
      this.errors = {};
      if (a) {
        this.editId = a.id;
        this.form = { name: a.name, type: a.type, balance: a.balance, color: a.color||'#6366f1', icon: a.icon||'💳' };
      } else {
        this.editId = null;
        this.form = { name:'', type:'cash', balance:0, color:'#6366f1', icon:'💳' };
      }
      this.open = true;
      document.body.style.overflow = 'hidden';
    },

    close: function() { this.open = false; document.body.style.overflow = ''; },

    validate: function() {
      this.errors = {};
      if (!this.form.name.trim()) this.errors.name = 'กรุณาระบุชื่อบัญชี';
      return Object.keys(this.errors).length === 0;
    },

    save: async function() {
      if (!this.validate()) return;
      this.saving = true;
      try {
        var body = { name: this.form.name, type: this.form.type, color: this.form.color, icon: this.form.icon };
        if (!this.editId) body.balance = parseFloat(this.form.balance||0);
        var url    = this.editId ? 'api/accounts.php?id=' + this.editId : 'api/accounts.php';
        var method = this.editId ? 'PUT' : 'POST';
        var r = await fetch(url, { method: method, headers: {'Content-Type':'application/json'}, body: JSON.stringify(body) });
        var j = await r.json();
        if (j.success) {
          showToast(this.editId ? 'แก้ไขสำเร็จ' : 'เพิ่มบัญชีแล้ว', 'success');
          this.close();
          window.dispatchEvent(new CustomEvent('account-saved'));
        } else { showToast(j.message || 'เกิดข้อผิดพลาด', 'error'); }
      } catch(e) { showToast('เกิดข้อผิดพลาด', 'error'); }
      finally { this.saving = false; }
    },

    remove: function() {
      document.getElementById('acc-delete-dialog').showModal();
    },

    doDelete: async function() {
      var id = this.editId;
      if (!id) return;
      try {
        var r = await fetch('api/accounts.php?id=' + id, { method: 'DELETE' });
        var j = await r.json();
        if (j.success) {
          showToast('ลบบัญชีแล้ว', 'error');
          this.close();
          window.dispatchEvent(new CustomEvent('account-saved'));
        } else { showToast(j.message || 'ลบไม่สำเร็จ', 'error'); }
      } catch(e) { showToast('เกิดข้อผิดพลาด', 'error'); }
    },

    fmt: function(n) {
      return '฿' + parseFloat(n||0).toLocaleString('th-TH', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }
  };
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
