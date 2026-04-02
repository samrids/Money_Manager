<?php
// หา base URL จาก path ของหน้าปัจจุบัน
$_base_url = rtrim(str_replace(basename($_SERVER['PHP_SELF']), '', $_SERVER['PHP_SELF']), '/') . '/';
$_cur_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="th" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="theme-color" content="#6366f1">
  <title><?php echo isset($page_title) ? h($page_title) . ' — Money' : 'Money'; ?></title>

  <!-- DaisyUI (includes Tailwind base) -->
  <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.14/dist/full.min.css" rel="stylesheet">
  <!-- Tailwind v3 Play CDN (utility classes) -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Alpine.js v3 -->
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
  <!-- Custom CSS -->
  <link rel="stylesheet" href="<?php echo $_base_url; ?>assets/css/app.css">

  <script>
    // DaisyUI theme config สำหรับ Tailwind CDN
    tailwind.config = {
      theme: { extend: {} },
      plugins: []
    }
  </script>
</head>
<body class="bg-base-200">

<!-- App Shell: max-width 480px, centered -->
<div class="max-w-[480px] mx-auto bg-base-100 min-h-screen flex flex-col">

  <!-- Top App Bar -->
  <header class="sticky top-0 z-30 bg-base-100 border-b border-base-200 flex items-center px-4 h-14 shrink-0">
    <?php if (isset($show_back) && $show_back): ?>
    <a href="javascript:history.back()" class="btn btn-ghost btn-sm btn-circle mr-2">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
      </svg>
    </a>
    <?php endif; ?>
    <h1 class="text-lg font-bold flex-1"><?php echo isset($page_title) ? h($page_title) : 'Money'; ?></h1>
    <?php if (isset($page_action)): ?>
    <?php echo $page_action; ?>
    <?php endif; ?>
  </header>

  <!-- Page Content (pb-20 เพื่อไม่ให้ bottom nav ทับ) -->
  <main class="flex-1 overflow-y-auto pb-24 px-4 pt-4">
