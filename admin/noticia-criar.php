<?php
require_once 'auth.php';
Auth::require();

$user = Auth::user();
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn = getConnection();
        
        // Validar dados
        $titulo = Security::sanitizeString($_POST['titulo'] ?? '');
        $categoria = Security::sanitizeString($_POST['categoria'] ?? '');
        $resumo = Security::sanitizeString($_POST['resumo'] ?? '');
        $conteudo = $_POST['conteudo'] ?? '';
        $autor = Security::sanitizeString($_POST['autor'] ?? $user['nome']);
        $tempo_leitura = Security::validateInt($_POST['tempo_leitura'] ?? 5, 1);
        $ativo = isset($_POST['ativo']) ? 1 : 0;
        $destaque = isset($_POST['destaque']) ? 1 : 0;
        $data_publicacao = $_POST['data_publicacao'] ?? date('Y-m-d');
        
        if (empty($titulo) || empty($categoria) || empty($resumo) || empty($conteudo)) {
            throw new Exception('Por favor, preencha todos os campos obrigatórios');
        }
        
        // Processar upload de imagem
        $imagem = '';
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../assets/images/noticias/';
            
            // Criar diretório se não existir
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileInfo = pathinfo($_FILES['imagem']['name']);
            $extension = strtolower($fileInfo['extension']);
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (!in_array($extension, $allowedExtensions)) {
                throw new Exception('Formato de imagem inválido. Use: JPG, PNG, GIF ou WEBP');
            }
            
            // Validar tamanho (máx 5MB)
            if ($_FILES['imagem']['size'] > 5 * 1024 * 1024) {
                throw new Exception('Imagem muito grande. Tamanho máximo: 5MB');
            }
            
            // Gerar nome único
            $fileName = gerarSlug($titulo) . '-' . time() . '.' . $extension;
            $filePath = $uploadDir . $fileName;
            
            if (!move_uploaded_file($_FILES['imagem']['tmp_name'], $filePath)) {
                throw new Exception('Erro ao fazer upload da imagem');
            }
            
            $imagem = 'assets/images/noticias/' . $fileName;
        }
        
        // Inserir notícia
        $stmt = $conn->prepare("
            INSERT INTO noticias (titulo, categoria, resumo, conteudo, imagem, autor, tempo_leitura, data_publicacao, ativo, destaque)
            VALUES (:titulo, :categoria, :resumo, :conteudo, :imagem, :autor, :tempo_leitura, :data_publicacao, :ativo, :destaque)
        ");
        
        $stmt->bindParam(':titulo', $titulo);
        $stmt->bindParam(':categoria', $categoria);
        $stmt->bindParam(':resumo', $resumo);
        $stmt->bindParam(':conteudo', $conteudo);
        $stmt->bindParam(':imagem', $imagem);
        $stmt->bindParam(':autor', $autor);
        $stmt->bindParam(':tempo_leitura', $tempo_leitura);
        $stmt->bindParam(':data_publicacao', $data_publicacao);
        $stmt->bindParam(':ativo', $ativo);
        $stmt->bindParam(':destaque', $destaque);
        
        $stmt->execute();
        
        $noticiaId = $conn->lastInsertId();
        
        logError('Notícia criada', [
            'id' => $noticiaId,
            'titulo' => $titulo,
            'user' => $user['email']
        ]);
        
        header('Location: noticias.php?success=created');
        exit;
        
    } catch (Exception $e) {
        logError('Erro ao criar notícia', ['error' => $e->getMessage()]);
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Notícia - Painel Administrativo</title>
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
            <?php $pageTitle = 'Nova Notícia'; include 'includes/topbar.php'; ?>

            <div class="content">
                <div class="page-header">
                    <div>
                        <h1><i class="fas fa-plus"></i> Nova Notícia</h1>
                        <p>Criar uma nova notícia</p>
                    </div>
                    <a href="noticias.php" class="btn btn-light">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" class="form-card">
                    <div class="form-group">
                        <label for="titulo">Título *</label>
                        <input type="text" id="titulo" name="titulo" required value="<?= htmlspecialchars($_POST['titulo'] ?? '') ?>">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="categoria">Categoria *</label>
                            <select id="categoria" name="categoria" required>
                                <option value="">Selecione...</option>
                                <option value="Campeonatos" <?= ($_POST['categoria'] ?? '') === 'Campeonatos' ? 'selected' : '' ?>>Campeonatos</option>
                                <option value="Categorias de Base" <?= ($_POST['categoria'] ?? '') === 'Categorias de Base' ? 'selected' : '' ?>>Categorias de Base</option>
                                <option value="Infraestrutura" <?= ($_POST['categoria'] ?? '') === 'Infraestrutura' ? 'selected' : '' ?>>Infraestrutura</option>
                                <option value="Eventos" <?= ($_POST['categoria'] ?? '') === 'Eventos' ? 'selected' : '' ?>>Eventos</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="data_publicacao">Data de Publicação *</label>
                            <input type="date" id="data_publicacao" name="data_publicacao" required value="<?= $_POST['data_publicacao'] ?? date('Y-m-d') ?>">
                        </div>

                        <div class="form-group">
                            <label for="tempo_leitura">Tempo de Leitura (min)</label>
                            <input type="number" id="tempo_leitura" name="tempo_leitura" min="1" value="<?= $_POST['tempo_leitura'] ?? 5 ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="resumo">Resumo *</label>
                        <textarea id="resumo" name="resumo" rows="3" required><?= htmlspecialchars($_POST['resumo'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="conteudo">Conteúdo *</label>
                        <textarea id="conteudo" name="conteudo" rows="15" required><?= htmlspecialchars($_POST['conteudo'] ?? '') ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="imagem">Imagem da Notícia</label>
                            <input type="file" id="imagem" name="imagem" accept="image/jpeg,image/png,image/gif,image/webp">
                            <small style="color: var(--text-light); font-size: 12px; margin-top: 4px; display: block;">Formatos: JPG, PNG, GIF, WEBP | Máximo: 5MB</small>
                        </div>

                        <div class="form-group">
                            <label for="autor">Autor</label>
                            <input type="text" id="autor" name="autor" value="<?= htmlspecialchars($_POST['autor'] ?? $user['nome']) ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <div class="checkbox-group">
                                <input type="checkbox" id="ativo" name="ativo" <?= isset($_POST['ativo']) || !$_POST ? 'checked' : '' ?>>
                                <label for="ativo" style="margin-bottom: 0;">Publicar notícia (ativa)</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="checkbox-group">
                                <input type="checkbox" id="destaque" name="destaque" <?= isset($_POST['destaque']) ? 'checked' : '' ?>>
                                <label for="destaque" style="margin-bottom: 0;">Notícia em destaque</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Criar Notícia
                        </button>
                        <a href="noticias.php" class="btn btn-light">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
