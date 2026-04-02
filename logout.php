<?php
require_once __DIR__ . '/includes/auth.php';

send_no_cache_headers();
logout_user();
redirect_to('login.php');
