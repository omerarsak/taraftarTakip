
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Veritabanı bağlantısını test et
try {
    $conn = new PDO("mysql:host=localhost;dbname=TaraftarTakip", "root", "");
    echo "✅ Veritabanı bağlantısı BAŞARILI!<br>";
    
    // Tablo kontrolü
    $stmt = $conn->query("SELECT 1 FROM Kullanıcılar LIMIT 1");
    echo "✅ Kullanıcılar tablosu MEVCUT!<br>";
} catch(PDOException $e) {
    die("❌ HATA: " . $e->getMessage());
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "TaraftarTakip";

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $ad = $_POST['ad'];
        $soyad = $_POST['soyad'];
        $email = $_POST['email'];
        $sifre = password_hash($_POST['sifre'], PASSWORD_DEFAULT);
        $telefon = $_POST['telefon'];
        $dogum_tarihi = $_POST['dogum_tarihi'];

        $stmt = $conn->prepare("CALL KullanıcıKayit(?, ?, ?, ?, ?, ?)");
        $stmt->bindParam(1, $ad);
        $stmt->bindParam(2, $soyad);
        $stmt->bindParam(3, $email);
        $stmt->bindParam(4, $sifre);
        $stmt->bindParam(5, $telefon);
        $stmt->bindParam(6, $dogum_tarihi);
        
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<script>alert('".$result['Mesaj']."');</script>";
        
        if ($result['Mesaj'] == 'Kayıt başarılı!') {
            header("Location: login.php");
            exit();
        }
    } catch(PDOException $e) {
        echo "Hata: " . $e->getMessage();
    }
    $conn = null;
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt Ol</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 50%;
            margin: 50px auto;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="tel"],
        input[type="date"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background-color: #1a1a1a;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }
        button:hover {
            background-color: #333;
        }
        .login-link {
            text-align: center;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Kayıt Ol</h2>
        <form action="register.php" method="post">
            <div class="form-group">
                <label for="ad">Ad:</label>
                <input type="text" id="ad" name="ad" required>
            </div>
            <div class="form-group">
                <label for="soyad">Soyad:</label>
                <input type="text" id="soyad" name="soyad" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="sifre">Şifre:</label>
                <input type="password" id="sifre" name="sifre" required>
            </div>
            <div class="form-group">
                <label for="telefon">Telefon:</label>
                <input type="tel" id="telefon" name="telefon">
            </div>
            <div class="form-group">
                <label for="dogum_tarihi">Doğum Tarihi:</label>
                <input type="date" id="dogum_tarihi" name="dogum_tarihi">
            </div>
            <button type="submit">Kayıt Ol</button>
        </form>
        <div class="login-link">
            Zaten hesabınız var mı? <a href="login.php">Giriş yapın</a>
        </div>
    </div>
</body>
</html>