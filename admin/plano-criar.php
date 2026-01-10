<?php
header('Content-Type: text/html; charset=UTF-8');
mb_internal_encoding('UTF-8');
require_once '../config/db.php';
require_once '../src/Cache.php';
require_once 'auth.php';

$erro = '';
$sucesso = '';

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
            
            $sql = "INSERT INTO planos (nome, tipo, preco_anual, parcelas, beneficios, ordem, ativo, destaque) 
                    VALUES (:nome, :tipo, :preco_anual, :parcelas, :beneficios, :ordem, :ativo, :destaque)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nome' => $nome,
                ':tipo' => $tipo,
                ':preco_anual' => $preco_anual,
                ':parcelas' => $parcelas,
                ':beneficios' => $beneficios,
                ':ordem' => $ordem,
                ':ativo' => $ativo,
                ':destaque' => $destaque
            ]);
            
            // Limpar cache de planos
            Cache::delete('planos_ativos');
            
            header('Location: planos.php?sucesso=criado');
            exit;
            
        } catch (Exception $e) {
            $erro = 'Erro ao criar plano: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Plano - Painel Administrativo</title>
    <link rel="icon" type="image/x-icon" href="/apafut/assets/logo.ico">
    <link rel="shortcut icon" type="image/x-icon" href="/apafut/assets/logo.ico">
    <link rel="apple-touch-icon" href="/apafut/assets/logo.png">
    <link rel="icon" href="../assets/logo.ico" type="image/x-icon">
    <link rel="shortcut icon" href="../assets/logo.ico" type="image/x-icon">
    
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/noticias.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
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
                            <i class="fas fa-crown"></i>
                        </div>
                        <div class="header-text">
                            <h1>Novo Plano</h1>
                            <p>Adicione um novo plano de sócio</p>
                        </div>
                    </div>
                    <a href="planos.php" class="btn-balanced-light">
                        <i class="fas fa-arrow-left"></i> Voltar
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

                <div class="form-card">
                    <form method="POST" enctype="multipart/form-data" class="form-balanced">
                        <!-- Seção: Informações Básicas -->
                        <div class="form-section">
                            <h3 class="section-title">Informações Básicas</h3>
                            
                            <div class="form-group">
                                <label for="nome">Nome do Plano *</label>
                                <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>" placeholder="Ex: Sócio APA Ouro">
                            </div>

                            <div class="form-group">
                                <label for="tipo">Tipo *</label>
                                <select id="tipo" name="tipo">
                                    <option value="">Selecione o tipo</option>
                                    <option value="Prata" <?= ($_POST['tipo'] ?? '') === 'Prata' ? 'selected' : '' ?>>Prata</option>
                                    <option value="Ouro" <?= ($_POST['tipo'] ?? '') === 'Ouro' ? 'selected' : '' ?>>Ouro</option>
                                    <option value="Diamante" <?= ($_POST['tipo'] ?? '') === 'Diamante' ? 'selected' : '' ?>>Diamante</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Seção: Valores -->
                        <div class="form-section">
                            <h3 class="section-title">Valores</h3>
                            
                            <div class="form-group">
                                <label for="preco_anual">Preço Anual (R$) *</label>
                                <input type="number" id="preco_anual" name="preco_anual" step="0.01" min="0" value="<?= htmlspecialchars($_POST['preco_anual'] ?? '') ?>" placeholder="Ex: 300.00">
                            </div>

                            <div class="form-group">
                                <label for="parcelas">Número de Parcelas *</label>
                                <input type="number" id="parcelas" name="parcelas" min="1" max="12" value="<?= htmlspecialchars($_POST['parcelas'] ?? '2') ?>">
                            </div>
                        </div>
                        
                        <!-- Seção: Benefícios -->
                        <div class="form-section">
                            <h3 class="section-title">Benefícios</h3>
                            
                            <div class="form-group">
                                <label for="beneficios">Lista de Benefícios *</label>
                                <textarea id="beneficios" name="beneficios" rows="6" placeholder="Digite um benefício por linha. Exemplo:&#10;Camiseta oficial exclusiva&#10;Jantar de fim de temporada&#10;Descontos com parceiros"><?= htmlspecialchars($_POST['beneficios'] ?? '') ?></textarea>
                                <small style="display: block; margin-top: 8px; color: #666;">Digite um benefício por linha</small>
                            </div>
                        </div>
                        
                        <!-- Seção: Configurações -->
                        <div class="form-section">
                            <h3 class="section-title">Configurações</h3>
                            
                            <div class="form-group">
                                <label for="ordem">Ordem de Exibição</label>
                                <input type="number" id="ordem" name="ordem" value="<?= htmlspecialchars($_POST['ordem'] ?? '0') ?>" min="0" placeholder="0">
                            </div>
                            
                            <div class="checkbox-wrapper">
                                <div class="checkbox-item">
                                    <label for="ativo">
                                        <input type="checkbox" id="ativo" name="ativo" <?= isset($_POST['ativo']) || !isset($_POST['nome']) ? 'checked' : '' ?>>
                                        <div class="checkbox-label-text">
                                            <strong>Plano Ativo</strong>
                                            <small>Apenas planos ativos são exibidos no site</small>
                                        </div>
                                    </label>
                                </div>
                                <div class="checkbox-item">
                                    <label for="destaque">
                                        <input type="checkbox" id="destaque" name="destaque" <?= isset($_POST['destaque']) ? 'checked' : '' ?>>
                                        <div class="checkbox-label-text">
                                            <strong>Marcar como Destaque</strong>
                                            <small>Plano em destaque recebe maior visibilidade</small>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-balanced">
                                <i class="fas fa-check"></i> Criar Plano
                            </button>
                            <a href="planos.php" class="btn-balanced-light">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Auto-formatar benefícios: converter separadores | para quebras de linha
        document.getElementById('beneficios').addEventListener('blur', function() {
            if (this.value.includes('|')) {
                this.value = this.value.split('|').map(b => b.trim()).join('\n');
            }
        });

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
