  </main><!-- /main -->

</div><!-- /app shell -->

<!-- ───────────────────────────────────────────────────────────────
     BOTTOM NAVIGATION BAR
─────────────────────────────────────────────────────────────────── -->
<nav class="fixed bottom-0 left-0 right-0 z-40 bg-base-100 border-t border-base-200"
     style="padding-bottom: env(safe-area-inset-bottom, 0px);">
  <div class="max-w-[480px] mx-auto flex items-end justify-around h-16">

    <!-- Dashboard -->
    <a href="<?php echo $_base_url; ?>index.php"
       class="nav-tab flex flex-col items-center justify-center gap-0.5 flex-1 h-full
              <?php echo ($_cur_page === 'index.php') ? 'text-primary' : 'text-base-content/50'; ?>">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
      </svg>
      <span class="text-[10px] font-medium leading-none">หน้าหลัก</span>
    </a>

    <!-- Transactions -->
    <a href="<?php echo $_base_url; ?>transactions.php"
       class="nav-tab flex flex-col items-center justify-center gap-0.5 flex-1 h-full
              <?php echo ($_cur_page === 'transactions.php') ? 'text-primary' : 'text-base-content/50'; ?>">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
      </svg>
      <span class="text-[10px] font-medium leading-none">รายการ</span>
    </a>

    <!-- FAB — Add Transaction -->
    <button onclick="openAddTransaction()"
            class="fab-btn flex-1 flex flex-col items-center justify-center -mt-5">
      <div class="w-14 h-14 rounded-full bg-primary shadow-lg flex items-center justify-center text-primary-content">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
      </div>
    </button>

    <!-- Accounts -->
    <a href="<?php echo $_base_url; ?>accounts.php"
       class="nav-tab flex flex-col items-center justify-center gap-0.5 flex-1 h-full
              <?php echo ($_cur_page === 'accounts.php') ? 'text-primary' : 'text-base-content/50'; ?>">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
      </svg>
      <span class="text-[10px] font-medium leading-none">บัญชี</span>
    </a>

    <!-- More -->
    <button onclick="document.getElementById('more-menu').showModal()"
            class="nav-tab flex flex-col items-center justify-center gap-0.5 flex-1 h-full
                   <?php echo in_array($_cur_page, array('categories.php','budgets.php','reports.php','settings.php')) ? 'text-primary' : 'text-base-content/50'; ?>">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
      </svg>
      <span class="text-[10px] font-medium leading-none">เพิ่มเติม</span>
    </button>

  </div>
</nav>

<!-- ───────────────────────────────────────────────────────────────
     MORE MENU MODAL
─────────────────────────────────────────────────────────────────── -->
<dialog id="more-menu" class="modal modal-bottom">
  <div class="modal-box rounded-t-2xl rounded-b-none max-w-[480px] mx-auto p-0 pb-safe">
    <div class="p-4 border-b border-base-200 flex items-center justify-between">
      <h3 class="font-bold text-base">เมนูเพิ่มเติม</h3>
      <form method="dialog"><button class="btn btn-ghost btn-sm btn-circle">✕</button></form>
    </div>
    <ul class="menu p-4 gap-1">
      <li>
        <a href="<?php echo $_base_url; ?>settings.php" class="flex items-center gap-3 py-3 text-base
           <?php echo ($_cur_page === 'settings.php') ? 'active' : ''; ?>">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317a1.724 1.724 0 013.35 0 1.724 1.724 0 002.573 1.066 1.724 1.724 0 012.897 1.675 1.724 1.724 0 001.066 2.573 1.724 1.724 0 010 3.35 1.724 1.724 0 00-1.066 2.573 1.724 1.724 0 01-2.897 1.675 1.724 1.724 0 00-2.573 1.066 1.724 1.724 0 01-3.35 0 1.724 1.724 0 00-2.573-1.066 1.724 1.724 0 01-2.897-1.675 1.724 1.724 0 00-1.066-2.573 1.724 1.724 0 010-3.35 1.724 1.724 0 001.066-2.573 1.724 1.724 0 012.897-1.675 1.724 1.724 0 002.573-1.066z"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15a3 3 0 100-6 3 3 0 000 6z"/>
          </svg>
          ตั้งค่า
        </a>
      </li>
      <li>
        <a href="<?php echo $_base_url; ?>budgets.php" class="flex items-center gap-3 py-3 text-base
           <?php echo ($_cur_page === 'budgets.php') ? 'active' : ''; ?>">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
          </svg>
          งบประมาณ
        </a>
      </li>
      <li>
        <a href="<?php echo $_base_url; ?>categories.php" class="flex items-center gap-3 py-3 text-base
           <?php echo ($_cur_page === 'categories.php') ? 'active' : ''; ?>">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/>
          </svg>
          หมวดหมู่
        </a>
      </li>
      <li>
        <a href="<?php echo $_base_url; ?>reports.php" class="flex items-center gap-3 py-3 text-base
           <?php echo ($_cur_page === 'reports.php') ? 'active' : ''; ?>">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
          รายงาน
        </a>
      </li>
    </ul>
  </div>
  <form method="dialog" class="modal-backdrop"><button>ปิด</button></form>
</dialog>

<!-- ───────────────────────────────────────────────────────────────
     GLOBAL TOAST
─────────────────────────────────────────────────────────────────── -->
<div id="toast-container"
     class="fixed top-4 left-1/2 -translate-x-1/2 z-50 flex flex-col gap-2 w-[calc(100%-2rem)] max-w-[440px] pointer-events-none">
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script src="<?php echo $_base_url; ?>assets/js/charts.js"></script>

<script>
// ─── Toast System ─────────────────────────────────────────────────
function showToast(message, type) {
  type = type || 'success';
  var container = document.getElementById('toast-container');
  var toast = document.createElement('div');
  var alertClass = type === 'error' ? 'alert-error' : type === 'warning' ? 'alert-warning' : 'alert-success';
  toast.className = 'alert ' + alertClass + ' shadow-lg pointer-events-auto text-sm py-3';
  toast.style.animation = 'slideDown 0.3s ease-out';
  toast.innerHTML = '<span>' + message + '</span>';
  container.appendChild(toast);
  setTimeout(function() {
    toast.style.animation = 'fadeOut 0.3s ease-out';
    setTimeout(function() { container.removeChild(toast); }, 300);
  }, 3000);
}

// ─── FAB: openAddTransaction ──────────────────────────────────────
// ส่ง event ไปยังหน้าที่รองรับ quick add ก่อน ถ้าไม่มี handler ค่อย fallback ไปหน้า transactions.php
function openAddTransaction() {
  var request = { handled: false };
  window.dispatchEvent(new CustomEvent('open-add-tx', { detail: request }));

  if (!request.handled) {
    window.location.href = '<?php echo $_base_url; ?>transactions.php';
  }
}
</script>
</body>
</html>
