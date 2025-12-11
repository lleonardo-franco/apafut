<header class="topbar">
    <div class="topbar-left">
        <h1><?= $pageTitle ?? 'Dashboard' ?></h1>
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
