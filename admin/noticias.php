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
    
    $sql .= " ORDER BY data_publicacao DESC, id DESC";
    
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

                <div class="page-header">
                    <div>
                        <h1><i class="fas fa-newspaper"></i> Notícias</h1>
                        <p>Gerencie as notícias do site</p>
                    </div>
                    <a href="noticia-criar.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nova Notícia
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
                <div class="table-card">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th width="60">ID</th>
                                <th>Título</th>
                                <th width="180">Categoria</th>
                                <th width="120">Data</th>
                                <th width="80">Status</th>
                                <th width="150">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($noticias)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">Nenhuma notícia encontrada</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($noticias as $noticia): ?>
                                    <tr>
                                        <td><?= $noticia['id'] ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($noticia['titulo']) ?></strong>
                                            <?php if ($noticia['destaque']): ?>
                                                <span class="badge badge-warning">Destaque</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($noticia['categoria']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($noticia['data_publicacao'])) ?></td>
                                        <td>
                                            <?php if ($noticia['ativo']): ?>
                                                <span class="badge badge-success">Ativo</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">Inativo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="../noticia.php?id=<?= $noticia['id'] ?>" class="btn-icon btn-secondary" title="Visualizar" target="_blank">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="noticia-editar.php?id=<?= $noticia['id'] ?>" class="btn-icon btn-primary" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="noticia-toggle.php?id=<?= $noticia['id'] ?>" class="btn-icon btn-toggle <?= $noticia['ativo'] ? 'active' : '' ?>" 
                                                   title="<?= $noticia['ativo'] ? 'Desativar' : 'Ativar' ?>">
                                                    <i class="fas fa-<?= $noticia['ativo'] ? 'toggle-on' : 'toggle-off' ?>"></i>
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