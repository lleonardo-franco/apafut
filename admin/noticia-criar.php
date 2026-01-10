<?php
require_once 'auth.php';
Auth::require();

$user = Auth::user();
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Log dos dados recebidos
        logError('POST recebido em noticia-criar.php', [
            'post_keys' => array_keys($_POST),
            'files_keys' => array_keys($_FILES),
            'titulo' => $_POST['titulo'] ?? 'vazio'
        ]);
        
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
        
        // Validar limite de notícias em destaque (máximo 6)
        if ($destaque) {
            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM noticias WHERE destaque = 1");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result['total'] >= 6) {
                throw new Exception('Limite de 6 notícias em destaque atingido. Remova o destaque de outra notícia primeiro.');
            }
        }
        
        $ordem = Security::validateInt($_POST['ordem'] ?? 0, 0);
        $data_publicacao = $_POST['data_publicacao'] ?? date('Y-m-d');
        $status = Security::sanitizeString($_POST['status'] ?? 'publicado');
        $data_agendamento = !empty($_POST['data_agendamento']) ? $_POST['data_agendamento'] : null;
        
        // Se for agendado, a data de publicação deve ser a mesma do agendamento
        if ($status === 'agendado' && $data_agendamento) {
            $data_publicacao = date('Y-m-d', strtotime($data_agendamento));
        }
        
        // Campos específicos de depoimentos
        $depoimento_texto = Security::sanitizeString($_POST['depoimento_texto'] ?? '');
        $depoimento_autor = Security::sanitizeString($_POST['depoimento_autor'] ?? '');
        
        // Validações avançadas
        if (empty($titulo) || empty($categoria) || empty($resumo) || empty($conteudo)) {
            throw new Exception('Por favor, preencha todos os campos obrigatórios');
        }
        
        if (strlen($titulo) < 10) {
            throw new Exception('O título deve ter no mínimo 10 caracteres');
        }
        
        if (strlen($titulo) > 200) {
            throw new Exception('O título deve ter no máximo 200 caracteres');
        }
        
        if (strlen($resumo) > 500) {
            throw new Exception('O resumo deve ter no máximo 500 caracteres');
        }
        
        if (strlen($conteudo) < 50) {
            throw new Exception('O conteúdo deve ter no mínimo 50 caracteres');
        }
        
        if ($tempo_leitura < 1 || $tempo_leitura > 60) {
            throw new Exception('O tempo de leitura deve estar entre 1 e 60 minutos');
        }
        
        if ($status === 'agendado' && empty($data_agendamento)) {
            throw new Exception('Para status agendado, informe a data e hora do agendamento');
        }
        
        if (!empty($data_agendamento)) {
            $dataAgendamentoObj = new DateTime($data_agendamento);
            $agora = new DateTime();
            if ($dataAgendamentoObj <= $agora) {
                throw new Exception('A data de agendamento deve ser futura');
            }
        }
        
        // Verificar se já existe notícia com mesmo título
        $stmt = $conn->prepare("SELECT id FROM noticias WHERE titulo = ?");
        $stmt->execute([$titulo]);
        if ($stmt->fetch()) {
            throw new Exception('Já existe uma notícia com este título');
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
            INSERT INTO noticias (titulo, categoria, resumo, conteudo, depoimento_texto, depoimento_autor, imagem, autor, tempo_leitura, data_publicacao, ativo, destaque, ordem, status, data_agendamento)
            VALUES (:titulo, :categoria, :resumo, :conteudo, :depoimento_texto, :depoimento_autor, :imagem, :autor, :tempo_leitura, :data_publicacao, :ativo, :destaque, :ordem, :status, :data_agendamento)
        ");
        
        $stmt->bindParam(':titulo', $titulo);
        $stmt->bindParam(':categoria', $categoria);
        $stmt->bindParam(':resumo', $resumo);
        $stmt->bindParam(':conteudo', $conteudo);
        $stmt->bindParam(':depoimento_texto', $depoimento_texto);
        $stmt->bindParam(':depoimento_autor', $depoimento_autor);
        $stmt->bindParam(':imagem', $imagem);
        $stmt->bindParam(':autor', $autor);
        $stmt->bindParam(':tempo_leitura', $tempo_leitura);
        $stmt->bindParam(':data_publicacao', $data_publicacao);
        $stmt->bindParam(':ativo', $ativo);
        $stmt->bindParam(':destaque', $destaque);
        $stmt->bindParam(':ordem', $ordem);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':data_agendamento', $data_agendamento);
        
        $result = $stmt->execute();
        
        logError('Resultado do INSERT notícia', [
            'success' => $result,
            'rowCount' => $stmt->rowCount(),
            'errorInfo' => $stmt->errorInfo()
        ]);
        
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
    <link rel="icon" href="../assets/logo.ico" type="image/x-icon">
    <link rel="shortcut icon" href="../assets/logo.ico" type="image/x-icon">
    <link rel="apple-touch-icon" href="../assets/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/15d6bd6a1c.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/noticias.css">
    <style>
        /* Modal de erro personalizado */
        .error-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 10000;
            animation: fadeIn 0.2s ease;
        }
        
        .error-modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .error-content {
            background: white;
            border-radius: 12px;
            padding: 0;
            max-width: 450px;
            width: 90%;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            animation: slideDown 0.3s ease;
        }
        
        .error-header {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            padding: 24px;
            border-radius: 12px 12px 0 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .error-header i {
            font-size: 28px;
        }
        
        .error-header h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
        }
        
        .error-body {
            padding: 28px;
            color: #333;
            font-size: 15px;
            line-height: 1.6;
        }
        
        .error-footer {
            padding: 20px 28px;
            border-top: 1px solid #f0f0f0;
            text-align: right;
        }
        
        .error-btn {
            background: var(--vermelho-primario);
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .error-btn:hover {
            background: #c0392b;
            transform: translateY(-1px);
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
    <style>
        /* Modal de erro personalizado */
        .error-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 10000;
            animation: fadeIn 0.2s ease;
        }
        
        .error-modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .error-content {
            background: white;
            border-radius: 12px;
            padding: 0;
            max-width: 450px;
            width: 90%;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            animation: slideDown 0.3s ease;
        }
        
        .error-header {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            padding: 24px;
            border-radius: 12px 12px 0 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .error-header i {
            font-size: 28px;
        }
        
        .error-header h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
        }
        
        .error-body {
            padding: 28px;
            color: #333;
            font-size: 15px;
            line-height: 1.6;
        }
        
        .error-footer {
            padding: 20px 28px;
            border-top: 1px solid #f0f0f0;
            text-align: right;
        }
        
        .error-btn {
            background: var(--vermelho-primario);
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .error-btn:hover {
            background: #c0392b;
            transform: translateY(-1px);
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
    <!-- TinyMCE: Obtenha sua API key gratuita em https://www.tiny.cloud/auth/signup/ -->
    <!-- Substitua 'SUA_API_KEY_AQUI' pela sua chave real -->
    <script src="https://cdn.tiny.cloud/1/pjivdo2bif18etpq2hxcq117ejq55w9zlu2aa2u669mwgdpl/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
</head>
<body>
    <!-- Modal de Erro -->
    <div id="errorModal" class="error-modal">
        <div class="error-content">
            <div class="error-header">
                <i class="fas fa-exclamation-circle"></i>
                <h3>Erro de Validação</h3>
            </div>
            <div class="error-body" id="errorMessage"></div>
            <div class="error-footer">
                <button class="error-btn" onclick="closeErrorModal()">Entendi</button>
            </div>
        </div>
    </div>

    <div class="admin-wrapper">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <?php $pageTitle = 'Nova Notícia'; include 'includes/topbar.php'; ?>

            <div class="content">
                <div class="page-header-balanced">
                    <div class="header-left">
                        <div class="icon-wrapper">
                            <i class="fas fa-plus"></i>
                        </div>
                        <div>
                            <h1>Nova Notícia</h1>
                            <p>Criar uma nova notícia</p>
                        </div>
                    </div>
                    <a href="noticias.php" class="btn-balanced-light">
                        <i class="fas fa-arrow-left"></i>
                        <span>Voltar</span>
                    </a>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" class="form-balanced">
                    <div class="form-section">
                        <h3><i class="fas fa-file-alt"></i> Informações Básicas</h3>
                        
                        <div class="form-group">
                            <label for="titulo">Título *</label>
                            <input type="text" id="titulo" name="titulo" required value="<?= htmlspecialchars($_POST['titulo'] ?? '') ?>" placeholder="Digite o título da notícia">
                        </div>

                        <div class="form-group" style="max-width: 500px;">
                            <label for="categoria">Categoria *</label>
                            <select id="categoria" name="categoria" required>
                                <option value="">Selecione...</option>
                                <option value="Campeonatos" <?= ($_POST['categoria'] ?? '') === 'Campeonatos' ? 'selected' : '' ?>>Campeonatos</option>
                                <option value="Categorias de Base" <?= ($_POST['categoria'] ?? '') === 'Categorias de Base' ? 'selected' : '' ?>>Categorias de Base</option>
                                <option value="Infraestrutura" <?= ($_POST['categoria'] ?? '') === 'Infraestrutura' ? 'selected' : '' ?>>Infraestrutura</option>
                                <option value="Eventos" <?= ($_POST['categoria'] ?? '') === 'Eventos' ? 'selected' : '' ?>>Eventos</option>
                                <option value="Projetos Sociais" <?= ($_POST['categoria'] ?? '') === 'Projetos Sociais' ? 'selected' : '' ?>>Projetos Sociais</option>
                            </select>
                        </div>
                        
                        <div class="form-group" style="margin-top: 28px;">
                            <label for="resumo">Resumo *</label>
                            <textarea id="resumo" name="resumo" rows="3" required placeholder="Breve resumo da notícia (será exibido nos cards)"><?= htmlspecialchars($_POST['resumo'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="conteudo">Conteúdo *</label>
                            <textarea id="conteudo" name="conteudo" rows="12"><?= htmlspecialchars($_POST['conteudo'] ?? '') ?></textarea>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3><i class="fas fa-calendar-alt"></i> Publicação</h3>

                        <div class="form-grid-3">
                            <div class="form-group">
                                <label for="data_publicacao">Data de Publicação *</label>
                                <input type="date" id="data_publicacao" name="data_publicacao" required value="<?= $_POST['data_publicacao'] ?? date('Y-m-d') ?>">
                            </div>

                            <div class="form-group">
                                <label for="tempo_leitura">Tempo de Leitura (min)</label>
                                <input type="number" id="tempo_leitura" name="tempo_leitura" min="1" value="<?= $_POST['tempo_leitura'] ?? 5 ?>" placeholder="5">
                            </div>

                            <div class="form-group">
                                <label for="autor">Autor</label>
                                <input type="text" id="autor" name="autor" value="<?= htmlspecialchars($_POST['autor'] ?? $user['nome']) ?>" placeholder="Nome do autor">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Campos de Depoimento (Opcional) -->
                    <div id="campos-depoimento" class="form-section">
                        <h3><i class="fas fa-quote-left"></i> Depoimento (Opcional)</h3>
                        
                        <div class="form-group">
                            <label for="depoimento_texto">Texto do Depoimento</label>
                            <textarea id="depoimento_texto" name="depoimento_texto" rows="3" placeholder="Digite o depoimento..."><?= htmlspecialchars($_POST['depoimento_texto'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="depoimento_autor">Nome da Pessoa</label>
                            <input type="text" id="depoimento_autor" name="depoimento_autor" placeholder="Ex: João Silva" value="<?= htmlspecialchars($_POST['depoimento_autor'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-section">
                        <h3><i class="fas fa-image"></i> Imagem</h3>
                        
                        <div class="form-group">
                            <label for="imagem">Imagem da Notícia</label>
                            
                            <div class="image-upload-box">
                                <div class="image-preview" id="noImagePlaceholder">
                                    <i class="fas fa-image"></i>
                                    <p>Clique no botão abaixo para escolher uma imagem</p>
                                </div>
                                <img id="previewImagem" class="image-preview-img" style="display: none;">
                                
                                <div class="image-upload-buttons">
                                    <label for="imagem" class="btn-upload">
                                        <i class="fas fa-upload"></i> Escolher Imagem
                                    </label>
                                    <input type="file" id="imagem" name="imagem" accept="image/jpeg,image/png,image/gif,image/webp" style="display: none;">
                                    <button type="button" id="removeImageBtn" class="btn-remove" style="display: none;">
                                        <i class="fas fa-trash"></i> Remover
                                    </button>
                                </div>
                                <small class="image-help">
                                    <i class="fas fa-info-circle"></i> Formatos aceitos: JPG, PNG, GIF, WEBP (máx. 5MB)
                                </small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="autor">Autor</label>
                            <input type="text" id="autor" name="autor" value="<?= htmlspecialchars($_POST['autor'] ?? $user['nome']) ?>" placeholder="Nome do autor">
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3><i class="fas fa-cog"></i> Configurações de Publicação</h3>

                        <div class="form-group">
                            <label for="status">Status da Publicação *</label>
                            <select id="status" name="status" required>
                                <option value="publicado" <?= ($_POST['status'] ?? 'publicado') === 'publicado' ? 'selected' : '' ?>>Publicado</option>
                                <option value="rascunho" <?= ($_POST['status'] ?? '') === 'rascunho' ? 'selected' : '' ?>>Rascunho</option>
                                <option value="agendado" <?= ($_POST['status'] ?? '') === 'agendado' ? 'selected' : '' ?>>Agendado</option>
                            </select>
                        </div>

                        <div class="form-group" id="agendamento-group" style="display: none;">
                            <label for="data_agendamento">Data e Hora do Agendamento</label>
                            <input type="datetime-local" id="data_agendamento" name="data_agendamento" value="<?= $_POST['data_agendamento'] ?? '' ?>">
                        </div>

                        <div class="checkbox-wrapper">
                            <div class="checkbox-item">
                                <input type="checkbox" id="ativo" name="ativo" <?= isset($_POST['ativo']) || !$_POST ? 'checked' : '' ?>>
                                <label for="ativo">
                                    <span class="checkbox-label">Publicar notícia (ativa)</span>
                                    <span class="checkbox-help">A notícia estará visível no site</span>
                                </label>
                            </div>

                            <div class="checkbox-item">
                                <input type="checkbox" id="destaque" name="destaque" <?= isset($_POST['destaque']) ? 'checked' : '' ?>>
                                <label for="destaque">
                                    <span class="checkbox-label">Notícia em destaque</span>
                                    <span class="checkbox-help">Será exibida com mais destaque na home</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions-balanced">
                        <button type="submit" class="btn-balanced">
                            <i class="fas fa-check"></i> Criar Notícia
                        </button>
                        <a href="noticias.php" class="btn-balanced-cancel">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </main>
    </div>
    
    <script>
        console.log('Script carregado!');
        
        // Função para mostrar erro formatado
        function showError(message) {
            const modal = document.getElementById('errorModal');
            const errorMessage = document.getElementById('errorMessage');
            errorMessage.innerHTML = message;
            modal.classList.add('active');
        }
        
        function closeErrorModal() {
            const modal = document.getElementById('errorModal');
            modal.classList.remove('active');
        }
        
        // Fechar modal ao clicar fora
        document.getElementById('errorModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeErrorModal();
            }
        });
        
        // Aguardar o DOM estar pronto
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM ready!');
            
            const form = document.querySelector('form');
            console.log('Form encontrado:', form);
            
            if (!form) {
                console.error('ERRO: Formulário não encontrado!');
                return;
            }
            
            // Debug: verificar se o formulário está sendo submetido
            form.addEventListener('submit', function(e) {
                console.log('=== FORM SUBMIT TRIGGERED ===');
                
                // Sincronizar TinyMCE antes de validar
                if (typeof tinymce !== 'undefined' && tinymce.get('conteudo')) {
                    tinymce.get('conteudo').save();
                }
                
                // Validações avançadas
                const titulo = document.getElementById('titulo').value.trim();
                const categoria = document.getElementById('categoria').value;
                const resumo = document.getElementById('resumo').value.trim();
                const conteudo = document.getElementById('conteudo').value.trim();
                const tempoLeitura = parseInt(document.getElementById('tempo_leitura').value);
                const status = document.getElementById('status').value;
                const dataAgendamento = document.getElementById('data_agendamento').value;
                
                // Validar título
                if (!titulo) {
                    e.preventDefault();
                    showError('<i class="fas fa-heading"></i> <strong>Título obrigatório</strong><br>Por favor, informe o título da notícia.');
                    document.getElementById('titulo').focus();
                    return false;
                }
                
                if (titulo.length < 10) {
                    e.preventDefault();
                    showError(`<i class="fas fa-heading"></i> <strong>Título muito curto</strong><br>O título deve ter no mínimo 10 caracteres.<br><small style="color: #7f8c8d;">Você digitou ${titulo.length} caractere(s).</small>`);
                    document.getElementById('titulo').focus();
                    return false;
                }
                
                if (titulo.length > 200) {
                    e.preventDefault();
                    showError(`<i class="fas fa-heading"></i> <strong>Título muito longo</strong><br>O título deve ter no máximo 200 caracteres.<br><small style="color: #7f8c8d;">Você digitou ${titulo.length} caracteres.</small>`);
                    document.getElementById('titulo').focus();
                    return false;
                }
                
                // Validar categoria
                if (!categoria) {
                    e.preventDefault();
                    showError('<i class="fas fa-folder"></i> <strong>Categoria obrigatória</strong><br>Por favor, selecione uma categoria para a notícia.');
                    document.getElementById('categoria').focus();
                    return false;
                }
                
                // Validar resumo
                if (!resumo) {
                    e.preventDefault();
                    showError('<i class="fas fa-align-left"></i> <strong>Resumo obrigatório</strong><br>Por favor, informe um resumo para a notícia.');
                    document.getElementById('resumo').focus();
                    return false;
                }
                
                if (resumo.length > 500) {
                    e.preventDefault();
                    showError(`<i class="fas fa-align-left"></i> <strong>Resumo muito longo</strong><br>O resumo deve ter no máximo 500 caracteres.<br><small style="color: #7f8c8d;">Você digitou ${resumo.length} caracteres.</small>`);
                    document.getElementById('resumo').focus();
                    return false;
                }
                
                // Validar conteúdo
                if (!conteudo) {
                    e.preventDefault();
                    showError('<i class="fas fa-file-alt"></i> <strong>Conteúdo obrigatório</strong><br>Por favor, escreva o conteúdo da notícia.');
                    if (typeof tinymce !== 'undefined' && tinymce.get('conteudo')) {
                        tinymce.get('conteudo').focus();
                    }
                    return false;
                }
                
                if (conteudo.length < 50) {
                    e.preventDefault();
                    showError(`<i class="fas fa-file-alt"></i> <strong>Conteúdo muito curto</strong><br>O conteúdo deve ter no mínimo 50 caracteres para ser publicado.<br><small style="color: #7f8c8d;">Você digitou ${conteudo.length} caractere(s).</small>`);
                    if (typeof tinymce !== 'undefined' && tinymce.get('conteudo')) {
                        tinymce.get('conteudo').focus();
                    }
                    return false;
                }
                
                // Validar tempo de leitura
                if (isNaN(tempoLeitura) || tempoLeitura < 1 || tempoLeitura > 60) {
                    e.preventDefault();
                    showError('<i class="fas fa-clock"></i> <strong>Tempo de leitura inválido</strong><br>O tempo de leitura deve estar entre 1 e 60 minutos.');
                    document.getElementById('tempo_leitura').focus();
                    return false;
                }
                
                // Validar agendamento
                if (status === 'agendado' && !dataAgendamento) {
                    e.preventDefault();
                    showError('<i class="fas fa-calendar-alt"></i> <strong>Data de agendamento obrigatória</strong><br>Para publicações agendadas, você deve informar a data e hora.');
                    document.getElementById('data_agendamento').focus();
                    return false;
                }
                
                if (dataAgendamento) {
                    const agendamento = new Date(dataAgendamento);
                    const agora = new Date();
                    if (agendamento <= agora) {
                        e.preventDefault();
                        showError('<i class="fas fa-calendar-times"></i> <strong>Data de agendamento inválida</strong><br>A data e hora do agendamento devem ser futuras.');
                        document.getElementById('data_agendamento').focus();
                        return false;
                    }
                }
                
                console.log('✅ Todas as validações passaram!');
                console.log('Título:', titulo);
                console.log('Categoria:', categoria);
                console.log('Resumo length:', resumo.length);
                console.log('Content length:', conteudo.length);
            });
        });
        
        // Inicializar TinyMCE
        tinymce.init({
            selector: '#conteudo',
            language: 'pt_BR',
            height: 500,
            menubar: false,
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'media', 'table', 'code', 'help', 'wordcount'
            ],
            toolbar: 'undo redo | blocks | bold italic forecolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | image link | code',
            content_style: 'body { font-family: Lato, Arial, sans-serif; font-size: 14px; line-height: 1.6; }',
            images_upload_url: 'upload-imagem.php',
            automatic_uploads: true,
            file_picker_types: 'image',
            paste_data_images: true,
            branding: false,
            promotion: false,
            setup: function(editor) {
                console.log('TinyMCE setup callback');
                editor.on('init', function() {
                    console.log('TinyMCE inicializado com sucesso!');
                });
            }
        });
        
        // Preview de imagem
        const imagemInput = document.getElementById('imagem');
        const previewImagem = document.getElementById('previewImagem');
        const removeImageBtn = document.getElementById('removeImageBtn');
        const noImagePlaceholder = document.getElementById('noImagePlaceholder');
        
        imagemInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            
            if (file) {
                // Validar tamanho
                if (file.size > 5 * 1024 * 1024) {
                    alert('Arquivo muito grande! Tamanho máximo: 5MB');
                    this.value = '';
                    return;
                }
                
                // Validar tipo
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Formato inválido! Use apenas JPG, PNG, GIF ou WEBP');
                    this.value = '';
                    return;
                }
                
                // Mostrar preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImagem.src = e.target.result;
                    previewImagem.style.display = 'block';
                    noImagePlaceholder.style.display = 'none';
                    removeImageBtn.style.display = 'inline-flex';
                };
                reader.readAsDataURL(file);
            }
        });
        
        // Remover imagem
        removeImageBtn.addEventListener('click', function() {
            imagemInput.value = '';
            previewImagem.style.display = 'none';
            noImagePlaceholder.style.display = 'flex';
            this.style.display = 'none';
        });
        
        // Controle de visibilidade do campo de agendamento
        const statusSelect = document.getElementById('status');
        const agendamentoGroup = document.getElementById('agendamento-group');
        const dataAgendamento = document.getElementById('data_agendamento');
        const dataPublicacao = document.getElementById('data_publicacao');
        
        // Definir mínimo para data de agendamento (agora)
        const agora = new Date();
        const agoraISO = agora.toISOString().slice(0, 16);
        dataAgendamento.min = agoraISO;
        
        // Atualizar data de publicação quando data de agendamento mudar
        dataAgendamento.addEventListener('change', function() {
            if (this.value && statusSelect.value === 'agendado') {
                const dataAgendamentoSelecionada = new Date(this.value);
                const hoje = new Date();
                
                // Validar se a data é futura
                if (dataAgendamentoSelecionada <= hoje) {
                    showError('<i class="fas fa-calendar-times"></i> <strong>Data inválida</strong><br>A data de agendamento deve ser futura.');
                    this.value = '';
                    return;
                }
                
                // Atualizar data de publicação com a data do agendamento
                const dataFormatada = this.value.split('T')[0];
                dataPublicacao.value = dataFormatada;
                console.log('Data de publicação atualizada para:', dataFormatada);
            }
        });
        
        // Validar em tempo real durante digitação
        dataAgendamento.addEventListener('input', function() {
            if (this.value) {
                const dataAgendamentoSelecionada = new Date(this.value);
                const hoje = new Date();
                
                if (dataAgendamentoSelecionada <= hoje) {
                    this.style.borderColor = '#e74c3c';
                } else {
                    this.style.borderColor = '';
                }
            }
        });
        
        statusSelect.addEventListener('change', function() {
            if (this.value === 'agendado') {
                agendamentoGroup.style.display = 'block';
                dataAgendamento.required = true;
            } else {
                agendamentoGroup.style.display = 'none';
                dataAgendamento.required = false;
                dataAgendamento.value = '';
            }
        });
        
        // Verificar estado inicial
        if (statusSelect.value === 'agendado') {
            agendamentoGroup.style.display = 'block';
            dataAgendamento.required = true;
        }
    </script>
</body>
</html>
