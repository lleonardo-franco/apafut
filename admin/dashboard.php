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
    
    // Total de jogadores
    $stmt = $conn->query("SELECT COUNT(*) as total FROM jogadores WHERE ativo = 1");
    $totalJogadores = $stmt->fetch()['total'];
    
    // Total de depoimentos
    $stmt = $conn->query("SELECT COUNT(*) as total FROM depoimentos WHERE ativo = 1");
    $totalDepoimentos = $stmt->fetch()['total'];
    
    // Total de planos
    $stmt = $conn->query("SELECT COUNT(*) as total FROM planos WHERE ativo = 1");
    $totalPlanos = $stmt->fetch()['total'];
    
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
                <a href="jogadores.php" class="menu-item">
                    <i class="fas fa-users"></i>
                    <span>Jogadores</span>
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-trophy"></i>
                    <span>Campeonatos</span>
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-comments"></i>
                    <span>Depoimentos</span>
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-users-cog"></i>
                    <span>Categorias de Base</span>
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-tags"></i>
                    <span>Planos</span>
                </a>
                
                <div class="menu-section-title">Sistema</div>
                <a href="#" class="menu-item">
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
            </div>
        </main>
    </div>
</body>
</html>
