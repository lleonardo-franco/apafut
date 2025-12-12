<?php
session_start();
require_once 'config/security-headers.php';
require_once 'config/db.php';
require_once 'src/Security.php';

// Validar ID
$assinatura_id = Security::validateInt($_GET['id'] ?? 0, 1);

$assinatura = null;
if ($assinatura_id) {
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare("
            SELECT a.*, p.nome as plano_nome, p.tipo 
            FROM assinaturas a 
            JOIN planos p ON a.plano_id = p.id 
            WHERE a.id = ?
        ");
        $stmt->execute([$assinatura_id]);
        $assinatura = $stmt->fetch();
    } catch (Exception $e) {
        error_log("Erro ao buscar assinatura: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Obrigado - APAFUT</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Lato', sans-serif;
            background: linear-gradient(135deg, #111D69 0%, #1a2b8f 50%, #eb3835 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .success-container {
            background: white;
            border-radius: 20px;
            padding: 60px 40px;
            max-width: 600px;
            text-align: center;
            box-shadow: 0 25px 70px rgba(0, 0, 0, 0.4);
        }
        
        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #28a745, #20c997);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            animation: scaleIn 0.5s ease;
        }
        
        .success-icon i {
            font-size: 50px;
            color: white;
        }
        
        @keyframes scaleIn {
            from {
                transform: scale(0);
            }
            to {
                transform: scale(1);
            }
        }
        
        h1 {
            color: #111D69;
            font-size: 32px;
            margin-bottom: 15px;
            font-weight: 800;
        }
        
        .subtitle {
            color: #666;
            font-size: 18px;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .info-box {
            background: linear-gradient(to right, rgba(17, 29, 105, 0.05), rgba(235, 56, 53, 0.05));
            padding: 25px;
            border-radius: 12px;
            margin: 30px 0;
            border: 2px solid rgba(17, 29, 105, 0.1);
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 15px;
        }
        
        .info-label {
            color: #666;
            font-weight: 600;
        }
        
        .info-value {
            color: #333;
            font-weight: 700;
        }
        
        .btn {
            display: inline-block;
            padding: 16px 36px;
            background: linear-gradient(135deg, #eb3835, #d32f2c);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 700;
            font-size: 16px;
            margin-top: 20px;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(235, 56, 53, 0.45);
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-icon">
            <i class="fas fa-check"></i>
        </div>
        
        <h1>Assinatura Realizada!</h1>
        <p class="subtitle">
            Obrigado por se tornar um sócio da APAFUT. Em breve você receberá um e-mail com mais informações sobre sua assinatura.
        </p>
        
        <?php if ($assinatura): ?>
        <div class="info-box">
            <div class="info-row">
                <span class="info-label">Plano:</span>
                <span class="info-value"><?= htmlspecialchars($assinatura['plano_nome']) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Nome:</span>
                <span class="info-value"><?= htmlspecialchars($assinatura['nome']) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">E-mail:</span>
                <span class="info-value"><?= htmlspecialchars($assinatura['email']) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Valor:</span>
                <span class="info-value">R$ <?= number_format($assinatura['valor'], 2, ',', '.') ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Status:</span>
                <span class="info-value">Aguardando pagamento</span>
            </div>
        </div>
        <?php endif; ?>
        
        <a href="index.php" class="btn">
            <i class="fas fa-home"></i> Voltar ao Site
        </a>
    </div>
</body>
</html>
