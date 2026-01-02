<?php
require_once 'auth.php';
Auth::require();

$user = Auth::user();

// Mensagens de sucesso/erro
$success = '';
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'created') $success = 'Banner cadastrado com sucesso!';
    if ($_GET['success'] === 'updated') $success = 'Banner atualizado com sucesso!';
    if ($_GET['success'] === 'deleted') $success = 'Banner excluído com sucesso!';
}

// Buscar banners
$banners = [];
try {
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT * FROM banners ORDER BY ordem ASC, id DESC");
    $stmt->execute();
    $banners = $stmt->fetchAll();
} catch (Exception $e) {
    logError('Erro ao buscar banners', ['error' => $e->getMessage()]);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Banners - Painel Administrativo</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/15d6bd6a1c.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/jogadores.css">
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <?php include 'includes/topbar.php'; ?>

            <div class="content">
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?= $success ?>
                    </div>
                <?php endif; ?>

                <div class="page-header">
                    <div>
                        <h1><i class="fas fa-images"></i> Banners</h1>
                        <p>Gerencie os banners do carrossel da página inicial</p>
                    </div>
                    <a href="banner-criar.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Novo Banner
                    </a>
                </div>

                <div class="table-card">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Imagem</th>
                                <th>Título</th>
                                <th>Ordem</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($banners) > 0): ?>
                                <?php foreach ($banners as $banner): ?>
                                    <tr>
                                        <td>
                                            <img src="../<?= htmlspecialchars($banner['imagem']) ?>" 
                                                 alt="<?= htmlspecialchars($banner['titulo']) ?>" 
                                                 style="width: 80px; height: 45px; object-fit: cover; border-radius: 4px;"
                                                 onerror="this.src='../assets/hero.png'">
                                        </td>
                                        <td><?= htmlspecialchars($banner['titulo']) ?></td>
                                        <td>
                                            <span class="badge badge-info"><?= $banner['ordem'] ?></span>
                                        </td>
                                        <td>
                                            <?php if ($banner['ativo']): ?>
                                                <span class="badge badge-success">Ativo</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Inativo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="actions">
                                            <a href="banner-editar.php?id=<?= $banner['id'] ?>" class="btn btn-sm btn-info" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="banner-excluir.php?id=<?= $banner['id'] ?>" class="btn btn-sm btn-danger" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir este banner?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="empty-state">
                                        <i class="fas fa-images"></i>
                                        <p>Nenhum banner cadastrado</p>
                                        <a href="banner-criar.php" class="btn btn-primary">Cadastrar Primeiro Banner</a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
