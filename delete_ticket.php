<?php
// Veritabanı bağlantısı
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ticket_id = $_POST['ticket_id'];
    
    // Güvenlik önlemi: Kullanıcının bu bilete sahip olduğunu doğrulayın
    $stmt = $pdo->prepare("DELETE FROM tickets WHERE id = ? AND user_id = ?");
    $stmt->execute([$ticket_id, $_SESSION['user_id']]);
    
    header("Location: tickets.php"); // İşlem sonrası yönlendirme
    exit();
}
?>