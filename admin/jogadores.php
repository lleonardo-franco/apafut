<?php
require_once 'auth.php';
Auth::require();

$user = Auth::user();

// Mensagens de sucesso/erro
$success = '';
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'created') $success = 'Jogador cadastrado com sucesso!';
    if ($_GET['success'] === 'updated') $success = 'Jogador atualizado com sucesso!';
    if ($_GET['success'] === 'deleted') $success = 'Jogador excluído com sucesso!';
}

// Buscar jogadores
$jogadores = [];
try {
    $conn = getConnection();
    
    // Filtros
    $search = $_GET['search'] ?? '';
    $posicao = $_GET['posicao'] ?? '';
    
    $sql = "SELECT * FROM jogadores WHERE 1=1";
    
    if ($search) {
        $sql .= " AND (nome LIKE :search OR numero LIKE :search)";
    }
    if ($posicao) {
        $sql .= " AND posicao = :posicao";
    }
    
    $sql .= " ORDER BY ordem ASC, numero ASC, id DESC";
    
    $stmt = $conn->prepare($sql);
    
    if ($search) {
        $searchParam = "%$search%";
        $stmt->bindParam(':search', $searchParam);
    }
    if ($posicao) {
        $stmt->bindParam(':posicao', $posicao);
    }
    
    $stmt->execute();
    $jogadores = $stmt->fetchAll();
    
} catch (Exception $e) {
    logError('Erro ao buscar jogadores', ['error' => $e->getMessage()]);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Jogadores - Painel Administrativo</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/15d6bd6a1c.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/noticias.css">
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

                <div class="page-header-balanced">
                    <div class="header-left">
                        <div class="icon-wrapper">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="header-text">
                            <h1>Jogadores</h1>
                            <p>Gerencie o elenco do time</p>
                        </div>
                    </div>
                    <a href="jogador-criar.php" class="btn-balanced">
                        <i class="fas fa-plus"></i> Novo Jogador
                    </a>
                </div>

                <!-- Filtros -->
                <div class="filters-card">
                    <form method="GET" class="filters-form">
                        <div class="filter-group search-group">
                            <i class="fas fa-search"></i>
                            <input type="text" name="search" placeholder="Buscar por nome ou número..." value="<?= htmlspecialchars($search) ?>">
                        </div>
                        <div class="filter-group">
                            <select name="posicao">
                                <option value="">Todas posições</option>
                                <option value="Goleiro" <?= $posicao === 'Goleiro' ? 'selected' : '' ?>>Goleiro</option>
                                <option value="Zagueiro" <?= $posicao === 'Zagueiro' ? 'selected' : '' ?>>Zagueiro</option>
                                <option value="Lateral" <?= $posicao === 'Lateral' ? 'selected' : '' ?>>Lateral</option>
                                <option value="Volante" <?= $posicao === 'Volante' ? 'selected' : '' ?>>Volante</option>
                                <option value="Meia" <?= $posicao === 'Meia' ? 'selected' : '' ?>>Meia</option>
                                <option value="Atacante" <?= $posicao === 'Atacante' ? 'selected' : '' ?>>Atacante</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                        <?php if ($search || $posicao): ?>
                            <a href="jogadores.php" class="btn btn-light">
                                <i class="fas fa-redo"></i> Limpar
                            </a>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- Grid de Jogadores -->
                <div class="jogadores-grid">
                    <?php if (empty($jogadores)): ?>
                        <p class="empty-message">Nenhum jogador encontrado</p>
                    <?php else: ?>
                        <?php foreach ($jogadores as $jogador): ?>
                            <div class="jogador-card">
                                <div class="jogador-foto <?= empty($jogador['foto']) ? 'sem-foto' : '' ?>">
                                    <?php if (!empty($jogador['foto'])): ?>
                                        <img src="<?= htmlspecialchars($jogador['foto']) ?>" alt="<?= htmlspecialchars($jogador['nome']) ?>">
                                    <?php else: ?>
                                        <div class="foto-placeholder">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="jogador-numero"><?= $jogador['numero'] ?></div>
                                    <?php if (!$jogador['ativo']): ?>
                                        <div class="status-badge inativo">Inativo</div>
                                    <?php endif; ?>
                                </div>
                                <div class="jogador-info">
                                    <h3><?= htmlspecialchars($jogador['nome']) ?></h3>
                                    <p class="posicao"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($jogador['posicao']) ?></p>
                                </div>
                                <div class="jogador-actions">
                                    <a href="jogador-editar.php?id=<?= $jogador['id'] ?>" class="btn-icon btn-primary" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn-icon btn-danger" title="Excluir" onclick="confirmarExclusao(<?= $jogador['id'] ?>, '<?= htmlspecialchars($jogador['nome'], ENT_QUOTES) ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal de Confirmação -->
    <div id="modalExcluir" class="modal">
        <div class="modal-overlay"></div>
        <div class="modal-content">
            <div class="modal-header">
                <i class="fas fa-exclamation-triangle"></i>
                <h3>Confirmar Exclusão</h3>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir o jogador <strong id="nomeJogador"></strong>?</p>
                <p class="modal-warning">Esta ação não pode ser desfeita.</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-light" onclick="fecharModal()">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <a id="btnConfirmarExcluir" href="#" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Excluir
                </a>
            </div>
        </div>
    </div>

    <script>
        function confirmarExclusao(id, nome) {
            document.getElementById('nomeJogador').textContent = nome;
            document.getElementById('btnConfirmarExcluir').href = 'jogador-excluir.php?id=' + id;
            document.getElementById('modalExcluir').classList.add('show');
        }

        function fecharModal() {
            document.getElementById('modalExcluir').classList.remove('show');
        }

        // Fechar modal ao clicar no overlay
        document.querySelector('.modal-overlay').addEventListener('click', fecharModal);

        // Fechar modal com ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                fecharModal();
            }
        });
    </script>
</body>
</html>
