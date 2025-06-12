<?php
include 'db_connection.php';

$ticket_id = $_GET['id'];
// Güvenlik önlemi: Kullanıcının bu bilete sahip olduğunu doğrulayın
$stmt = $pdo->prepare("SELECT * FROM tickets WHERE id = ? AND user_id = ?");
$stmt->execute([$ticket_id, $_SESSION['user_id']]);
$ticket = $stmt->fetch();

if (!$ticket) {
    die("Bilet bulunamadı veya erişim izniniz yok.");
}

// Form gönderildiğinde güncelleme işlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_seat = $_POST['new_seat'];
    // Güncelleme sorgusu
    $stmt = $pdo->prepare("UPDATE tickets SET KoltukNumarasi = ? WHERE id = ?");
    $stmt->execute([$new_seat, $ticket_id]);
    
    header("Location: tickets.php");
    exit();
}
?>

<!-- Güncelleme formu -->
<form method="POST">
    <input type="text" name="new_seat" value="<?= htmlspecialchars($ticket['KoltukNumarasi']) ?>">
    <button type="submit">Güncelle</button>
</form>