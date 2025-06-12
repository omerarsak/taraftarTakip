<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_config.php';

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $stmt = $conn->prepare("SELECT b.*, m.EvSahibiTakım, m.DeplasmanTakım, m.MaçTarihi 
                           FROM Biletler b 
                           JOIN Maçlar m ON b.MaçID = m.MaçID
                           WHERE b.KullanıcıID = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $tickets = $stmt->fetchAll();
} catch(PDOException $e) {
    echo "Hata: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Biletlerim</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container">
        <h1>Biletlerim</h1>
        <?php if (count($tickets) > 0): ?>
            <div class="tickets-grid">
                <?php foreach ($tickets as $ticket): ?>
                    <div class="ticket-card">
                        <h3><?= htmlspecialchars($ticket['EvSahibiTakım']) ?> vs <?= htmlspecialchars($ticket['DeplasmanTakım']) ?></h3>
                        <p><strong>Tarih:</strong> <?= htmlspecialchars($ticket['MaçTarihi']) ?></p>
                        <p><strong>Koltuk:</strong> <?= htmlspecialchars($ticket['KoltukNumarası']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>Henüz biletiniz bulunmamaktadır.</p>
        <?php endif; ?>
    </div>
</body>
</html>
