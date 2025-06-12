<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    
    // Kullanıcının biletlerini getir
    $stmt = $conn->prepare("SELECT b.*, m.EvSahibiTakım, m.DeplasmanTakım, m.MaçTarihi, 
                           s.Ad AS StadyumAdı, DATEDIFF(m.MaçTarihi, NOW()) AS KalanGun
                           FROM biletler b
                           JOIN maçlar m ON b.MaçID = m.MaçID
                           JOIN stadyumlar s ON m.StadyumID = s.StadyumID
                           WHERE b.KullanıcıID = ?
                           ORDER BY m.MaçTarihi");
    $stmt->execute([$user_id]);
    $biletler = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $hata = "Biletler yüklenirken hata oluştu: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biletlerim</title>
    <style>
        .bilet-container { display: flex; flex-wrap: wrap; gap: 20px; }
        .bilet-card { 
            border: 1px solid #ddd; border-radius: 8px; 
            padding: 15px; width: 300px; position: relative;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .bilet-header { 
            background-color: #1a1a1a; color: white; 
            padding: 10px; margin: -15px -15px 15px -15px;
            border-radius: 8px 8px 0 0;
        }
        .action-buttons { margin-top: 15px; display: flex; gap: 10px; }
        .btn { 
            padding: 8px 12px; border: none; border-radius: 4px;
            cursor: pointer; text-decoration: none; display: inline-block;
        }
        .btn-edit { background-color: #4CAF50; color: white; }
        .btn-delete { background-color: #f44336; color: white; }
        .btn-disabled { background-color: #cccccc; cursor: not-allowed; }
        .edit-form { display: none; margin-top: 15px; padding: 15px; background: #f9f9f9; }
        .form-group { margin-bottom: 10px; }
        .form-group label { display: block; margin-bottom: 5px; }
        .form-group input { width: 100%; padding: 8px; box-sizing: border-box; }
        .info-text { font-style: italic; color: #666; margin-top: 5px; }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container">
        <h1>Biletlerim</h1>
        
        <?php if (isset($_SESSION['mesaj'])): ?>
            <div class="alert success"><?= $_SESSION['mesaj'] ?></div>
            <?php unset($_SESSION['mesaj']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['hata'])): ?>
            <div class="alert error"><?= $_SESSION['hata'] ?></div>
            <?php unset($_SESSION['hata']); ?>
        <?php endif; ?>
        
        <div class="bilet-container">
            <?php foreach ($biletler as $bilet): ?>
                <div class="bilet-card">
                    <div class="bilet-header">
                        <h3><?= htmlspecialchars($bilet['EvSahibiTakım']) ?> vs <?= htmlspecialchars($bilet['DeplasmanTakım']) ?></h3>
                    </div>
                    
                    <p><strong>Tarih:</strong> <?= htmlspecialchars($bilet['MaçTarihi']) ?></p>
                    <p><strong>Stadyum:</strong> <?= htmlspecialchars($bilet['StadyumAdı']) ?></p>
                    <p><strong>Koltuk No:</strong> <?= htmlspecialchars($bilet['KoltukNumarası']) ?></p>
                    <p><strong>Fiyat:</strong> <?= htmlspecialchars($bilet['Fiyat']) ?> TL</p>
                    <p class="info-text">
                        <?= ($bilet['KalanGun'] > 3) ? 
                            "Değişiklik için " . $bilet['KalanGun'] . " gününüz var" : 
                            "Maça " . $bilet['KalanGun'] . " gün kaldığı için değişiklik yapılamaz" ?>
                    </p>
                    
                    <div class="action-buttons">
                        <button class="btn btn-edit" 
                                onclick="toggleEditForm(<?= $bilet['BiletID'] ?>)"
                                <?= ($bilet['KalanGun'] <= 3) ? 'disabled' : '' ?>>
                            Düzenle
                        </button>
                        
                        <form method="post" style="display:inline;" 
                              onsubmit="return confirm('Bu bileti iptal etmek istediğinize emin misiniz?');">
                            <input type="hidden" name="bilet_id" value="<?= $bilet['BiletID'] ?>">
                            <button type="submit" name="bilet_sil" class="btn btn-delete"
                                    <?= ($bilet['KalanGun'] <= 3) ? 'disabled' : '' ?>>
                                İptal Et
                            </button>
                        </form>
                    </div>
                    
                    <div id="edit-form-<?= $bilet['BiletID'] ?>" class="edit-form">
                        <form method="post">
                            <input type="hidden" name="bilet_id" value="<?= $bilet['BiletID'] ?>">
                            
                            <div class="form-group">
                                <label for="yeni_koltuk">Yeni Koltuk No:</label>
                                <input type="text" id="yeni_koltuk" name="yeni_koltuk" 
                                       value="<?= htmlspecialchars($bilet['KoltukNumarası']) ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="yeni_fiyat">Fiyat (TL):</label>
                                <input type="number" step="0.01" id="yeni_fiyat" name="yeni_fiyat" 
                                       value="<?= htmlspecialchars($bilet['Fiyat']) ?>" required>
                            </div>
                            
                            <button type="submit" name="bilet_guncelle" class="btn btn-edit">
                                Güncelle
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <script>
        function toggleEditForm(biletId) {
            const form = document.getElementById(`edit-form-${biletId}`);
            form.style.display = form.style.display === 'block' ? 'none' : 'block';
        }
    </script>
</body>
</html>
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $_SESSION['mesaj'] = $result['Mesaj'];
    } catch(PDOException $e) {
        $_SESSION['hata'] = "Hata: " . $e->getMessage();
    }
    header("Location: biletlerim.php");
    exit();
}

// Kullanıcının biletlerini getir
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $stmt = $conn->prepare("CALL KullanıcıBiletleri(?)");
    $stmt->execute([$user_id]);
    $biletler = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $hata = "Biletler yüklenirken hata oluştu: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Bilet Yönetimi</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
        .bilet-card { border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; border-radius: 5px; }
        .form-group { margin-bottom: 10px; }
        button { padding: 5px 10px; cursor: pointer; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>Biletlerim</h1>
    
    <?php if (isset($_SESSION['mesaj'])): ?>
        <div class="success"><?= $_SESSION['mesaj'] ?></div>
        <?php unset($_SESSION['mesaj']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['hata'])): ?>
        <div class="error"><?= $_SESSION['hata'] ?></div>
        <?php unset($_SESSION['hata']); ?>
    <?php endif; ?>
    
    <?php if (isset($hata)): ?>
        <div class="error"><?= $hata ?></div>
    <?php endif; ?>
    
    <?php foreach ($biletler as $bilet): ?>
        <div class="bilet-card">
            <h3><?= htmlspecialchars($bilet['EvSahibiTakım']) ?> vs <?= htmlspecialchars($bilet['DeplasmanTakım']) ?></h3>
            <p>Tarih: <?= htmlspecialchars($bilet['MaçTarihi']) ?></p>
            <p>Stadyum: <?= htmlspecialchars($bilet['StadyumAdı']) ?></p>
            <p>Koltuk: <?= htmlspecialchars($bilet['KoltukNumarası']) ?></p>
            <p>Fiyat: <?= htmlspecialchars($bilet['Fiyat']) ?> TL</p>
            
            <!-- Bilet Silme Formu -->
            <form method="post" onsubmit="return confirm('Bu bileti silmek istediğinize emin misiniz?');">
                <input type="hidden" name="bilet_id" value="<?= $bilet['BiletID'] ?>">
                <button type="submit" name="bilet_sil">Bileti İptal Et</button>
            </form>
            
            <!-- Bilet Güncelleme Formu -->
            <form method="post">
                <input type="hidden" name="bilet_id" value="<?= $bilet['BiletID'] ?>">
                <div class="form-group">
                    <label>Yeni Koltuk No:</label>
                    <input type="text" name="yeni_koltuk" value="<?= htmlspecialchars($bilet['KoltukNumarası']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Fiyat (TL):</label>
                    <input type="number" step="0.01" name="yeni_fiyat" value="<?= htmlspecialchars($bilet['Fiyat']) ?>" required>
                </div>
                <button type="submit" name="bilet_guncelle">Bileti Güncelle</button>
            </form>
        </div>
    <?php endforeach; ?>
    
    <a href="dashboard.php">Geri Dön</a>
</body>
</html>