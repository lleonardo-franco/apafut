<aside class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <img src="../assets/logo.png" alt="Logo Apafut">
            <span>Apafut Admin</span>
        </div>
    </div>
    
    <nav class="sidebar-menu">
        <div class="menu-section-title">Principal</div>
        <a href="dashboard.php" class="menu-item <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>
        
        <div class="menu-section-title">Conteúdo</div>
        <a href="noticias.php" class="menu-item <?= basename($_SERVER['PHP_SELF']) === 'noticias.php' || strpos(basename($_SERVER['PHP_SELF']), 'noticia-') === 0 ? 'active' : '' ?>">
            <i class="fas fa-newspaper"></i>
            <span>Notícias</span>
        </a>
        <a href="jogadores.php" class="menu-item <?= basename($_SERVER['PHP_SELF']) === 'jogadores.php' || strpos(basename($_SERVER['PHP_SELF']), 'jogador-') === 0 ? 'active' : '' ?>">
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
