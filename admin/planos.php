<?php
header('Content-Type: text/html; charset=UTF-8');
mb_internal_encoding('UTF-8');
require_once '../config/db.php';
require_once 'auth.php';

$pdo = getConnection();
$pdo->exec("SET NAMES utf8mb4");

// Mensagens de sucesso/erro
$mensagem = '';
$tipoMensagem = '';

if (isset($_GET['sucesso'])) {
    switch ($_GET['sucesso']) {
        case 'criado':
            $mensagem = 'Plano criado com sucesso!';
            $tipoMensagem = 'success';
            break;
        case 'excluido':
            $mensagem = 'Plano excluído com sucesso!';
            $tipoMensagem = 'success';
            break;
    }
} elseif (isset($_GET['erro'])) {
    switch ($_GET['erro']) {
        case 'nao_encontrado':
            $mensagem = 'Plano não encontrado.';
            $tipoMensagem = 'danger';
            break;
        case 'exclusao':
            $mensagem = 'Erro ao excluir plano.';
            $tipoMensagem = 'danger';
            break;
    }
}

// Filtros
$ordem = $_GET['ordem'] ?? 'ordem';
$direcao = $_GET['direcao'] ?? 'ASC';
$status = $_GET['status'] ?? 'todos';

// Construir query com filtros
$where = [];
$params = [];

