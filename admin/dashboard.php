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
    <link rel="icon" type="image/x-icon" href="/apafut/assets/logo.ico">
    <link rel="shortcut icon" type="image/x-icon" href="/apafut/assets/logo.ico">
    <link rel="apple-touch-icon" href="/apafut/assets/logo.png">
    <link rel="icon" href="../assets/logo.ico" type="image/x-icon">
    <link rel="shortcut icon" href="../assets/logo.ico" type="image/x-icon">
    
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/noticias.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
    <style>
        .content-area {
            padding: 40px;
        }
        
        .dashboard-welcome {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            padding: 40px;
            color: white;
            margin-bottom: 40px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }
        
        .dashboard-welcome h1 {
            font-size: 32px;
            font-weight: 900;
            margin: 0 0 8px 0;
        }
        
        .dashboard-welcome p {
            font-size: 16px;
            opacity: 0.95;
            margin: 0;
        }
        
        .stats-grid-modern {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }
        
        .stat-card-modern {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card-modern::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--color), transparent);
        }
        
        .stat-card-modern:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }
        
        .stat-card-modern.primary { --color: #3b82f6; }
        .stat-card-modern.success { --color: #10b981; }
        .stat-card-modern.warning { --color: #f59e0b; }
        .stat-card-modern.danger { --color: #ef4444; }
        .stat-card-modern.purple { --color: #8b5cf6; }
        .stat-card-modern.teal { --color: #14b8a6; }
        
        .stat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }
        
        .stat-icon-modern {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
        }
        
        .stat-card-modern.primary .stat-icon-modern { background: linear-gradient(135deg, #3b82f6, #2563eb); }
        .stat-card-modern.success .stat-icon-modern { background: linear-gradient(135deg, #10b981, #059669); }
        .stat-card-modern.warning .stat-icon-modern { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .stat-card-modern.danger .stat-icon-modern { background: linear-gradient(135deg, #ef4444, #dc2626); }
        .stat-card-modern.purple .stat-icon-modern { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
        .stat-card-modern.teal .stat-icon-modern { background: linear-gradient(135deg, #14b8a6, #0d9488); }
        
        .stat-value {
            font-size: 36px;
            font-weight: 900;
            color: #1e293b;
            line-height: 1;
            margin-bottom: 8px;
        }
        
        .stat-label {
            font-size: 14px;
            color: #64748b;
            font-weight: 600;
        }
        
        .stat-trend {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 8px;
            padding: 4px 8px;
            border-radius: 6px;
        }
        
        .stat-trend.up {
            background: #d1fae5;
            color: #065f46;
        }
        
        .stat-trend.neutral {
            background: #e2e8f0;
            color: #475569;
        }
        
        .dashboard-main-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
            margin-bottom: 32px;
        }
        
        .card-modern {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        }
        
        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 2px solid #f1f5f9;
        }
        
        .card-title {
            font-size: 18px;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .card-title i {
            color: #EB3835;
        }
        
        .view-all-link {
            font-size: 13px;
            color: #EB3835;
            text-decoration: none !important;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .view-all-link:hover {
            color: #d32f2f;
            transform: translateX(2px);
        }
        
        .activity-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .activity-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 12px;
            background: #f8fafc;
            border-radius: 10px;
            transition: all 0.3s;
            cursor: pointer;
            text-decoration: none !important;
        }
        
        .activity-item:hover {
            background: #f1f5f9;
            transform: translateX(4px);
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }
        
        .activity-icon.news { background: #dbeafe; color: #1e40af; }
        .activity-icon.player { background: #d1fae5; color: #065f46; }
        .activity-icon.testimonial { background: #fef3c7; color: #92400e; }
        
        .activity-content {
            flex: 1;
            min-width: 0;
        }
        
        .activity-title {
            font-size: 14px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 4px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .activity-meta {
            font-size: 12px;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .category-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .category-badge.campeonatos { background: #dbeafe; color: #1e40af; }
        .category-badge.infraestrutura { background: #fef3c7; color: #92400e; }
        .category-badge.eventos { background: #fee2e2; color: #991b1b; }
        .category-badge.depoimentos { background: #fce7f3; color: #9f1239; }
        .category-badge.outros { background: #e2e8f0; color: #475569; }
        
        .quick-actions-grid {
            display: grid;
            gap: 12px;
        }
        
        .quick-action {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px;
            background: linear-gradient(135deg, #f8fafc 0%, white 100%);
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            text-decoration: none !important;
            color: #1e293b;
            transition: all 0.3s;
            font-weight: 600;
        }
        
        .quick-action:hover {
            border-color: #EB3835;
            background: linear-gradient(135deg, #fff5f5 0%, white 100%);
            transform: translateX(4px);
        }
        
        .quick-action-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: linear-gradient(135deg, #EB3835, #d32f2f);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #64748b;
        }
        
        .empty-state i {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.3;
        }
        
        .empty-state p {
            margin: 0;
            font-size: 14px;
        }
        
        @media (max-width: 1200px) {
            .dashboard-main-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .stats-grid-modern {
                grid-template-columns: 1fr;
            }
            
            .content-area {
                padding: 20px;
            }
            
            .dashboard-welcome {
                padding: 24px;
            }
            
            .dashboard-welcome h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/topbar.php'; ?>
        
        <div class="content-area">
            <!-- Dashboard Welcome -->
            <div class="dashboard-welcome">
                <h1>👋 Bem-vindo <?= htmlspecialchars($usuario['nome']) ?>!</h1>
                <p>Aqui está um resumo das atividades e estatísticas do seu painel administrativo.</p>
            </div>
            
            <!-- Statistics Grid -->
            <div class="stats-grid-modern">
                <div class="stat-card-modern primary">
                    <div class="stat-header">
                        <div class="stat-icon-modern">
                            <i class="fa-solid fa-newspaper"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?= $totalNoticias ?></div>
                    <div class="stat-label">Notícias Publicadas</div>
                    <div class="stat-trend up">
                        <i class="fa-solid fa-arrow-up"></i>
                        <span>Conteúdo ativo</span>
                    </div>
                </div>
                
                <div class="stat-card-modern success">
                    <div class="stat-header">
                        <div class="stat-icon-modern">
                            <i class="fa-solid fa-users"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?= $totalJogadores ?></div>
                    <div class="stat-label">Jogadores Cadastrados</div>
                    <div class="stat-trend neutral">
                        <i class="fa-solid fa-minus"></i>
                        <span>Elenco completo</span>
                    </div>
                </div>
                
                <div class="stat-card-modern warning">
                    <div class="stat-header">
                        <div class="stat-icon-modern">
                            <i class="fa-solid fa-comments"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?= $totalDepoimentos ?></div>
                    <div class="stat-label">Depoimentos</div>
                    <div class="stat-trend up">
                        <i class="fa-solid fa-arrow-up"></i>
                        <span>Engajamento</span>
                    </div>
                </div>
                
                <div class="stat-card-modern danger">
                    <div class="stat-header">
                        <div class="stat-icon-modern">
                            <i class="fa-solid fa-tag"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?= $totalPlanos ?></div>
                    <div class="stat-label">Planos Disponíveis</div>
                    <div class="stat-trend neutral">
                        <i class="fa-solid fa-minus"></i>
                        <span>Ativos</span>
                    </div>
                </div>
                
                <div class="stat-card-modern purple">
                    <div class="stat-header">
                        <div class="stat-icon-modern">
                            <i class="fa-solid fa-calendar-check"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?= count($ultimasNoticias) ?></div>
                    <div class="stat-label">Notícias Recentes</div>
                    <div class="stat-trend neutral">
                        <i class="fa-solid fa-clock"></i>
                        <span>Últimos 30 dias</span>
                    </div>
                </div>
                
                <div class="stat-card-modern teal">
                    <div class="stat-header">
                        <div class="stat-icon-modern">
                            <i class="fa-solid fa-chart-line"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?= ($totalNoticias + $totalDepoimentos) ?></div>
                    <div class="stat-label">Conteúdo Total</div>
                    <div class="stat-trend up">
                        <i class="fa-solid fa-arrow-up"></i>
                        <span>Crescimento</span>
                    </div>
                </div>
            </div>
            
            <!-- Main Dashboard Grid -->
            <div class="dashboard-main-grid">
                <!-- Recent Activities -->
                <div>
                    <!-- Latest News -->
                    <div class="card-modern" style="margin-bottom: 24px;">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fa-solid fa-newspaper"></i>
                                Últimas Notícias
                            </h3>
                            <a href="noticias.php" class="view-all-link">
                                Ver todas <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                        
                        <div class="activity-list">
                            <?php if (empty($ultimasNoticias)): ?>
                                <div class="empty-state">
                                    <i class="fa-solid fa-newspaper"></i>
                                    <p>Nenhuma notícia cadastrada ainda.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($ultimasNoticias as $noticia): ?>
                                    <a href="noticia-editar.php?id=<?= $noticia['id'] ?>" class="activity-item">
                                        <div class="activity-icon news">
                                            <i class="fa-solid fa-newspaper"></i>
                                        </div>
                                        <div class="activity-content">
                                            <div class="activity-title"><?= htmlspecialchars($noticia['titulo']) ?></div>
                                            <div class="activity-meta">
                                                <span class="category-badge <?= strtolower($noticia['categoria']) ?>">
                                                    <?= htmlspecialchars($noticia['categoria']) ?>
                                                </span>
                                                <span><i class="fa-regular fa-calendar"></i> <?= date('d/m/Y', strtotime($noticia['data_publicacao'])) ?></span>
                                                <?php if ($noticia['destaque']): ?>
                                                    <span style="color: #f59e0b;"><i class="fa-solid fa-star"></i> Destaque</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Latest Players -->
                    <div class="card-modern">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fa-solid fa-users"></i>
                                Últimos Jogadores
                            </h3>
                            <a href="jogadores.php" class="view-all-link">
                                Ver todos <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                        
                        <div class="activity-list">
                            <?php if (empty($ultimosJogadores)): ?>
                                <div class="empty-state">
                                    <i class="fa-solid fa-users"></i>
                                    <p>Nenhum jogador cadastrado ainda.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($ultimosJogadores as $jogador): ?>
                                    <a href="jogador-editar.php?id=<?= $jogador['id'] ?>" class="activity-item">
                                        <div class="activity-icon player">
                                            <i class="fa-solid fa-user"></i>
                                        </div>
                                        <div class="activity-content">
                                            <div class="activity-title"><?= htmlspecialchars($jogador['nome']) ?></div>
                                            <div class="activity-meta">
                                                <span><i class="fa-solid fa-shirt"></i> Posição: <?= htmlspecialchars($jogador['posicao']) ?></span>
                                                <?php if (!empty($jogador['numero_camisa'])): ?>
                                                    <span><i class="fa-solid fa-hashtag"></i> Nº <?= $jogador['numero_camisa'] ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Sidebar: Quick Actions & Testimonials -->
                <div>
                    <!-- Quick Actions -->
                    <div class="card-modern" style="margin-bottom: 24px;">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fa-solid fa-bolt"></i>
                                Ações Rápidas
                            </h3>
                        </div>
                        
                        <div class="quick-actions-grid">
                            <a href="noticia-criar.php" class="quick-action">
                                <div class="quick-action-icon">
                                    <i class="fa-solid fa-plus"></i>
                                </div>
                                <span>Nova Notícia</span>
                            </a>
                            
                            <a href="jogador-criar.php" class="quick-action">
                                <div class="quick-action-icon">
                                    <i class="fa-solid fa-user-plus"></i>
                                </div>
                                <span>Novo Jogador</span>
                            </a>
                            
                            <a href="depoimento-criar.php" class="quick-action">
                                <div class="quick-action-icon">
                                    <i class="fa-solid fa-comment-medical"></i>
                                </div>
                                <span>Novo Depoimento</span>
                            </a>
                            
                            <a href="plano-criar.php" class="quick-action">
                                <div class="quick-action-icon">
                                    <i class="fa-solid fa-tag"></i>
                                </div>
                                <span>Novo Plano</span>
                            </a>
                            
                            <a href="banner-criar.php" class="quick-action">
                                <div class="quick-action-icon">
                                    <i class="fa-solid fa-image"></i>
                                </div>
                                <span>Novo Banner</span>
                            </a>
                            
                            <a href="configuracoes.php" class="quick-action">
                                <div class="quick-action-icon">
                                    <i class="fa-solid fa-cog"></i>
                                </div>
                                <span>Configurações</span>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Latest Testimonials -->
                    <div class="card-modern">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fa-solid fa-comments"></i>
                                Últimos Depoimentos
                            </h3>
                            <a href="depoimentos.php" class="view-all-link">
                                Ver todos <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                        
                        <div class="activity-list">
                            <?php if (empty($ultimosDepoimentos)): ?>
                                <div class="empty-state">
                                    <i class="fa-solid fa-comments"></i>
                                    <p>Nenhum depoimento cadastrado ainda.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($ultimosDepoimentos as $depoimento): ?>
                                    <a href="depoimento-editar.php?id=<?= $depoimento['id'] ?>" class="activity-item">
                                        <div class="activity-icon testimonial">
                                            <i class="fa-solid fa-quote-left"></i>
                                        </div>
                                        <div class="activity-content">
                                            <div class="activity-title"><?= htmlspecialchars($depoimento['nome']) ?></div>
                                            <div class="activity-meta">
                                                <span><i class="fa-solid fa-briefcase"></i> <?= htmlspecialchars($depoimento['cargo']) ?></span>
                                                <?php if ($depoimento['ativo']): ?>
                                                    <span style="color: #10b981;"><i class="fa-solid fa-check-circle"></i> Ativo</span>
                                                <?php else: ?>
                                                    <span style="color: #64748b;"><i class="fa-solid fa-circle"></i> Inativo</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
