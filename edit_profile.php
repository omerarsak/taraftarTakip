<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Kullanıcı bilgilerini getir
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $stmt = $conn->prepare("SELECT * FROM Kullanıcılar WHERE KullanıcıID = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        $error = "Kullanıcı bulunamadı!";
    }
} catch(PDOException $e) {
    $error = "Veritabanı hatası: " . $e->getMessage();
}

// Profil güncelleme işlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $new_name = $_POST['name'];
    $new_surname = $_POST['surname'];
    $new_phone = $_POST['phone'];
    $new_birthdate = $_POST['birthdate'];
    
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $stmt = $conn->prepare("UPDATE Kullanıcılar SET Ad = ?, Soyad = ?, Telefon = ?, DoğumTarihi = ? WHERE KullanıcıID = ?");
        $stmt->execute([$new_name, $new_surname, $new_phone, $new_birthdate, $user_id]);
        
        if ($stmt->rowCount() > 0) {
            $message = "Profil bilgileriniz başarıyla güncellendi!";
            // Sayfayı yenilemek için bilgileri tekrar çek
            $stmt = $conn->prepare("SELECT * FROM Kullanıcılar WHERE KullanıcıID = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $error = "Herhangi bir değişiklik yapılmadı.";
        }
    } catch(PDOException $e) {
        $error = "Güncelleme hatası: " . $e->getMessage();
    }
}

// Hesap silme işlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_account'])) {
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        
        // Transaction başlat
        $conn->beginTransaction();
        
        // Önce bağımlı kayıtları sil (biletler vb.)
        $stmt = $conn->prepare("DELETE FROM Biletler WHERE KullanıcıID = ?");
        $stmt->execute([$user_id]);
        
        // Sonra kullanıcıyı sil
        $stmt = $conn->prepare("DELETE FROM Kullanıcılar WHERE KullanıcıID = ?");
        $stmt->execute([$user_id]);
        
        if ($stmt->rowCount() > 0) {
            $conn->commit();
            // Oturumu sonlandır ve giriş sayfasına yönlendir
            session_destroy();
            header("Location: login.php?message=Hesabınız başarıyla silindi");
            exit();
        } else {
            $conn->rollBack();
            $error = "Hesap silinemedi!";
        }
    } catch(PDOException $e) {
        $conn->rollBack();
        $error = "Silme işlemi sırasında hata: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Düzenle</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn {
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-right: 10px;
        }
        .btn-primary {
            background-color: #4CAF50;
            color: white;
        }
        .btn-danger {
            background-color: #f44336;
            color: white;
        }
        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .alert-success {
            background-color: #dff0d8;
            color: #3c763d;
        }
        .alert-error {
            background-color: #f2dede;
            color: #a94442;
        }
        .delete-section {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Profil Düzenle</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?= $message ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="post">
            <div class="form-group">
                <label for="name">Ad:</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['Ad']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="surname">Soyad:</label>
                <input type="text" id="surname" name="surname" value="<?= htmlspecialchars($user['Soyad']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">E-posta (değiştirilemez):</label>
                <input type="email" id="email" value="<?= htmlspecialchars($user['Email']) ?>" disabled>
            </div>
            
            <div class="form-group">
                <label for="phone">Telefon:</label>
                <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($user['Telefon']) ?>">
            </div>
            
            <div class="form-group">
                <label for="birthdate">Doğum Tarihi:</label>
                <input type="date" id="birthdate" name="birthdate" value="<?= htmlspecialchars($user['DoğumTarihi']) ?>">
            </div>
            
            <button type="submit" name="update_profile" class="btn btn-primary">
                <i class="fas fa-save"></i> Bilgileri Güncelle
            </button>
            <a href="dashboard.php" class="btn">
                <i class="fas fa-arrow-left"></i> Geri Dön
            </a>
        </form>
        
        <div class="delete-section">
            <h3>Hesap Silme</h3>
            <p>Hesabınızı silmek istediğinizden emin misiniz? Bu işlem geri alınamaz!</p>
            
            <form method="post" onsubmit="return confirm('Hesabınızı silmek istediğinizden emin misiniz? Bu işlem geri alınamaz!');">
                <button type="submit" name="delete_account" class="btn btn-danger">
                    <i class="fas fa-trash-alt"></i> Hesabımı Sil
                </button>
            </form>
        </div>
    </div>
</body>
</html>