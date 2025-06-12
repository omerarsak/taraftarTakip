<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "TaraftarTakip";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Kullanıcı bilgilerini al
    $stmt = $conn->prepare("SELECT * FROM Kullanıcılar WHERE KullanıcıID = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    // Maçları al
    $stmt = $conn->query("SELECT * FROM Maçlar ORDER BY MaçTarihi");
    $matches = $stmt->fetchAll();

    // Etkinlikleri al
    $stmt = $conn->query("SELECT * FROM Etkinlikler ORDER BY Tarih");
    $events = $stmt->fetchAll();

    // Kullanıcının biletlerini al
    $stmt = $conn->prepare("SELECT b.*, m.EvSahibiTakım, m.DeplasmanTakım, m.MaçTarihi, s.Ad AS StadyumAdı 
                           FROM Biletler b 
                           JOIN Maçlar m ON b.MaçID = m.MaçID 
                           JOIN Stadyumlar s ON m.StadyumID = s.StadyumID
                           WHERE b.KullanıcıID = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $tickets = $stmt->fetchAll();
} catch(PDOException $e) {
    echo "Bağlantı hatası: " . $e->getMessage();
}
$conn = null;
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kullanıcı Paneli</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .header {
            background-color: #1a1a1a;
            color: white;
            padding: 15px 0;
            text-align: center;
        }
        .container {
            width: 90%;
            margin: 20px auto;
        }
        .welcome {
            text-align: center;
            margin-bottom: 20px;
        }
        .nav {
            background-color: #333;
            overflow: hidden;
        }
        .nav a {
            float: left;
            display: block;
            color: white;
            text-align: center;
            padding: 14px 16px;
            text-decoration: none;
        }
        .nav a:hover {
            background-color: #ddd;
            color: black;
        }
        .section {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
        }
        .section h2 {
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .match, .event, .ticket {
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .btn {
            background-color: #1a1a1a;
            color: white;
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover {
            background-color: #333;
        }
        .logout {
            float: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Futbol Takımı Taraftar Sistemi</h1>
    </div>

    <div class="nav">
    <a href="index.php" class="btn-home">Anasayfa</a>
    <a href="#profile">Profilim</a>
    <a href="matches.php">Maçlar</a>
    <a href="events.php">Etkinlikler</a>
    <a href="tickets.php">Biletlerim</a>
    <a href="logout.php" class="logout">Çıkış Yap</a>
</div>

    <div class="container">
        <div class="welcome">
            <h2>Hoşgeldiniz, <?php echo htmlspecialchars($user['Ad'] . ' ' . $user['Soyad']); ?></h2>
        </div>

        <div id="profile" class="section">
            <h2>Profil Bilgileri</h2>
            <p><strong>Ad:</strong> <?php echo htmlspecialchars($user['Ad']); ?></p>
            <p><strong>Soyad:</strong> <?php echo htmlspecialchars($user['Soyad']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['Email']); ?></p>
            <p><strong>Telefon:</strong> <?php echo htmlspecialchars($user['Telefon']); ?></p>
            <p><strong>Doğum Tarihi:</strong> <?php echo htmlspecialchars($user['DoğumTarihi']); ?></p>
            <a href="edit_profile.php" class="btn">
            <i class="fas fa-user-edit"></i> Profili Düzenle
            </a>
        </div>

        <div id="matches" class="section">
            <h2>Yaklaşan Maçlar</h2>
            <?php foreach ($matches as $match): ?>
                <div class="match">
                    <h3><?php echo htmlspecialchars($match['EvSahibiTakım']); ?> vs <?php echo htmlspecialchars($match['DeplasmanTakım']); ?></h3>
                    <p><strong>Tarih:</strong> <?php echo htmlspecialchars($match['MaçTarihi']); ?></p>
                    <p><strong>Bilet Fiyatı:</strong> <?php echo htmlspecialchars($match['BiletFiyatı']); ?> TL</p>
                    <a href="buy_ticket.php?match_id=<?php echo $match['MaçID']; ?>" class="btn">Bilet Al</a>
                </div>
            <?php endforeach; ?>
        </div>

        <div id="events" class="section">
            <h2>Yaklaşan Etkinlikler</h2>
            <?php foreach ($events as $event): ?>
                <div class="event">
                    <h3><?php echo htmlspecialchars($event['Ad']); ?></h3>
                    <p><?php echo htmlspecialchars($event['Açıklama']); ?></p>
                    <p><strong>Tarih:</strong> <?php echo htmlspecialchars($event['Tarih']); ?></p>
                    <p><strong>Yer:</strong> <?php echo htmlspecialchars($event['Yer']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <div id="tickets" class="section">
            <h2>Biletlerim</h2>
            <?php if (count($tickets) > 0): ?>
                <?php foreach ($tickets as $ticket): ?>
                    <div class="ticket">
                        <h3><?php echo htmlspecialchars($ticket['EvSahibiTakım']); ?> vs <?php echo htmlspecialchars($ticket['DeplasmanTakım']); ?></h3>
                        <p><strong>Stadyum:</strong> <?php echo htmlspecialchars($ticket['StadyumAdı']); ?></p>
                        <p><strong>Tarih:</strong> <?php echo htmlspecialchars($ticket['MaçTarihi']); ?></p>
                        <p><strong>Koltuk No:</strong> <?php echo htmlspecialchars($ticket['KoltukNumarası']); ?></p>
                        <p><strong>Fiyat:</strong> <?php echo htmlspecialchars($ticket['Fiyat']); ?> TL</p>
                        <p><strong>Satın Alma Tarihi:</strong> <?php echo htmlspecialchars($ticket['SatınAlmaTarihi']); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Henüz biletiniz bulunmamaktadır.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>