<?php
session_start();
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header('Location: dashboard.php');
    exit;
}
require_once 'config.php';

$error = '';
$lockout_message = '';

// cek brute force lockout
$ip = $_SERVER['REMOTE_ADDR'];
$ua = $_SERVER['HTTP_USER_AGENT'];
$lock_minutes = 15;
$max_attempts = 5;

$stmt = $db->prepare("SELECT COUNT(*) as attempts, MAX(attempt_time) as last_attempt FROM login_attempts WHERE ip = ? AND attempt_time > datetime('now', '-" . $lock_minutes . " minutes')");
$stmt->execute([$ip]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row['attempts'] >= $max_attempts) {
    $lockout_message = "akun terkunci selama " . $lock_minutes . " menit karena terlalu banyak percobaan gagal. kembali lagi nanti, sampah.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($lockout_message)) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        // double md5 hash sesuai permintaan brutal
        $hashed = md5(md5($password));
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
        $stmt->execute([$username, $hashed]);
        $user = $stmt->fetch();

        if ($user) {
            // sukses login
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $username;
            // hapus attempt yang sukses
            $stmt = $db->prepare("DELETE FROM login_attempts WHERE ip = ?");
            $stmt->execute([$ip]);
            header('Location: dashboard.php');
            exit;
        } else {
            // gagal login
            $error = "username atau password salah, goblok.";
            // log gagal
            $stmt = $db->prepare("INSERT INTO login_attempts (ip, user_agent, attempt_time) VALUES (?, ?, datetime('now'))");
            $stmt->execute([$ip, $ua]);

            // tambah ke log file juga
            $log = date('Y-m-d H:i:s') . " - FAILED LOGIN - IP: $ip - UA: $ua\n";
            file_put_contents('logs/login_attempts.txt', $log, FILE_APPEND);
        }
    } else {
        $error = "isi username dan password, jangan kosong.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VENOM STRIKE PANEL - LOGIN</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <h1 class="glitch" data-text="VENOM STRIKE">VENOM STRIKE</h1>
        <p class="subtitle">Akses Panel Brutal</p>
        <?php if ($lockout_message): ?>
            <div class="error-box"><?= $lockout_message ?></div>
        <?php elseif ($error): ?>
            <div class="error-box"><?= $error ?></div>
        <?php endif; ?>
        <form method="POST" id="loginForm">
            <div class="input-group">
                <label>USERNAME</label>
                <input type="text" name="username" id="username" autocomplete="off">
            </div>
            <div class="input-group">
                <label>PASSWORD</label>
                <input type="password" name="password" id="password">
            </div>
            <button type="submit" class="btn-blood">MASUK KE SISTEM</button>
        </form>
        <div class="footer-note">NO MERCY FOR TARGETS</div>
    </div>
    <script src="assets/js/venom.js"></script>
</body>
</html>