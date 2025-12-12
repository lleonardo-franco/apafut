<?php
header('Content-Type: text/html; charset=UTF-8');
mb_internal_encoding('UTF-8');
require_once '../config/db.php';
require_once 'auth.php';

$erro = '';
$sucesso = '';
$plano = null;

// Buscar plano
$id = intval($_GET['id'] ?? 0);
if ($id > 0) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT * FROM planos WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $plano = $stmt->fetch();
    
    if (!$plano) {
        header('Location: planos.php?erro=nao_encontrado');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $tipo = trim($_POST['tipo'] ?? '');
    $preco_anual = floatval($_POST['preco_anual'] ?? 0);
    $parcelas = intval($_POST['parcelas'] ?? 2);
    $beneficios = trim($_POST['beneficios'] ?? '');
    $ordem = intval($_POST['ordem'] ?? 0);
    $ativo = isset($_POST['ativo']) ? 1 : 0;
    $destaque = isset($_POST['destaque']) ? 1 : 0;
    
    // Validação
    if (empty($nome) || empty($tipo) || $preco_anual <= 0 || empty($beneficios)) {
        $erro = 'Todos os campos obrigatórios devem ser preenchidos';
    } else {
        try {
            $pdo = getConnection();
            
            $sql = "UPDATE planos SET 
                    nome = :nome, 
                    tipo = :tipo, 
                    preco_anual = :preco_anual, 
                    parcelas = :parcelas, 
                    beneficios = :beneficios, 
                    ordem = :ordem, 
                    ativo = :ativo, 
                    destaque = :destaque 
                    WHERE id = :id";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nome' => $nome,
                ':tipo' => $tipo,
                ':preco_anual' => $preco_anual,
                ':parcelas' => $parcelas,
                ':beneficios' => $beneficios,
                ':ordem' => $ordem,
                ':ativo' => $ativo,
                ':destaque' => $destaque,
                ':id' => $id
            ]);
            
            $sucesso = 'Plano atualizado com sucesso!';
            
            // Recarregar dados
            $stmt = $pdo->prepare("SELECT * FROM planos WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $plano = $stmt->fetch();
            
        } catch (Exception $e) {
            $erro = 'Erro ao atualizar plano: ' . $e->getMessage();
        }
    }
}

// Converter beneficios de | para quebras de linha para exibição
$beneficiosTexto = isset($plano['beneficios']) ? str_replace('|', "\n", $plano['beneficios']) : '';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Plano - Painel Administrativo</title>
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
                <div class="page-header">
                    <div>
                        <h1>Editar Plano</h1>
                        <p>Atualize as informações do plano</p>
                    </div>
                    <a href="planos.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Voltar
                    </a>
                </div>

                <?php if ($erro): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= htmlspecialchars($erro) ?>
                    </div>
                <?php endif; ?>

                <?php if ($sucesso): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?= htmlspecialchars($sucesso) ?>
                    </div>
                <?php endif; ?>

                <?php if ($plano): ?>
                <div class="form-card">
                    <form method="POST">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="nome">Nome do Plano *</label>
                                <input 
                                    type="text" 
                                    id="nome" 
                                    name="nome" 
                                    value="<?= htmlspecialchars($plano['nome']) ?>"
                                    placeholder="Ex: Sócio APA Ouro"
                                    required
                                >
                            </div>

                            <div class="form-group">
                                <label for="tipo">Tipo *</label>
                                <select id="tipo" name="tipo" required>
                                    <option value="">Selecione o tipo</option>
                                    <option value="Prata" <?= $plano['tipo'] === 'Prata' ? 'selected' : '' ?>>Prata</option>
                                    <option value="Ouro" <?= $plano['tipo'] === 'Ouro' ? 'selected' : '' ?>>Ouro</option>
                                    <option value="Diamante" <?= $plano['tipo'] === 'Diamante' ? 'selected' : '' ?>>Diamante</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="preco_anual">Preço Anual (R$) *</label>
                                <input 
                                    type="number" 
                                    id="preco_anual" 
                                    name="preco_anual" 
                                    step="0.01"
                                    min="0"
                                    value="<?= htmlspecialchars($plano['preco_anual']) ?>"
                                    placeholder="Ex: 300.00"
                                    required
                                >
                            </div>

                            <div class="form-group">
                                <label for="parcelas">Número de Parcelas *</label>
                                <input 
                                    type="number" 
                                    id="parcelas" 
                                    name="parcelas" 
                                    min="1"
                                    max="12"
                                    value="<?= htmlspecialchars($plano['parcelas']) ?>"
                                    required
                                >
                            </div>

                            <div class="form-group">
                                <label for="ordem">Ordem de Exibição</label>
                                <input 
                                    type="number" 
                                    id="ordem" 
                                    name="ordem" 
                                    value="<?= htmlspecialchars($plano['ordem']) ?>"
                                    min="0"
                                >
                                <small>Define a ordem de exibição no site (menor aparece primeiro)</small>
                            </div>
                        </div>

                        <div class="form-group full-width">
                            <label for="beneficios">Benefícios *</label>
                            <textarea 
                                id="beneficios" 
                                name="beneficios" 
                                rows="8"
                                placeholder="Digite um benefício por linha"
                                required
                            ><?= htmlspecialchars($beneficiosTexto) ?></textarea>
                            <small>Digite um benefício por linha</small>
                        </div>

                        <div class="form-row">
                            <div class="form-group-checkbox">
                                <input 
                                    type="checkbox" 
                                    id="ativo" 
                                    name="ativo"
                                    <?= $plano['ativo'] ? 'checked' : '' ?>
                                >
                                <label for="ativo">Plano Ativo</label>
                            </div>

                            <div class="form-group-checkbox">
                                <input 
                                    type="checkbox" 
                                    id="destaque" 
                                    name="destaque"
                                    <?= $plano['destaque'] ? 'checked' : '' ?>
                                >
                                <label for="destaque">Marcar como Destaque</label>
                            </div>
                        </div>

                        <div class="form-actions">
                            <a href="planos.php" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                Salvar Alterações
                            </button>
                        </div>
                    </form>
                </div>

                <div class="help-card">
                    <h3><i class="fas fa-info-circle"></i> Informações</h3>
                    <ul>
                        <li><strong>ID do Plano:</strong> #<?= $plano['id'] ?></li>
                        <li><strong>Criado em:</strong> <?= date('d/m/Y H:i', strtotime($plano['created_at'])) ?></li>
                        <li><strong>Status:</strong> <?= $plano['ativo'] ? 'Ativo' : 'Inativo' ?></li>
                    </ul>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        // Antes de enviar, converter quebras de linha para |
        document.querySelector('form').addEventListener('submit', function(e) {
            const beneficiosField = document.getElementById('beneficios');
            const beneficios = beneficiosField.value
                .split('\n')
                .map(b => b.trim())
                .filter(b => b.length > 0)
                .join('|');
            beneficiosField.value = beneficios;
        });
    </script>
</body>
</html>
