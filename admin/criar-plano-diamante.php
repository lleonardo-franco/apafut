<?php
header('Content-Type: text/html; charset=UTF-8');
mb_internal_encoding('UTF-8');
require_once '../config/db.php';
require_once '../src/Cache.php';
require_once 'auth.php';

$resultado = '';

try {
    $pdo = getConnection();
    
    // Verificar se já existe plano Diamante
    $check = $pdo->prepare("SELECT COUNT(*) FROM planos WHERE tipo = 'Diamante'");
    $check->execute();
    $existe = $check->fetchColumn();
    
    if ($existe > 0) {
        $resultado = '<div class="alert alert-warning">Plano Diamante já existe no banco de dados!</div>';
    } else {
        // Inserir plano Diamante
        $sql = "INSERT INTO planos (nome, tipo, preco_anual, parcelas, beneficios, ordem, ativo, destaque) 
                VALUES (:nome, :tipo, :preco_anual, :parcelas, :beneficios, :ordem, :ativo, :destaque)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nome' => 'Sócio APA Diamante',
            ':tipo' => 'Diamante',
            ':preco_anual' => 720.00,
            ':parcelas' => 12,
            ':beneficios' => 'Kit Diamante|Camiseta Oficial Temporada 2026|Jantar Sócios|Acesso aos treinos|Desconto em produtos oficiais',
            ':ordem' => 2,
            ':ativo' => 1,
            ':destaque' => 1
        ]);
        
        // Limpar cache
        Cache::delete('planos_ativos');
        
        $resultado = '<div class="alert alert-success">✅ Plano Diamante criado com sucesso!</div>';
    }
    
} catch (Exception $e) {
    $resultado = '<div class="alert alert-danger">❌ Erro: ' . $e->getMessage() . '</div>';
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Plano Diamante</title>
    <link rel="icon" type="image/x-icon" href="/apafut/assets/logo.ico">
    <link rel="shortcut icon" type="image/x-icon" href="/apafut/assets/logo.ico">
    <link rel="apple-touch-icon" href="/apafut/assets/logo.png">
    <link rel="icon" href="../assets/logo.ico" type="image/x-icon">
    <link rel="shortcut icon" href="../assets/logo.ico" type="image/x-icon">
    
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/alerts.css">
    <style>
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        h1 { color: #111D69; margin-bottom: 20px; }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            margin: 10px 5px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-primary {
            background: #eb3835;
            color: white;
        }
        .btn-primary:hover {
            background: #d32f2f;
            transform: translateY(-2px);
        }
        .btn-secondary {
            background: #6b7280;
            color: white;
        }
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin: 20px 0;
            font-size: 16px;
        }
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }
        .alert-warning {
            background: #fef3c7;
            color: #92400e;
            border-left: 4px solid #f59e0b;
        }
        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }
        .plano-info {
            background: #f8fafc;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .plano-info h3 {
            color: #111D69;
            margin-bottom: 15px;
        }
        .plano-info p {
            margin: 8px 0;
            color: #475569;
        }
        .plano-info strong {
            color: #1e293b;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>💎 Criar Plano Diamante</h1>
        
        <?= $resultado ?>
        
        <div class="plano-info">
            <h3>Detalhes do Plano Diamante:</h3>
            <p><strong>Nome:</strong> Sócio APA Diamante</p>
            <p><strong>Tipo:</strong> Diamante</p>
            <p><strong>Preço Anual:</strong> R$ 720,00</p>
            <p><strong>Parcelas:</strong> 12x de R$ 60,00</p>
            <p><strong>Ordem:</strong> 2 (terceiro plano)</p>
            <p><strong>Status:</strong> Ativo ✓</p>
            <p><strong>Destaque:</strong> Sim ✓</p>
            <p><strong>Benefícios:</strong></p>
            <ul style="margin-left: 20px; color: #475569;">
                <li>Kit Diamante</li>
                <li>Camiseta Oficial Temporada 2026</li>
                <li>Jantar Sócios</li>
                <li>Acesso aos treinos</li>
                <li>Desconto em produtos oficiais</li>
            </ul>
        </div>
        
        <div style="margin-top: 30px;">
            <a href="planos.php" class="btn btn-primary">Ver Todos os Planos</a>
            <a href="dashboard.php" class="btn btn-secondary">Voltar ao Dashboard</a>
        </div>
    </div>
</body>
</html>
