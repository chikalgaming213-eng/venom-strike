<?php
// database sqlite auto create
$db_path = 'database/venom.db';
try {
    $db = new PDO("sqlite:$db_path");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // buat tabel users kalo belum ada
    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL
    )");

    // buat tabel login_attempts
    $db->exec("CREATE TABLE IF NOT EXISTS login_attempts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        ip TEXT,
        user_agent TEXT,
        attempt_time DATETIME
    )");

    // insert default user kalo belum ada (username: venom, password: strike2024, double md5)
    $stmt = $db->prepare("SELECT COUNT(*) FROM users");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $default_password = md5(md5('strike2024'));
        $stmt = $db->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->execute(['venom', $default_password]);
    }

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>