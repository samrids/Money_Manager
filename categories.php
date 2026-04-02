<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';
require_auth();
$page_title = 'หมวดหมู่';
require_once __DIR__ . '/includes/header.php';
?>

<div x-data="catPage()" x-init="init()" x-cloak>

  <!-- Tabs -->
  <div class="tabs tabs-boxed mb-4">
    <button class="tab flex-1" :class="tab==='expense'?'tab-active':''" @click="tab='expense'">
      💸 รายจ่าย
    </button>
    <button class="tab flex-1" :class="tab==='income'?'tab-active':''" @click="tab='income'">
      💰 รายรับ
    </button>
  </div>

  <!-- Loading -->
  <template x-if="loading">
    <div class="space-y-2">
      <template x-for="i in [1,2,3,4,5]" :key="i">
        <div class="skeleton h-14 rounded-xl"></div>
      </template>
    </div>
  </template>

  <div x-show="!loading">
    <!-- List -->
    <div class="card bg-base-100 border border-base-200 overflow-hidden divide-y divide-base-200 mb-3">
      <template x-for="c in filtered()" :key="c.id">
        <div class="flex items-center gap-3 px-4 py-3 tx-card" @click="openSheet(c)">
          <div class="w-9 h-9 rounded-full flex items-center justify-center text-lg shrink-0"
               :style="'background:' + (c.color||'#6366f1') + '22'">
            <span x-text="c.icon||'📦'"></span>
          </div>
          <p class="flex-1 text-sm font-medium" x-text="c.name"></p>
          <span x-show="c.is_default==1" class="badge badge-xs badge-ghost">default</span>
          <svg x-show="c.is_default!=1" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-base-content/30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </div>
      </template>
      <template x-if="filtered().length === 0">
        <div class="flex flex-col items-center py-10 text-base-content/30">
          <p class="text-sm">ไม่มีหมวดหมู่</p>
        </div>
      </template>
    </div>

    <!-- Add button -->
    <button @click="openSheet(null)"
            class="w-full border-2 border-dashed border-base-300 rounded-xl py-4 text-sm text-base-content/40 hover:border-primary hover:text-primary transition-colors">
      + เพิ่มหมวดหมู่ใหม่
    </button>
  </div>

</div>

<!-- ═══════════════════════════════════════════════════════════════
     CATEGORY SHEET
═══════════════════════════════════════════════════════════════════ -->
<div x-data="catSheet()" x-cloak>
  <div x-show="open" class="sheet-overlay" @click="close()"></div>
  <div x-show="open" class="sheet">
    <div class="sheet-handle"></div>
    <div class="flex items-center justify-between px-4 py-3 border-b border-base-200">
      <h3 class="font-bold" x-text="editId ? 'แก้ไขหมวดหมู่' : 'เพิ่มหมวดหมู่'"></h3>
      <button @click="close()" class="btn btn-ghost btn-sm btn-circle">✕</button>
    </div>

    <div class="px-4 py-4 space-y-3">

      <!-- Preview -->
      <div class="flex items-center gap-3 p-3 bg-base-200 rounded-xl">
        <div class="w-12 h-12 rounded-full flex items-center justify-center text-2xl"
             :style="'background:' + (form.color||'#6366f1') + '33'">
          <span x-text="form.icon||'📦'"></span>
        </div>
        <div>
          <p class="font-semibold text-sm" x-text="form.name||'ชื่อหมวดหมู่'"></p>
          <p class="text-xs text-base-content/50" x-text="form.type==='income'?'รายรับ':'รายจ่าย'"></p>
        </div>
      </div>

      <!-- Name -->
      <div>
        <label class="text-xs text-base-content/50 font-medium">ชื่อ *</label>
        <input type="text" x-model="form.name" placeholder="เช่น อาหาร, เดินทาง"
               class="input input-bordered input-sm w-full mt-1">
        <p x-show="errors.name" class="text-xs text-error mt-1" x-text="errors.name"></p>
      </div>

      <!-- Type -->
      <div x-show="!editId">
        <label class="text-xs text-base-content/50 font-medium">ประเภท</label>
        <div class="flex gap-2 mt-1">
          <button @click="form.type='expense'" class="flex-1 btn btn-sm"
                  :class="form.type==='expense'?'btn-error':'btn-ghost'">💸 รายจ่าย</button>
          <button @click="form.type='income'" class="flex-1 btn btn-sm"
                  :class="form.type==='income'?'btn-success':'btn-ghost'">💰 รายรับ</button>
        </div>
      </div>

      <!-- Icon + Color -->
      <div class="flex gap-3">
        <div class="flex-1">
          <label class="text-xs text-base-content/50 font-medium">Icon (emoji)</label>
          <input type="text" x-model="form.icon" maxlength="2" placeholder="📦"
                 class="input input-bordered input-sm w-full mt-1 text-center text-lg">
        </div>
        <div class="flex-1">
          <label class="text-xs text-base-content/50 font-medium">สี</label>
          <input type="color" x-model="form.color"
                 class="w-full h-9 rounded-lg border border-base-200 cursor-pointer mt-1 p-0.5">
        </div>
      </div>

    </div>

    <div class="px-4 pb-4 space-y-2">
      <button @click="save()" :disabled="saving || isDefault" class="btn btn-primary w-full">
        <span x-show="saving" class="loading loading-spinner loading-sm"></span>
        <span x-show="!saving" x-text="editId?'บันทึกการแก้ไข':'+ เพิ่มหมวดหมู่'"></span>
      </button>
      <button x-show="editId && !isDefault" @click="remove()" class="btn btn-ghost btn-sm w-full text-error">
        ลบหมวดหมู่นี้
      </button>
      <p x-show="isDefault" class="text-xs text-center text-base-content/40">หมวดหมู่เริ่มต้นไม่สามารถลบได้</p>
    </div>
  </div>
