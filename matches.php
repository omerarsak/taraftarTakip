<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_config.php'; // Veritabanı bağlantısı için

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $stmt = $conn->query("SELECT * FROM Maçlar ORDER BY MaçTarihi");
    $matches = $stmt->fetchAll();
} catch(PDOException $e) {
    echo "Hata: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Maçlar</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container">
        <h1>Yaklaşan Maçlar</h1>
        <div class="matches-grid">
            <?php foreach ($matches as $match): ?>
                <div class="match-card">
                    <h3><?= htmlspecialchars($match['EvSahibiTakım']) ?> vs <?= htmlspecialchars($match['DeplasmanTakım']) ?></h3>
                    <p><?= htmlspecialchars($match['MaçTarihi']) ?></p>
                    <a href="buy_ticket.php?match_id=<?= $match['MaçID'] ?>" class="btn">Bilet Al</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>