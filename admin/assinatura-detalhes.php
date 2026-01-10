<?php
require_once 'auth.php';
require_once '../config/db.php';

Auth::require();

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: assinaturas.php');
    exit;
}

$pdo = getConnection();
$stmt = $pdo->prepare("
    SELECT a.*, p.nome as plano_nome, p.tipo as plano_tipo, 
           p.preco_anual, p.preco_mensal, p.preco_diario
    FROM assinaturas a
    LEFT JOIN planos p ON a.plano_id = p.id
    WHERE a.id = :id
");
$stmt->execute([':id' => $id]);
$assinatura = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$assinatura) {
    header('Location: assinaturas.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes da Assinatura - Admin APAFUT</title>
    <link rel="icon" href="../assets/logo.ico" type="image/x-icon">
    <link rel="shortcut icon" href="../assets/logo.ico" type="image/x-icon">
    <link rel="apple-touch-icon" href="../assets/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <style>
        .detail-card {
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .detail-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .detail-title {
            font-size: 24px;
            color: #333;
            font-weight: 600;
        }
        
        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .detail-item {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 6px;
            border-left: 4px solid var(--azul-primario);
        }
        
        .detail-label {
            font-size: 12px;
            text-transform: uppercase;
            color: #666;
            font-weight: 600;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }
        
        .detail-value {
            font-size: 16px;
            color: #333;
            font-weight: 500;
        }
        
        .status-badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }
        
        .status-pendente { background: #fff3cd; color: #856404; }
        .status-aprovado { background: #d4edda; color: #155724; }
        .status-cancelado { background: #f8d7da; color: #721c24; }
        .status-expirado { background: #e2e3e5; color: #383d41; }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: var(--azul-primario);
            color: white;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .history-section {
            margin-top: 30px;
        }
        
        .history-title {
            font-size: 18px;
            color: #333;
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .history-item {
            padding: 15px;
            background: #f8f9fa;
            border-left: 3px solid var(--azul-primario);
            margin-bottom: 10px;
            border-radius: 4px;
        }
        
        .history-date {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .history-text {
            font-size: 14px;
            color: #333;
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/topbar.php'; ?>
        
        <div class="content-wrapper">
            <div class="detail-card">
                <div class="detail-header">
                    <h1 class="detail-title">
                        <i class="fas fa-file-alt"></i> Detalhes da Assinatura #<?= $assinatura['id'] ?>
                    </h1>
                    <span class="status-badge status-<?= $assinatura['status'] ?>">
                        <?= ucfirst($assinatura['status']) ?>
                    </span>
                </div>
                
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Nome Completo</div>
                        <div class="detail-value"><?= htmlspecialchars($assinatura['nome']) ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">CPF</div>
                        <div class="detail-value"><?= htmlspecialchars($assinatura['cpf']) ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Email</div>
                        <div class="detail-value"><?= htmlspecialchars($assinatura['email']) ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Telefone</div>
                        <div class="detail-value"><?= htmlspecialchars($assinatura['telefone']) ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Data de Nascimento</div>
                        <div class="detail-value"><?= date('d/m/Y', strtotime($assinatura['data_nascimento'])) ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Tipo de Plano</div>
                        <div class="detail-value"><?= htmlspecialchars($assinatura['plano_nome'] ?? 'N/A') ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Categoria</div>
                        <div class="detail-value"><?= ucfirst($assinatura['plano_tipo'] ?? 'N/A') ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Valor</div>
                        <div class="detail-value">
                            <?php
                            $preco = $assinatura['preco_anual'] ?? $assinatura['preco_mensal'] ?? $assinatura['preco_diario'] ?? 0;
                            echo 'R$ ' . number_format($preco, 2, ',', '.');
                            ?>
                        </div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Data de Assinatura</div>
                        <div class="detail-value"><?= date('d/m/Y H:i', strtotime($assinatura['created_at'])) ?></div>
                    </div>
                </div>
                
                <?php if (!empty($assinatura['endereco'])): ?>
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Endereço</div>
                        <div class="detail-value"><?= htmlspecialchars($assinatura['endereco']) ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Cidade/Estado</div>
                        <div class="detail-value">
                            <?= htmlspecialchars($assinatura['cidade'] ?? '') ?> - <?= htmlspecialchars($assinatura['estado'] ?? '') ?>
                        </div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">CEP</div>
                        <div class="detail-value"><?= htmlspecialchars($assinatura['cep'] ?? '') ?></div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="action-buttons">
                    <a href="assinaturas.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                    
                    <button onclick="updateStatus(<?= $id ?>, 'cancelado')" class="btn btn-danger">
                        <i class="fas fa-ban"></i> Cancelar Assinatura
                    </button>
                    
                    <?php if ($assinatura['status'] === 'aprovado'): ?>
                    <button onclick="updateStatus(<?= $id ?>, 'expirado')" class="btn btn-secondary">
                        <i class="fas fa-clock"></i> Marcar como Expirado
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        async function updateStatus(id, status) {
            const confirmMsg = {
                'aprovado': 'Tem certeza que deseja aprovar esta assinatura?',
                'cancelado': 'Tem certeza que deseja cancelar esta assinatura?',
                'expirado': 'Tem certeza que deseja marcar como expirado?'
            };
            
            if (!confirm(confirmMsg[status])) {
                return;
            }
            
            try {
                const response = await fetch('assinatura-update-status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id, status })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('Status atualizado com sucesso!');
                    window.location.reload();
                } else {
                    alert('Erro ao atualizar status: ' + (data.error || 'Erro desconhecido'));
                }
            } catch (error) {
                alert('Erro ao atualizar status: ' + error.message);
            }
        }
    </script>
</body>
</html>