if ($status === 'ativos') {
    $where[] = "ativo = 1";
} elseif ($status === 'inativos') {
    $where[] = "ativo = 0";
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
$orderClause = "ORDER BY $ordem $direcao";

// Buscar planos
$query = "SELECT * FROM planos $whereClause $orderClause";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$planos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planos - Painel Administrativo</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/noticias.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
    <style>
        .plano-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            border-left: 4px solid #3b82f6;
            transition: all 0.3s;
        }
        
        .plano-card.destaque {
            border-left-color: #f59e0b;
            background: linear-gradient(135deg, #fffbeb 0%, white 100%);
        }
        
        .plano-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        
        .plano-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
        }
        
        .plano-info h3 {
            font-size: 20px;
            font-weight: 700;
            color: #1e293b;
            margin: 0 0 8px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .plano-tipo {
            display: inline-flex;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .plano-tipo.prata {
            background: #e2e8f0;
            color: #475569;
        }
        
        .plano-tipo.ouro {
            background: #fef3c7;
            color: #92400e;
        }
        
        .plano-tipo.diamante {
            background: linear-gradient(135deg, #fce7f3 0%, #fbcfe8 100%);
            color: #9f1239;
            font-weight: 700;
        }
        
        .plano-preco {
            font-size: 28px;
            font-weight: 900;
            color: #10b981;
            margin: 0;
        }
        
        .plano-preco small {
            font-size: 14px;
            font-weight: 400;
            color: #64748b;
        }
        
        .plano-beneficios {
            margin: 16px 0;
        }
        
        .plano-beneficios ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 8px;
        }
        
        .plano-beneficios li {
            font-size: 14px;
            color: #475569;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .plano-beneficios li:before {
            content: "✓";
            color: #10b981;
            font-weight: 700;
            font-size: 16px;
        }
        
        .plano-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 16px;
            border-top: 1px solid #e2e8f0;
        }
        
        .plano-meta {
            display: flex;
            gap: 16px;
            font-size: 13px;
            color: #64748b;
        }
        
        .plano-actions {
            display: flex;
            gap: 8px;
        }
        
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .badge.success {
            background: #d1fae5;
            color: #065f46;
        }
        
        .badge.danger {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .badge.warning {
            background: #fef3c7;
            color: #92400e;
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <?php include 'includes/topbar.php'; ?>

            <div class="content">
                <?php if ($mensagem): ?>
                    <div class="alert alert-<?= $tipoMensagem ?>">
                        <i class="fas fa-<?= $tipoMensagem === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                        <?= htmlspecialchars($mensagem) ?>
                    </div>
                <?php endif; ?>

                <div class="page-header-balanced">
                    <div class="header-left">
                        <div class="icon-wrapper">
                            <i class="fas fa-crown"></i>
                        </div>
                        <div class="header-text">
                            <h1>Planos</h1>
                            <p>Gerencie os planos de sócio da Apafut</p>
                        </div>
                    </div>
                    <a href="plano-criar.php" class="btn-balanced">
                        <i class="fas fa-plus"></i> Novo Plano
                    </a>
                </div>

                <div class="filters-bar">
                    <div class="filters-group">
                        <label>Status:</label>
                        <select id="statusFilter" onchange="applyFilters()">
                            <option value="todos" <?= $status === 'todos' ? 'selected' : '' ?>>Todos</option>
                            <option value="ativos" <?= $status === 'ativos' ? 'selected' : '' ?>>Ativos</option>
                            <option value="inativos" <?= $status === 'inativos' ? 'selected' : '' ?>>Inativos</option>
                        </select>
                    </div>
                    
                    <div class="filters-group">
                        <label>Ordenar por:</label>
                        <select id="ordemFilter" onchange="applyFilters()">
                            <option value="ordem" <?= $ordem === 'ordem' ? 'selected' : '' ?>>Ordem</option>
                            <option value="nome" <?= $ordem === 'nome' ? 'selected' : '' ?>>Nome</option>
                            <option value="preco_anual" <?= $ordem === 'preco_anual' ? 'selected' : '' ?>>Preço</option>
                            <option value="created_at" <?= $ordem === 'created_at' ? 'selected' : '' ?>>Data de Criação</option>
                        </select>
                    </div>
                    
                    <div class="filters-group">
                        <label>Direção:</label>
                        <select id="direcaoFilter" onchange="applyFilters()">
                            <option value="ASC" <?= $direcao === 'ASC' ? 'selected' : '' ?>>Crescente</option>
                            <option value="DESC" <?= $direcao === 'DESC' ? 'selected' : '' ?>>Decrescente</option>
                        </select>
                    </div>
                </div>

                <div class="stats-mini">
                    <div class="stat-mini">
                        <span class="stat-mini-value"><?= count($planos) ?></span>
                        <span class="stat-mini-label">Total de Planos</span>
                    </div>
                    <div class="stat-mini">
                        <span class="stat-mini-value"><?= count(array_filter($planos, fn($p) => $p['ativo'])) ?></span>
                        <span class="stat-mini-label">Planos Ativos</span>
                    </div>
                    <div class="stat-mini">
                        <span class="stat-mini-value"><?= count(array_filter($planos, fn($p) => $p['destaque'])) ?></span>
                        <span class="stat-mini-label">Em Destaque</span>
                    </div>
                </div>

                <div class="planos-list">
                    <?php if (empty($planos)): ?>
                        <div class="empty-state">
                            <i class="fas fa-tags"></i>
                            <h3>Nenhum plano encontrado</h3>
                            <p>Comece criando seu primeiro plano de sócio</p>
                            <a href="plano-criar.php" class="btn btn-primary">
                                <i class="fas fa-plus"></i>
                                Criar Primeiro Plano
                            </a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($planos as $plano): ?>
                            <div class="plano-card <?= $plano['destaque'] ? 'destaque' : '' ?>">
                                <div class="plano-header">
                                    <div class="plano-info">
                                        <h3>
                                            <?= htmlspecialchars($plano['nome']) ?>
                                            <span class="plano-tipo <?= strtolower($plano['tipo']) ?>">
                                                <?= htmlspecialchars($plano['tipo']) ?>
                                            </span>
                                        </h3>
                                        <p class="plano-preco">
                                            R$ <?= number_format($plano['preco_anual'], 2, ',', '.') ?>
                                            <small>/ano em <?= $plano['parcelas'] ?>x</small>
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="plano-beneficios">
                                    <ul>
                                        <?php 
                                        $beneficios = explode('|', $plano['beneficios']);
                                        foreach ($beneficios as $beneficio): 
                                        ?>
                                            <li><?= htmlspecialchars(trim($beneficio)) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                
                                <div class="plano-footer">
                                    <div class="plano-meta">
                                        <span>
                                            <i class="fas fa-sort"></i>
                                            Ordem: <?= $plano['ordem'] ?>
                                        </span>
                                        <span class="badge <?= $plano['ativo'] ? 'success' : 'danger' ?>">
                                            <?= $plano['ativo'] ? 'Ativo' : 'Inativo' ?>
                                        </span>
                                        <?php if ($plano['destaque']): ?>
                                            <span class="badge warning">
                                                <i class="fas fa-star"></i> Destaque
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="plano-actions">
                                        <a href="plano-editar.php?id=<?= $plano['id'] ?>" class="btn-balanced-light btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button onclick="confirmarExclusao(<?= $plano['id'] ?>, '<?= htmlspecialchars($plano['nome']) ?>')" 
                                                class="btn-balanced-light btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal de Exclusão -->
    <div id="modalExcluir" class="modal">
        <div class="modal-content">
            <h3>Confirmar Exclusão</h3>
            <p>Tem certeza que deseja excluir o plano <strong id="planoNome"></strong>?</p>
            <p class="warning-text">Esta ação não pode ser desfeita.</p>
            <div class="modal-actions">
                <button onclick="fecharModal()" class="btn-secondary">Cancelar</button>
                <button onclick="excluirPlano()" class="btn-danger">Excluir</button>
            </div>
        </div>
    </div>

    <script>
        let planoIdExcluir = null;

        function applyFilters() {
            const status = document.getElementById('statusFilter').value;
            const ordem = document.getElementById('ordemFilter').value;
            const direcao = document.getElementById('direcaoFilter').value;
            
            window.location.href = `planos.php?status=${status}&ordem=${ordem}&direcao=${direcao}`;
        }

        function confirmarExclusao(id, nome) {
            planoIdExcluir = id;
            document.getElementById('planoNome').textContent = nome;
            document.getElementById('modalExcluir').style.display = 'flex';
        }

        function fecharModal() {
            document.getElementById('modalExcluir').style.display = 'none';
            planoIdExcluir = null;
        }

        function excluirPlano() {
            if (planoIdExcluir) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'plano-excluir.php';
                
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'id';
                input.value = planoIdExcluir;
                
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Fechar modal ao clicar fora
        window.onclick = function(event) {
            const modal = document.getElementById('modalExcluir');
            if (event.target === modal) {
                fecharModal();
            }
        }
    </script>
</body>
</html>
