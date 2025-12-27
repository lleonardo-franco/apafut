<?php
require_once 'auth.php';
Auth::require();

header('Content-Type: application/json');

try {
    if (!isset($_FILES['file'])) {
        throw new Exception('Nenhum arquivo enviado');
    }
    
    $file = $_FILES['file'];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Erro ao fazer upload do arquivo');
    }
    
    // Validar tipo de arquivo
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        throw new Exception('Tipo de arquivo não permitido. Use apenas imagens JPG, PNG, GIF ou WEBP');
    }
    
    // Validar tamanho (máx 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        throw new Exception('Arquivo muito grande. Tamanho máximo: 5MB');
    }
    
    // Criar diretório se não existir
    $uploadDir = '../assets/images/noticias/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Gerar nome único
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = 'upload-' . uniqid() . '-' . time() . '.' . strtolower($extension);
    $filePath = $uploadDir . $fileName;
    
    // Mover arquivo
    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        throw new Exception('Erro ao salvar arquivo no servidor');
    }
    
    // Retornar URL da imagem
    $imageUrl = '../assets/images/noticias/' . $fileName;
    
    // Log de sucesso
    logError('Imagem carregada no editor', [
        'arquivo' => $fileName,
        'usuario' => Auth::user()['email']
    ]);
    
    echo json_encode([
        'location' => $imageUrl
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error' => $e->getMessage()
    ]);
    
    logError('Erro ao fazer upload de imagem', [
        'erro' => $e->getMessage(),
        'usuario' => Auth::user()['email'] ?? 'desconhecido'
    ]);
}
