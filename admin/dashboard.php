<?php
require_once 'auth.php';

// Requer autenticação
Auth::require();

// Obter dados do usuário
$user = Auth::user();

// Buscar estatísticas
try {
    $conn = getConnection();
    
    // Total de notícias
    $stmt = $conn->query("SELECT COUNT(*) as total FROM noticias WHERE ativo = 1");
    $totalNoticias = $stmt->fetch()['total'];
    
    // Total de notícias em destaque
    $stmt = $conn->query("SELECT COUNT(*) as total FROM noticias WHERE ativo = 1 AND destaque = 1");
    $noticiasDestaque = $stmt->fetch()['total'];
    
    // Total de jogadores
    $stmt = $conn->query("SELECT COUNT(*) as total FROM jogadores WHERE ativo = 1");
    $totalJogadores = $stmt->fetch()['total'];
    
    // Total de depoimentos
    $stmt = $conn->query("SELECT COUNT(*) as total FROM depoimentos WHERE ativo = 1");
    $totalDepoimentos = $stmt->fetch()['total'];
    
    // Total de planos
    $stmt = $conn->query("SELECT COUNT(*) as total FROM planos WHERE ativo = 1");
    $totalPlanos = $stmt->fetch()['total'];
    
    // Últimas notícias
    $stmt = $conn->query("SELECT id, titulo, categoria, created_at FROM noticias WHERE ativo = 1 ORDER BY created_at DESC LIMIT 5");
    $ultimasNoticias = $stmt->fetchAll();
    
    // Últimos jogadores
    $stmt = $conn->query("SELECT id, nome, posicao, created_at FROM jogadores WHERE ativo = 1 ORDER BY created_at DESC LIMIT 5");
    $ultimosJogadores = $stmt->fetchAll();
    
    // Últimos depoimentos
    $stmt = $conn->query("SELECT id, nome, created_at FROM depoimentos WHERE ativo = 1 ORDER BY created_at DESC LIMIT 5");
    $ultimosDepoimentos = $stmt->fetchAll();
    
    // Estatísticas adicionais
    $stmt = $conn->query("SELECT COUNT(*) as total FROM noticias");
    $totalTodasNoticias = $stmt->fetch()['total'];
    
    $stmt = $conn->query("SELECT COUNT(*) as total FROM jogadores");
    $totalTodosJogadores = $stmt->fetch()['total'];
    
} catch (Exception $e) {
    logError('Erro ao buscar estatísticas do dashboard', ['error' => $e->getMessage()]);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Painel Administrativo</title>
    <!-- Lato Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
    <!-- FontAwesome -->
    <script src="https://kit.fontawesome.com/15d6bd6a1c.js" crossorigin="anonymous"></script>
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/noticias.css">
    <style>
        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
            margin-top: 24px;
        }
        
        .recent-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .recent-card h3 {
            font-size: 18px;
            font-weight: 700;
            color: #1e293b;
            margin: 0 0 20px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .recent-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .recent-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px;
            background: #f8fafc;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .recent-item:hover {
            background: #f1f5f9;
            transform: translateX(4px);
        }
        
        .recent-item-info {
            flex: 1;
        }
        
        .recent-item-title {
            font-size: 14px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 4px;
        }
        
        .recent-item-meta {
            font-size: 12px;
            color: #64748b;
        }
        
        .recent-item-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .badge-campeonatos { background: #dbeafe; color: #1e40af; }
        .badge-infraestrutura { background: #fef3c7; color: #92400e; }
        .badge-eventos { background: #fee2e2; color: #991b1b; }
        .badge-outros { background: #e2e8f0; color: #475569; }
        
        .quick-actions {
            display: grid;
            gap: 12px;
        }
        
        .quick-action-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px;
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            text-decoration: none;
            color: #1e293b;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .quick-action-btn:hover {
            border-color: var(--vermelho-primario);
            background: #fff5f5;
            transform: translateY(-2px);
        }
        
        .quick-action-btn i {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--vermelho-primario), #d32f2f);
            color: white;
            border-radius: 8px;
            font-size: 18px;
        }
        
        .metrics-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-top: 24px;
        }
        
        .metric-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            border-left: 4px solid var(--vermelho-primario);
        }
        
        .metric-card h4 {
            font-size: 13px;
            font-weight: 600;
            color: #64748b;
            margin: 0 0 8px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .metric-card .value {
            font-size: 32px;
            font-weight: 900;
            color: #1e293b;
            line-height: 1;
        }
        
        .metric-card .label {
            font-size: 12px;
            color: #64748b;
            margin-top: 4px;
        }
        
        @media (max-width: 1200px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <i class="fas fa-futbol"></i>
                    <span>Apafut Admin</span>
                </div>
            </div>
            
            <nav class="sidebar-menu">
                <div class="menu-section-title">Principal</div>
                <a href="dashboard.php" class="menu-item active">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                
                <div class="menu-section-title">Conteúdo</div>
                <a href="noticias.php" class="menu-item">
                    <i class="fas fa-newspaper"></i>
                    <span>Notícias</span>
                </a>
                <a href="banners.php" class="menu-item">
                    <i class="fas fa-images"></i>
                    <span>Banners</span>
                </a>
                <a href="jogadores.php" class="menu-item">
                    <i class="fas fa-users"></i>
                    <span>Jogadores</span>
                </a>
                <a href="depoimentos.php" class="menu-item">
                    <i class="fas fa-comments"></i>
                    <span>Depoimentos</span>
                </a>
                <a href="planos.php" class="menu-item">
                    <i class="fas fa-tags"></i>
                    <span>Planos</span>
                </a>
                
                <div class="menu-section-title">Sistema</div>
                <a href="analytics.php" class="menu-item">
                    <i class="fas fa-chart-line"></i>
                    <span>Analytics</span>
                </a>
                <a href="configuracoes.php" class="menu-item">
                    <i class="fas fa-cog"></i>
                    <span>Configurações</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Bar -->
            <header class="topbar">
                <div class="topbar-left">
                    <h1>Dashboard</h1>
                </div>
                <div class="topbar-right">
                    <div class="user-info">
                        <div class="user-avatar">
                            <?= Auth::getInitials($user['nome']) ?>
                        </div>
                        <div class="user-details">
                            <span class="user-name"><?= htmlspecialchars($user['nome']) ?></span>
                            <span class="user-role"><?= ucfirst($user['nivel_acesso']) ?></span>
                        </div>
                    </div>
                    <a href="logout.php" class="btn-logout">
                        <i class="fas fa-sign-out-alt"></i>
                        Sair
                    </a>
                </div>
            </header>

            <!-- Content -->
            <div class="content">
                <!-- Welcome Card -->
                <div class="welcome-card">
                    <h2>Bem-vindo de volta, <?= htmlspecialchars(explode(' ', $user['nome'])[0]) ?>! 👋</h2>
                    <p>Gerencie o conteúdo do site Apafut de forma fácil e rápida.</p>
                </div>

                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-info">
                            <h3><?= $totalNoticias ?? 0 ?></h3>
                            <p>Notícias Publicadas</p>
                        </div>
                        <div class="stat-icon primary">
                            <i class="fas fa-newspaper"></i>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-info">
                            <h3><?= $totalJogadores ?? 0 ?></h3>
                            <p>Jogadores Ativos</p>
                        </div>
                        <div class="stat-icon success">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-info">
                            <h3><?= $totalDepoimentos ?? 0 ?></h3>
                            <p>Depoimentos</p>
                        </div>
                        <div class="stat-icon warning">
                            <i class="fas fa-comments"></i>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-info">
                            <h3><?= $totalPlanos ?? 0 ?></h3>
                            <p>Planos Disponíveis</p>
                        </div>
                        <div class="stat-icon danger">
                            <i class="fas fa-tags"></i>
                        </div>
                    </div>
                </div>

                <!-- Métricas Adicionais -->
                <div class="metrics-row">
                    <div class="metric-card">
                        <h4>Taxa de Publicação</h4>
                        <div class="value">
                            <?php 
                            $taxa = $totalTodasNoticias > 0 ? round(($totalNoticias / $totalTodasNoticias) * 100) : 0;
                            echo $taxa . '%';
                            ?>
                        </div>
                        <div class="label">Notícias ativas</div>
                    </div>
                    
                    <div class="metric-card">
                        <h4>Destaques</h4>
                        <div class="value"><?= $noticiasDestaque ?? 0 ?></div>
                        <div class="label">Notícias em destaque</div>
                    </div>
                    
                    <div class="metric-card">
                        <h4>Conteúdo Total</h4>
                        <div class="value">
                            <?= ($totalTodasNoticias ?? 0) + ($totalTodosJogadores ?? 0) + ($totalDepoimentos ?? 0) ?>
                        </div>
                        <div class="label">Itens no sistema</div>
                    </div>
                </div>

                <!-- Grid Principal -->
                <div class="dashboard-grid">
                    <!-- Atividades Recentes -->
                    <div>
                        <div class="recent-card">
                            <h3><i class="fas fa-newspaper"></i> Últimas Notícias</h3>
                            <div class="recent-list">
                                <?php if (!empty($ultimasNoticias)): ?>
                                    <?php foreach ($ultimasNoticias as $noticia): ?>
                                        <div class="recent-item">
                                            <div class="recent-item-info">
                                                <div class="recent-item-title">
                                                    <?= htmlspecialchars($noticia['titulo']) ?>
                                                </div>
                                                <div class="recent-item-meta">
                                                    <i class="fas fa-clock"></i>
                                                    <?= date('d/m/Y H:i', strtotime($noticia['created_at'])) ?>
                                                </div>
                                            </div>
                                            <span class="recent-item-badge badge-<?= strtolower(str_replace(' ', '-', $noticia['categoria'])) ?>">
                                                <?= htmlspecialchars($noticia['categoria']) ?>
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p style="color: #64748b; text-align: center; padding: 20px;">
                                        Nenhuma notícia cadastrada ainda
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="recent-card" style="margin-top: 24px;">
                            <h3><i class="fas fa-users"></i> Últimos Jogadores</h3>
                            <div class="recent-list">
                                <?php if (!empty($ultimosJogadores)): ?>
                                    <?php foreach ($ultimosJogadores as $jogador): ?>
                                        <div class="recent-item">
                                            <div class="recent-item-info">
                                                <div class="recent-item-title">
                                                    <?= htmlspecialchars($jogador['nome']) ?>
                                                </div>
                                                <div class="recent-item-meta">
                                                    <i class="fas fa-futbol"></i>
                                                    <?= htmlspecialchars($jogador['posicao']) ?>
                                                </div>
                                            </div>
                                            <div class="recent-item-meta">
                                                <?= date('d/m/Y', strtotime($jogador['created_at'])) ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p style="color: #64748b; text-align: center; padding: 20px;">
                                        Nenhum jogador cadastrado ainda
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Ações Rápidas -->
                    <div>
                        <div class="recent-card">
                            <h3><i class="fas fa-bolt"></i> Ações Rápidas</h3>
                            <div class="quick-actions">
                                <a href="noticia-criar.php" class="quick-action-btn">
                                    <i class="fas fa-plus"></i>
                                    <span>Nova Notícia</span>
                                </a>
                                <a href="jogador-criar.php" class="quick-action-btn">
                                    <i class="fas fa-user-plus"></i>
                                    <span>Novo Jogador</span>
                                </a>
                                <a href="depoimento-criar.php" class="quick-action-btn">
                                    <i class="fas fa-comment-medical"></i>
                                    <span>Novo Depoimento</span>
                                </a>
                                <a href="plano-criar.php" class="quick-action-btn">
                                    <i class="fas fa-tag"></i>
                                    <span>Novo Plano</span>
                                </a>
                            </div>
                        </div>

                        <div class="recent-card" style="margin-top: 24px;">
                            <h3><i class="fas fa-comments"></i> Últimos Depoimentos</h3>
                            <div class="recent-list">
                                <?php if (!empty($ultimosDepoimentos)): ?>
                                    <?php foreach ($ultimosDepoimentos as $depoimento): ?>
                                        <div class="recent-item">
                                            <div class="recent-item-info">
                                                <div class="recent-item-title">
                                                    <?= htmlspecialchars($depoimento['nome']) ?>
                                                </div>
                                                <div class="recent-item-meta">
                                                    <i class="fas fa-clock"></i>
                                                    <?= date('d/m/Y', strtotime($depoimento['created_at'])) ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p style="color: #64748b; text-align: center; padding: 20px;">
                                        Nenhum depoimento cadastrado ainda
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
