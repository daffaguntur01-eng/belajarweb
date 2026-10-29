<?php
// 1. Inisialisasi session
session_start();

// 2. Hapus semua variabel session
$_SESSION = array();

// 3. Hapus cookie session jika ada
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// 4. Hancurkan session secara penuh
session_destroy();

// 5. UBAH DI SINI: pastikan mengarah ke login_user.php
header("Location: login_user.php");
exit();
?>