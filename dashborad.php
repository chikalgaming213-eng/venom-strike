<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit;
}
require_once 'config.php';

// cek auto logout inactivity (30 menit)
$inactive = 1800; // detik
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $inactive)) {
    session_unset();
    session_destroy();
    header('Location: index.php?msg=inactive');
    exit;
}
$_SESSION['last_activity'] = time();

// download dompdf otomatis jika belum ada
$dompdf_dir = 'libraries/dompdf';
if (!file_exists($dompdf_dir . '/vendor/autoload.php')) {
    // kita coba install composer lalu dompdf secara programmatically, brutal.
    // ini akan download composer.phar dan install dompdf jika composer belum ada
    if (!file_exists('composer.phar')) {
        // download composer
        copy('https://getcomposer.org/composer-stable.phar', 'composer.phar');
    }
    // jalankan composer require dompdf/dompdf di folder libraries/dompdf
    chdir('libraries/dompdf');
    exec('php ../../composer.phar require dompdf/dompdf --no-interaction 2>&1', $output, $return_var);
    chdir('../..');
    if ($return_var !== 0) {
        $dompdf_error = "gagal install dompdf otomatis: " . implode("\n", $output);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VENOM STRIKE DASHBOARD</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <header class="main-header">
            <h1 class="glitch" data-text="VENOM STRIKE PANEL">VENOM STRIKE PANEL</h1>
            <div class="user-info">
                <span>LOGGED AS: <?= htmlspecialchars($_SESSION['username']) ?></span>
                <a href="logout.php" class="btn-logout">LOGOUT</a>
            </div>
        </header>

        <div class="stats-grid" id="statsContainer">
            <div class="stat-card blood-drip">
                <div class="stat-value counter" id="totalScans">0</div>
                <div class="stat-label">TOTAL SCANS</div>
            </div>
            <div class="stat-card blood-drip">
                <div class="stat-value counter" id="targetsDown">0</div>
                <div class="stat-label">TARGETS TUMBANG</div>
            </div>
            <div class="stat-card blood-drip">
                <div class="stat-value counter" id="vulnsFound">0</div>
                <div class="stat-label">VULNERABILITIES</div>
            </div>
        </div>

        <div class="command-executor">
            <h3>COMMAND EXECUTOR (SHELL LANGSUNG)</h3>
            <textarea id="cmdInput" rows="2" placeholder="ketik command linux disini..."></textarea>
            <button id="execCmd" class="btn-blood">EKSEKUSI</button>
            <div class="cmd-history">
                <select id="cmdHistory" size="5" style="width:100%;background:#0a0a0a;color:#ff3333;"></select>
                <button id="clearHistory" class="btn-small">CLEAR HISTORY</button>
            </div>
            <div id="cmdOutput" class="output-box"></div>
        </div>

        <div class="modules-grid">
            <h3>MODULES</h3>
            <div class="module-list">
                <button class="module-btn" data-module="network-scanner">NETWORK SCANNER (NMAP -A -T4)</button>
                <button class="module-btn" data-module="port-scanner">PORT SCANNER (1-65535 SYN)</button>
                <button class="module-btn" data-module="subdomain-finder">SUBDOMAIN FINDER</button>
                <button class="module-btn" data-module="cms-detector">CMS DETECTOR</button>
                <button class="module-btn" data-module="whois-lookup">WHOIS LOOKUP</button>
                <button class="module-btn" data-module="dns-enum">DNS ENUMERATION</button>
                <button class="module-btn" data-module="reverse-ip">REVERSE IP</button>
                <button class="module-btn" data-module="hash-cracker">HASH CRACKER (MD5/SHA1/SHA256/BCRYPT)</button>
                <button class="module-btn" data-module="payload-generator">PAYLOAD GENERATOR (REVERSE SHELL)</button>
                <button class="module-btn" data-module="live-target">LIVE TARGET CHECK</button>
            </div>
        </div>

        <div id="moduleOutput" class="output-box module-output">
            <!-- output module brutal -->
        </div>

        <!-- hidden iframe untuk ajax polling? gak, kita pake fetch js -->
    </div>

    <div id="autoLogoutWarning" class="logout-warning" style="display:none;">
        <div class="warning-inner">
            <p>ANDA AKAN LOGOUT OTOMATIS DALAM <span id="logoutCountdown">60</span> DETIK. GERAKKAN MOUSE UNTUK BATAL.</p>
        </div>
    </div>

    <footer class="footer-glitch">
        <p data-text="VENOM STRIKE - NO MERCY FOR TARGETS - DEVELOPED BY KALZ">VENOM STRIKE - NO MERCY FOR TARGETS - DEVELOPED BY KALZ</p>
    </footer>

    <script src="assets/js/venom.js"></script>
</body>
</html>