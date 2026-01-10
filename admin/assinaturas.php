<?php
header('Content-Type: text/html; charset=UTF-8');
mb_internal_encoding('UTF-8');
require_once '../config/db.php';
require_once '../src/Cache.php';
require_once 'auth.php';

// Filtros
$filtroStatus = $_GET['status'] ?? '';
$filtroPlano = $_GET['plano'] ?? '';
$filtroBusca = $_GET['busca'] ?? '';

// Paginação
$paginaAtual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$itensPorPagina = 20;
$offset = ($paginaAtual - 1) * $itensPorPagina;

try {
    $pdo = getConnection();
    
    // Construir query com filtros
    $where = [];
    $params = [];
    
    if ($filtroStatus) {
        $where[] = "a.status = :status";
        $params[':status'] = $filtroStatus;
    }
    
    if ($filtroPlano) {
        $where[] = "a.plano_id = :plano_id";
        $params[':plano_id'] = $filtroPlano;
    }
    
    if ($filtroBusca) {
        $where[] = "(a.nome LIKE :busca OR a.cpf LIKE :busca OR a.email LIKE :busca)";
        $params[':busca'] = "%$filtroBusca%";
    }
    
    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    // Total de registros
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM assinaturas a $whereClause");
    $stmtCount->execute($params);
    $totalRegistros = $stmtCount->fetchColumn();
    $totalPaginas = ceil($totalRegistros / $itensPorPagina);
    
    // Buscar assinaturas
    $sql = "SELECT a.*, p.nome as plano_nome, p.tipo as plano_tipo, p.preco_anual 
            FROM assinaturas a 
            LEFT JOIN planos p ON a.plano_id = p.id 
            $whereClause
            ORDER BY a.created_at DESC 
            LIMIT :limit OFFSET :offset";
    
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $itensPorPagina, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $assinaturas = $stmt->fetchAll();
    
    // Buscar todos os planos para o filtro
    $stmtPlanos = $pdo->query("SELECT id, nome FROM planos ORDER BY nome");
    $planos = $stmtPlanos->fetchAll();
    
    // Estatísticas
    $stats = [
        'total' => $pdo->query("SELECT COUNT(*) FROM assinaturas")->fetchColumn(),

    ];
    
} catch (Exception $e) {
    error_log("Erro ao buscar assinaturas: " . $e->getMessage());
    $assinaturas = [];
    $planos = [];
    $stats = ['total' => 0, 'pendente' => 0, 'aprovado' => 0, 'cancelado' => 0];
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Assinaturas - Painel Administrativo</title>
    <link rel="icon" href="../assets/logo.ico" type="image/x-icon">
    <link rel="shortcut icon" href="../assets/logo.ico" type="image/x-icon">
    <link rel="apple-touch-icon" href="../assets/logo.png">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/noticias.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-card h3 {
            font-size: 2.5rem;
            margin: 10px 0;
            color: var(--azul-secundario);
        }
        
        .stat-card.aprovado h3 { color: #28a745; }
        .stat-card.cancelado h3 { color: #dc3545; }
        
        .filters {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: end;
        }
        
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        
        .filter-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        
        .filter-group select,
        .filter-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-badge.aprovado {
            background: #d4edda;
            color: #155724;
        }
        
        .status-badge.cancelado {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-badge.expirado {
            background: #e2e3e5;
            color: #383d41;
        }
        
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        
        .btn-action {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s;
        }
        
        .btn-cancelar {
            background: #dc3545;
            color: white;
        }
        
        .btn-cancelar:hover {
            background: #c82333;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
        }
        
        .pagination a,
        .pagination span {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            color: #333;
        }
        
        .pagination .active {
            background: var(--azul-secundario);
            color: white;
            border-color: var(--azul-secundario);
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <?php include 'includes/topbar.php'; ?>

            <div class="content">
                <div class="page-header-balanced">
                    <div class="header-left">
                        <div class="icon-wrapper">
                            <i class="fas fa-file-contract"></i>
                        </div>
                        <div class="header-text">
                            <h1>Gestão de Assinaturas</h1>
                            <p>Acompanhe e gerencie todas as assinaturas de sócios</p>
                        </div>
                    </div>
                </div>

                <!-- Estatísticas -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <i class="fas fa-users fa-2x" style="color: var(--azul-secundario);"></i>
                        <h3><?= $stats['total'] ?></h3>
                        <p>Total de Assinaturas</p>
                    </div>
                    <div class="stat-card aprovado">
                        <i class="fas fa-check-circle fa-2x"></i>
                        <h3><?= $stats['aprovado'] ?></h3>
                        <p>Aprovados</p>
                    </div>
                    <div class="stat-card cancelado">
                        <i class="fas fa-times-circle fa-2x"></i>
                        <h3><?= $stats['cancelado'] ?></h3>
                        <p>Cancelados</p>
                    </div>
                </div>

                <!-- Filtros -->
                <form class="filters" method="GET" action="assinaturas.php">
                    <div class="filter-group">
                        <label for="status">Status</label>
                        <select name="status" id="status">
                            <option value="">Todos</option>
                            <option value="aprovado" <?= $filtroStatus === 'aprovado' ? 'selected' : '' ?>>Aprovado</option>
                            <option value="cancelado" <?= $filtroStatus === 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                            <option value="expirado" <?= $filtroStatus === 'expirado' ? 'selected' : '' ?>>Expirado</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="plano">Plano</label>
                        <select name="plano" id="plano">
                            <option value="">Todos os Planos</option>
                            <?php foreach ($planos as $plano): ?>
                                <option value="<?= $plano['id'] ?>" <?= $filtroPlano == $plano['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($plano['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="busca">Buscar</label>
                        <input type="text" name="busca" id="busca" placeholder="Nome, CPF ou Email" value="<?= htmlspecialchars($filtroBusca) ?>">
                    </div>
                    
                    <div class="filter-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                    </div>
                </form>

                <!-- Tabela de Assinaturas -->
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>CPF</th>
                                <th>Email</th>
                                <th>Telefone</th>
                                <th>Plano</th>
                                <th>Status</th>
                                <th>Data</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($assinaturas)): ?>
                                <tr>
                                    <td colspan="9" style="text-align: center; padding: 40px;">
                                        <i class="fas fa-inbox fa-3x" style="color: #ccc;"></i>
                                        <p style="margin-top: 15px; color: #999;">Nenhuma assinatura encontrada</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($assinaturas as $assinatura): ?>
                                    <tr>
                                        <td>#<?= $assinatura['id'] ?></td>
                                        <td><?= htmlspecialchars($assinatura['nome']) ?></td>
                                        <td><?= htmlspecialchars($assinatura['cpf']) ?></td>
                                        <td><?= htmlspecialchars($assinatura['email']) ?></td>
                                        <td><?= htmlspecialchars($assinatura['telefone']) ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($assinatura['plano_nome']) ?></strong><br>
                                            <small><?= htmlspecialchars($assinatura['plano_tipo']) ?> - R$ <?= number_format($assinatura['preco_anual'], 2, ',', '.') ?>/ano</small>
                                        </td>
                                        <td>
                                            <span class="status-badge <?= $assinatura['status'] ?>">
                                                <?= $assinatura['status'] ?>
                                            </span>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($assinatura['created_at'])) ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <?php if ($assinatura['status'] !== 'cancelado'): ?>
                                                    <button class="btn-action btn-cancelar" onclick="updateStatus(<?= $assinatura['id'] ?>, 'cancelado')">
                                                        <i class="fas fa-times"></i> Cancelar
                                                    </button>
                                                <?php endif; ?>
                                                <a href="assinatura-detalhes.php?id=<?= $assinatura['id'] ?>" class="btn-action" style="background: #007bff;">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginação -->
                <?php if ($totalPaginas > 1): ?>
                    <div class="pagination">
                        <?php if ($paginaAtual > 1): ?>
                            <a href="?pagina=<?= $paginaAtual - 1 ?>&status=<?= $filtroStatus ?>&plano=<?= $filtroPlano ?>&busca=<?= $filtroBusca ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                            <?php if ($i == $paginaAtual): ?>
                                <span class="active"><?= $i ?></span>
                            <?php else: ?>
                                <a href="?pagina=<?= $i ?>&status=<?= $filtroStatus ?>&plano=<?= $filtroPlano ?>&busca=<?= $filtroBusca ?>">
                                    <?= $i ?>
                                </a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($paginaAtual < $totalPaginas): ?>
                            <a href="?pagina=<?= $paginaAtual + 1 ?>&status=<?= $filtroStatus ?>&plano=<?= $filtroPlano ?>&busca=<?= $filtroBusca ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        function updateStatus(id, novoStatus) {
            if (!confirm(`Confirma a alteração de status para "${novoStatus}"?`)) {
                return;
            }
            
            fetch('assinatura-update-status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id: id,
                    status: novoStatus
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Erro ao atualizar status: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao processar requisição');
            });
        }
    </script>
</body>
</html>
