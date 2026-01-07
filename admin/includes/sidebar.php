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
        <!-- TESTE: ARQUIVO ATUALIZADO -->
        <a href="noticias.php" class="menu-item <?= basename($_SERVER['PHP_SELF']) === 'noticias.php' || strpos(basename($_SERVER['PHP_SELF']), 'noticia-') === 0 ? 'active' : '' ?>">
            <i class="fas fa-newspaper"></i>
            <span>Notícias</span>
        </a>
        <a href="banners.php" class="menu-item <?= basename($_SERVER['PHP_SELF']) === 'banners.php' || strpos(basename($_SERVER['PHP_SELF']), 'banner-') === 0 ? 'active' : '' ?>">
            <i class="fas fa-images"></i>
            <span>Banners</span>
        </a>
        <a href="jogadores.php" class="menu-item <?= basename($_SERVER['PHP_SELF']) === 'jogadores.php' || strpos(basename($_SERVER['PHP_SELF']), 'jogador-') === 0 ? 'active' : '' ?>">
            <i class="fas fa-users"></i>
            <span>Jogadores</span>
        </a>
        <a href="comissao.php" class="menu-item <?= basename($_SERVER['PHP_SELF']) === 'comissao.php' || strpos(basename($_SERVER['PHP_SELF']), 'comissao-') === 0 ? 'active' : '' ?>">
            <i class="fas fa-users-cog"></i>
            <span>Comissão Técnica</span>
        </a>
        <a href="diretoria.php" class="menu-item <?= basename($_SERVER['PHP_SELF']) === 'diretoria.php' || strpos(basename($_SERVER['PHP_SELF']), 'diretoria-') === 0 ? 'active' : '' ?>">
            <i class="fas fa-user-tie"></i>
            <span>Diretoria</span>
        </a>
        <a href="depoimentos.php" class="menu-item <?= basename($_SERVER['PHP_SELF']) === 'depoimentos.php' || strpos(basename($_SERVER['PHP_SELF']), 'depoimento-') === 0 ? 'active' : '' ?>">
            <i class="fas fa-comments"></i>
            <span>Depoimentos</span>
        </a>
        <a href="planos.php" class="menu-item <?= basename($_SERVER['PHP_SELF']) === 'planos.php' || strpos(basename($_SERVER['PHP_SELF']), 'plano-') === 0 ? 'active' : '' ?>">
            <i class="fas fa-tags"></i>
            <span>Planos</span>
        </a>
        <a href="assinaturas.php" class="menu-item <?= basename($_SERVER['PHP_SELF']) === 'assinaturas.php' || strpos(basename($_SERVER['PHP_SELF']), 'assinatura-') === 0 ? 'active' : '' ?>">
            <i class="fas fa-file-contract"></i>
            <span>Assinaturas</span>
        </a>
        
        <div class="menu-section-title">Sistema</div>
        <a href="configuracoes.php" class="menu-item <?= basename($_SERVER['PHP_SELF']) === 'configuracoes.php' ? 'active' : '' ?>">
            <i class="fas fa-cog"></i>
            <span>Configurações</span>
        </a>
    </nav>
</aside>