</div>

<!-- Delete confirm -->
<dialog id="cat-delete-dialog" class="modal modal-bottom">
  <div class="modal-box rounded-t-2xl rounded-b-none max-w-[480px] mx-auto">
    <h3 class="font-bold text-lg mb-1">ลบหมวดหมู่?</h3>
    <p class="text-sm text-base-content/60 mb-4">หมวดหมู่ที่มีรายการธุรกรรมผูกอยู่จะลบไม่ได้</p>
    <div class="flex gap-3">
      <form method="dialog" class="flex-1"><button class="btn btn-ghost w-full">ยกเลิก</button></form>
      <button id="cat-delete-btn" class="btn btn-error flex-1">ลบ</button>
    </div>
  </div>
  <form method="dialog" class="modal-backdrop"><button>ปิด</button></form>
</dialog>


<script>
function catPage() {
  return {
    categories: [], loading: true, tab: 'expense',

    init: function() {
      var self = this;
      this.load();
      window.addEventListener('cat-saved', function() { self.load(); });
    },

    load: async function() {
      this.loading = true;
      try {
        var r = await fetch('api/categories.php');
        var j = await r.json();
        if (j.success) this.categories = j.data;
      } catch(e) {}
      finally { this.loading = false; }
    },

    filtered: function() {
      var t = this.tab;
      return this.categories.filter(function(c){ return c.type === t; });
    },

    openSheet: function(c) {
      window.dispatchEvent(new CustomEvent('open-cat-sheet', { detail: c }));
    }
  };
}

function catSheet() {
  return {
    open: false, saving: false, editId: null, isDefault: false, errors: {},
    form: { name:'', type:'expense', icon:'📦', color:'#6366f1' },

    init: function() {
      var self = this;
      window.addEventListener('open-cat-sheet', function(e){ self.openSheet(e.detail); });
      document.getElementById('cat-delete-btn').addEventListener('click', function(){ self.doDelete(); });
    },

    openSheet: function(c) {
      this.errors = {};
      if (c) {
        this.editId    = c.id;
        this.isDefault = c.is_default == 1;
        this.form = { name: c.name, type: c.type, icon: c.icon||'📦', color: c.color||'#6366f1' };
      } else {
        this.editId    = null;
        this.isDefault = false;
        this.form = { name:'', type:'expense', icon:'📦', color:'#6366f1' };
      }
      this.open = true;
      document.body.style.overflow = 'hidden';
    },

    close: function() { this.open = false; document.body.style.overflow = ''; },

    validate: function() {
      this.errors = {};
      if (!this.form.name.trim()) this.errors.name = 'กรุณาระบุชื่อ';
      return Object.keys(this.errors).length === 0;
    },

    save: async function() {
      if (!this.validate()) return;
      this.saving = true;
      try {
        var body   = { name: this.form.name, type: this.form.type, icon: this.form.icon, color: this.form.color };
        var url    = this.editId ? 'api/categories.php?id=' + this.editId : 'api/categories.php';
        var method = this.editId ? 'PUT' : 'POST';
        var r = await fetch(url, { method: method, headers:{'Content-Type':'application/json'}, body: JSON.stringify(body) });
        var j = await r.json();
        if (j.success) {
          showToast(this.editId ? 'แก้ไขสำเร็จ' : 'เพิ่มหมวดหมู่แล้ว', 'success');
          this.close();
          window.dispatchEvent(new CustomEvent('cat-saved'));
        } else { showToast(j.message||'เกิดข้อผิดพลาด','error'); }
      } catch(e) { showToast('เกิดข้อผิดพลาด','error'); }
      finally { this.saving = false; }
    },

    remove: function() { document.getElementById('cat-delete-dialog').showModal(); },

    doDelete: async function() {
      var id = this.editId;
      if (!id) return;
      try {
        var r = await fetch('api/categories.php?id=' + id, { method:'DELETE' });
        var j = await r.json();
        if (j.success) {
          showToast('ลบหมวดหมู่แล้ว','error');
          this.close();
          window.dispatchEvent(new CustomEvent('cat-saved'));
        } else { showToast(j.message||'ลบไม่สำเร็จ','error'); }
      } catch(e) { showToast('เกิดข้อผิดพลาด','error'); }
    }
  };
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
