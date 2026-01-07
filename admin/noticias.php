<?php
require_once 'auth.php';
Auth::require();

$user = Auth::user();

// Mensagens de sucesso/erro
$success = '';
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'created') $success = 'Notícia criada com sucesso!';
    if ($_GET['success'] === 'deleted') $success = 'Notícia excluída com sucesso!';
    if ($_GET['success'] === 'ativada') $success = 'Notícia ativada com sucesso!';
    if ($_GET['success'] === 'desativada') $success = 'Notícia desativada com sucesso!';
}

// Buscar notícias
try {
    $conn = getConnection();
    
    // Filtros
    $search = $_GET['search'] ?? '';
    $categoria = $_GET['categoria'] ?? '';
    $status = $_GET['status'] ?? '';
    
    $sql = "SELECT * FROM noticias WHERE 1=1";
    
    if ($search) {
        $sql .= " AND (titulo LIKE :search OR conteudo LIKE :search)";
    }
    if ($categoria) {
        $sql .= " AND categoria = :categoria";
    }
    if ($status !== '') {
        $sql .= " AND ativo = :status";
    }
    
    $sql .= " ORDER BY id ASC";
    
    $stmt = $conn->prepare($sql);
    
    if ($search) {
        $searchParam = "%$search%";
        $stmt->bindParam(':search', $searchParam);
    }
    if ($categoria) {
        $stmt->bindParam(':categoria', $categoria);
    }
    if ($status !== '') {
        $stmt->bindParam(':status', $status, PDO::PARAM_INT);
    }
    
    $stmt->execute();
    $noticias = $stmt->fetchAll();
    
} catch (Exception $e) {
    logError('Erro ao buscar notícias', ['error' => $e->getMessage()]);
    $noticias = [];
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Notícias - Painel Administrativo</title>
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
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>

                <div class="page-header-balanced">
                    <div class="header-left">
                        <div class="icon-wrapper">
                            <i class="fas fa-newspaper"></i>
                        </div>
                        <div>
                            <h1>Notícias</h1>
                            <p>Gerencie o conteúdo do site</p>
                        </div>
                    </div>
                    <a href="noticia-criar.php" class="btn-balanced">
                        <i class="fas fa-plus"></i>
                        <span>Nova Notícia</span>
                    </a>
                </div>

                <!-- Filtros -->
                <div class="filters-card">
                    <form method="GET" class="filters-form">
                        <div class="filter-group search-group">
                            <i class="fas fa-search"></i>
                            <input type="text" name="search" placeholder="Buscar por título ou conteúdo..." value="<?= htmlspecialchars($search) ?>">
                        </div>
                        <div class="filter-group">
                            <select name="categoria">
                                <option value="">Todas categorias</option>
                                <option value="Campeonatos" <?= $categoria === 'Campeonatos' ? 'selected' : '' ?>>Campeonatos</option>
                                <option value="Categorias de Base" <?= $categoria === 'Categorias de Base' ? 'selected' : '' ?>>Categorias de Base</option>
                                <option value="Infraestrutura" <?= $categoria === 'Infraestrutura' ? 'selected' : '' ?>>Infraestrutura</option>
                                <option value="Eventos" <?= $categoria === 'Eventos' ? 'selected' : '' ?>>Eventos</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <select name="status">
                                <option value="">Todos status</option>
                                <option value="1" <?= $status === '1' ? 'selected' : '' ?>>Ativos</option>
                                <option value="0" <?= $status === '0' ? 'selected' : '' ?>>Inativos</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                        <?php if ($search || $categoria || $status !== ''): ?>
                            <a href="noticias.php" class="btn btn-light">
                                <i class="fas fa-redo"></i> Limpar
                            </a>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- Listagem -->
                <div class="table-balanced">
                    <div class="table-top">
                        <h3><i class="fas fa-list"></i> Lista de Notícias</h3>
                        <span class="badge-count"><?= count($noticias) ?> notícias</span>
                    </div>
                    <table class="table-styled">
                            <thead>
                                <tr>
                                    <th width="60">ID</th>
                                    <th>Título</th>
                                    <th width="140">Categoria</th>
                                    <th width="110">Data</th>
                                    <th width="100">Status</th>
                                    <th width="140">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($noticias)): ?>
                                    <tr>
                                        <td colspan="6" class="empty-cell">
                                            <div class="empty-state">
                                                <i class="fas fa-inbox"></i>
                                                <p>Nenhuma notícia encontrada</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($noticias as $noticia): ?>
                                        <tr>
                                            <td><span class="badge-id"><?= $noticia['id'] ?></span></td>
                                            <td class="col-title">
                                                <strong><?= htmlspecialchars($noticia['titulo']) ?></strong>
                                                <?php if ($noticia['destaque']): ?>
                                                    <span class="badge-star"><i class="fas fa-star"></i></span>
                                                <?php endif; ?>
                                            </td>
                                            <td><span class="badge-cat"><?= htmlspecialchars($noticia['categoria']) ?></span></td>
                                            <td class="col-date">
                                                <i class="fas fa-calendar"></i>
                                                <?= date('d/m/Y', strtotime($noticia['data_publicacao'])) ?>
                                            </td>
                                            <td>
                                                <?php if ($noticia['ativo']): ?>
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
                                                    <a href="../noticia.php?id=<?= $noticia['id'] ?>" class="btn-action view" title="Visualizar" target="_blank">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="noticia-editar.php?id=<?= $noticia['id'] ?>" class="btn-action edit" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="noticia-toggle.php?id=<?= $noticia['id'] ?>" class="btn-action toggle <?= $noticia['ativo'] ? 'toggle-active' : 'toggle-inactive' ?>" title="<?= $noticia['ativo'] ? 'Desativar' : 'Ativar' ?>">
                                                        <i class="fas fa-toggle-<?= $noticia['ativo'] ? 'on' : 'off' ?>"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>