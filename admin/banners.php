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
    <link rel="icon" href="../assets/logo.ico" type="image/x-icon">
    <link rel="shortcut icon" href="../assets/logo.ico" type="image/x-icon">
    <link rel="apple-touch-icon" href="../assets/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/15d6bd6a1c.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/noticias.css">
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
                            <i class="fas fa-images"></i>
                        </div>
                        <div>
                            <h1>Banners</h1>
                            <p>Gerencie os banners do carrossel da página inicial</p>
                        </div>
                    </div>
                    <a href="banner-criar.php" class="btn-balanced">
                        <i class="fas fa-plus"></i> Novo Banner
                    </a>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?= $success ?>
                    </div>
                <?php endif; ?>

                <div class="table-card">
                    <table class="table-styled">
                        <thead>
                            <tr>
                                <th style="width: 100px;">Imagem</th>
                                <th>Título</th>
                                <th style="width: 100px; text-align: center;">Ordem</th>
                                <th style="width: 120px; text-align: center;">Status</th>
                                <th style="width: 140px; text-align: center;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($banners) > 0): ?>
                                <?php foreach ($banners as $banner): ?>
                                    <tr>
                                        <td>
                                            <img src="../<?= htmlspecialchars($banner['imagem']) ?>" 
                                                 alt="<?= htmlspecialchars($banner['titulo']) ?>" 
                                                 class="table-image"
                                                 onerror="this.src='../assets/hero.png'">
                                        </td>
                                        <td class="col-title">
                                            <strong><?= htmlspecialchars($banner['titulo']) ?></strong>
                                            <?php if (!empty($banner['descricao'])): ?>
                                                <br><small style="color: #757575;"><?= htmlspecialchars(substr($banner['descricao'], 0, 50)) ?>...</small>
                                            <?php endif; ?>
                                        </td>
                                        <td style="text-align: center;">
                                            <span class="badge-id"><?= $banner['ordem'] ?></span>
                                        </td>
                                        <td style="text-align: center;">
                                            <?php if ($banner['ativo']): ?>
                                                <span class="badge-status active">
                                                    <i class="fas fa-check-circle"></i> Ativo
                                                </span>
                                            <?php else: ?>
                                                <span class="badge-status inactive">
                                                    <i class="fas fa-times-circle"></i> Inativo
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="actions-flex">
                                                <a href="banner-editar.php?id=<?= $banner['id'] ?>" class="btn-action edit" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="banner-excluir.php?id=<?= $banner['id'] ?>" class="btn-action toggle-inactive" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir este banner?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 60px 20px;">
                                        <i class="fas fa-images" style="font-size: 48px; color: #e0e0e0; margin-bottom: 16px;"></i>
                                        <p style="color: #757575; font-size: 15px; margin-bottom: 20px;">Nenhum banner cadastrado</p>
                                        <a href="banner-criar.php" class="btn-balanced">
                                            <i class="fas fa-plus"></i> Cadastrar Primeiro Banner
                                        </a>
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
