<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Futbol Takımı Taraftar Sistemi</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #1a1a1a, #333);
            color: white;
            height: 100vh;
            overflow: hidden;
        }
        .container {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }
        .header {
            margin-bottom: 30px;
        }
        .header h1 {
            font-size: 3rem;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        .auth-buttons {
            margin-top: 30px;
        }
        .btn {
            display: inline-block;
            padding: 12px 25px;
            margin: 0 10px;
            background: #ff5722;
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: bold;
            transition: all 0.3s;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .btn:hover {
            background: #ff7043;
            transform: translateY(-3px);
            box-shadow: 0 6px 8px rgba(0,0,0,0.2);
        }
        
        /* Futbol topu animasyonu */
        .football {
            width: 100px;
            height: 100px;
            background: url('https://cdn.pixabay.com/photo/2013/07/12/14/53/football-148635_960_720.png') no-repeat center center;
            background-size: contain;
            position: absolute;
            animation: bounce 3s infinite ease-in-out;
        }
        
        @keyframes bounce {
            0%, 100% {
                transform: translateY(0) rotate(0deg);
            }
            25% {
                transform: translateY(-100px) rotate(90deg);
            }
            50% {
                transform: translateY(0) rotate(180deg);
            }
            75% {
                transform: translateY(-50px) rotate(270deg);
            }
        }
        
        /* Topun farklı konumları */
        .football-1 {
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }
        .football-2 {
            top: 70%;
            left: 80%;
            animation-delay: 0.5s;
        }
        .football-3 {
            top: 40%;
            left: 60%;
            animation-delay: 1s;
        }
    </style>
</head>
<body>
    <!-- Futbol topu animasyonları -->
    <div class="football football-1"></div>
    <div class="football football-2"></div>
    <div class="football football-3"></div>
    
    <div class="container">
        <div class="header">
            <h1>Futbol Takımı Taraftar Sistemi</h1>
            <p>Takımınıza destek olun, maçları kaçırmayın!</p>
        </div>
        
        <div class="auth-buttons">
            <a href="login.php" class="btn">Giriş Yap</a>
            <a href="register.php" class="btn">Kayıt Ol</a>
        </div>
    </div>
</body>
</html>