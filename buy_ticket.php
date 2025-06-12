<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['match_id'])) {
    header("Location: dashboard.php");
    exit();
}

$match_id = $_GET['match_id'];

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "TaraftarTakip";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Maç bilgilerini al
    $stmt = $conn->prepare("SELECT m.*, s.Ad AS StadyumAdı, s.Kapasite 
                          FROM Maçlar m 
                          JOIN Stadyumlar s ON m.StadyumID = s.StadyumID
                          WHERE m.MaçID = ?");
    $stmt->execute([$match_id]);
    $match = $stmt->fetch();

    if (!$match) {
        header("Location: dashboard.php");
        exit();
    }

    // Dolu koltukları al
    $stmt = $conn->prepare("SELECT KoltukNumarası FROM Biletler WHERE MaçID = ?");
    $stmt->execute([$match_id]);
    $taken_seats = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['seat'])) {
        $seat = $_POST['seat'];
        
        try {
            $stmt = $conn->prepare("CALL BiletSatınAl(?, ?, ?)");
            $stmt->bindParam(1, $_SESSION['user_id'], PDO::PARAM_INT);
            $stmt->bindParam(2, $match_id, PDO::PARAM_INT);
            $stmt->bindParam(3, $seat, PDO::PARAM_STR);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $message = $result['Mesaj'];
            
            if ($message == 'Bilet alımı başarılı!') {
                header("Location: dashboard.php");
                exit();
            }
        } catch(PDOException $e) {
            $error = "Hata: " . $e->getMessage();
        }
    }
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
    <title>Bilet Satın Al</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: 30px auto;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #333;
        }
        .match-info {
            margin-bottom: 20px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 5px;
        }
        .seat-selection {
            display: grid;
            grid-template-columns: repeat(10, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }
        .seat {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #4CAF50;
            color: white;
            border-radius: 5px;
            cursor: pointer;
        }
        .seat.taken {
            background-color: #f44336;
            cursor: not-allowed;
        }
        .seat.selected {
            background-color: #2196F3;
        }
        .btn {
            background-color: #1a1a1a;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn:hover {
            background-color: #333;
        }
        .btn:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }
        .error {
            color: red;
            text-align: center;
            margin-bottom: 15px;
        }
        .success {
            color: green;
            text-align: center;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Bilet Satın Al</h2>
        
        <div class="match-info">
            <h3><?php echo htmlspecialchars($match['EvSahibiTakım']); ?> vs <?php echo htmlspecialchars($match['DeplasmanTakım']); ?></h3>
            <p><strong>Stadyum:</strong> <?php echo htmlspecialchars($match['StadyumAdı']); ?></p>
            <p><strong>Tarih:</strong> <?php echo htmlspecialchars($match['MaçTarihi']); ?></p>
            <p><strong>Bilet Fiyatı:</strong> <?php echo htmlspecialchars($match['BiletFiyatı']); ?> TL</p>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (isset($message)): ?>
            <div class="success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <h3>Koltuk Seçimi</h3>
        <form action="buy_ticket.php?match_id=<?php echo $match_id; ?>" method="post">
            <div class="seat-selection">
                <?php for ($i = 1; $i <= $match['Kapasite']; $i++): ?>
                    <?php $seat_num = "A-" . $i; ?>
                    <?php if (in_array($seat_num, $taken_seats)): ?>
                        <div class="seat taken" title="Dolu"><?php echo $i; ?></div>
                    <?php else: ?>
                        <button type="submit" name="seat" value="<?php echo $seat_num; ?>" class="seat"><?php echo $i; ?></button>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>
        </form>
        
        <a href="dashboard.php" class="btn">Geri Dön</a>
    </div>
</body>
</html>