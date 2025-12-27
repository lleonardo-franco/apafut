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
        $data_publicacao = $_POST['data_publicacao'] ?? date('Y-m-d');
        $status = Security::sanitizeString($_POST['status'] ?? 'publicado');
        $data_agendamento = !empty($_POST['data_agendamento']) ? $_POST['data_agendamento'] : null;
        
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
            INSERT INTO noticias (titulo, categoria, resumo, conteudo, imagem, autor, tempo_leitura, data_publicacao, ativo, destaque, ordem, status, data_agendamento)
            VALUES (:titulo, :categoria, :resumo, :conteudo, :imagem, :autor, :tempo_leitura, :data_publicacao, :ativo, :destaque, 0, :status, :data_agendamento)
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
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':data_agendamento', $data_agendamento);
        
        $result = $stmt->execute();
        
        logError('Resultado do INSERT', [
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/15d6bd6a1c.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/noticias.css">
    <!-- TinyMCE: Obtenha sua API key gratuita em https://www.tiny.cloud/auth/signup/ -->
    <!-- Substitua 'SUA_API_KEY_AQUI' pela sua chave real -->
    <script src="https://cdn.tiny.cloud/1/pjivdo2bif18etpq2hxcq117ejq55w9zlu2aa2u669mwgdpl/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
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
                            <label for="imagem">
                                <i class="fas fa-image"></i> Imagem da Notícia
                            </label>
                            
                            <div class="image-upload-container">
                                <div class="no-image" id="noImagePlaceholder">
                                    <i class="fas fa-image"></i>
                                    <p>Nenhuma imagem selecionada</p>
                                </div>
                                <img id="previewImagem" style="display: none; max-width: 100%; max-height: 300px; border-radius: 12px; margin-bottom: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                                
                                <div class="image-upload-actions">
                                    <label for="imagem" class="btn btn-secondary">
                                        <i class="fas fa-upload"></i> Escolher imagem
                                    </label>
                                    <input type="file" id="imagem" name="imagem" accept="image/jpeg,image/png,image/gif,image/webp" style="display: none;">
                                    <button type="button" id="removeImageBtn" class="btn btn-danger" style="display: none;">
                                        <i class="fas fa-times"></i> Remover
                                    </button>
                                </div>
                                <small class="form-help">
                                    <i class="fas fa-info-circle"></i> Formatos: JPG, PNG, GIF, WEBP | Máximo: 5MB
                                </small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="autor">
                                <i class="fas fa-user-edit"></i> Autor
                            </label>
                            <input type="text" id="autor" name="autor" value="<?= htmlspecialchars($_POST['autor'] ?? $user['nome']) ?>" placeholder="Nome do autor">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="status">Status da Publicação *</label>
                            <select id="status" name="status" required>
                                <option value="publicado" <?= ($_POST['status'] ?? 'publicado') === 'publicado' ? 'selected' : '' ?>>Publicado</option>
                                <option value="rascunho" <?= ($_POST['status'] ?? '') === 'rascunho' ? 'selected' : '' ?>>Rascunho</option>
                                <option value="agendado" <?= ($_POST['status'] ?? '') === 'agendado' ? 'selected' : '' ?>>Agendado</option>
                            </select>
                        </div>

                        <div class="form-group" id="agendamento-group" style="display: none;">
                            <label for="data_agendamento">
                                <i class="far fa-calendar-alt" style="color: var(--azul-secundario);"></i> Data e Hora do Agendamento
                            </label>
                            <div style="position: relative;">
                                <input type="datetime-local" id="data_agendamento" name="data_agendamento" value="<?= $_POST['data_agendamento'] ?? '' ?>" 
                                       style="padding-left: 45px; padding-right: 15px; height: 45px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px; transition: all 0.3s;" 
                                       onmouseover="this.style.borderColor='var(--azul-secundario)'" 
                                       onmouseout="this.style.borderColor='#e0e0e0'" 
                                       onfocus="this.style.borderColor='var(--azul-secundario)'; this.style.boxShadow='0 0 0 3px rgba(0, 105, 217, 0.1)'" 
                                       onblur="this.style.borderColor='#e0e0e0'; this.style.boxShadow='none'">
                                <i class="far fa-clock" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--azul-secundario); pointer-events: none; font-size: 18px;"></i>
                            </div>
                            <small style="color: #666; font-size: 13px; margin-top: 8px; display: flex; align-items: center; gap: 6px; background: #f0f7ff; padding: 8px 12px; border-radius: 6px; border-left: 3px solid var(--azul-secundario);">
                                <i class="fas fa-info-circle" style="color: var(--azul-secundario);"></i> 
                                <span>A notícia será publicada automaticamente na data e hora definidas</span>
                            </small>
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
    
    <script>
        console.log('Script carregado!');
        
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
                console.log('Título:', document.getElementById('titulo').value);
                console.log('Categoria:', document.getElementById('categoria').value);
                console.log('Resumo:', document.getElementById('resumo').value);
                
                // Sincronizar TinyMCE
                if (typeof tinymce !== 'undefined' && tinymce.get('conteudo')) {
                    tinymce.get('conteudo').save();
                    console.log('TinyMCE synced. Content length:', document.getElementById('conteudo').value.length);
                } else {
                    console.warn('TinyMCE não inicializado ainda');
                }
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
            promotion: false
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
        
        statusSelect.addEventListener('change', function() {
            if (this.value === 'agendado') {
                agendamentoGroup.style.display = 'block';
                dataAgendamento.required = true;
            } else {
                agendamentoGroup.style.display = 'none';
                dataAgendamento.required = false;
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
