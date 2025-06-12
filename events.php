<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_config.php';

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $stmt = $conn->query("SELECT * FROM Etkinlikler ORDER BY Tarih");
    $events = $stmt->fetchAll();
} catch(PDOException $e) {
    echo "Hata: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Etkinlikler</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container">
        <h1>Yaklaşan Etkinlikler</h1>
        <div class="events-list">
            <?php foreach ($events as $event): ?>
                <div class="event-card">
                    <h3><?= htmlspecialchars($event['Ad']) ?></h3>
                    <p><?= htmlspecialchars($event['Açıklama']) ?></p>
                    <p><strong>Tarih:</strong> <?= htmlspecialchars($event['Tarih']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>