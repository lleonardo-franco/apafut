<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config/db.php';
require_once '../src/Security.php';

// Define headers de segurança
Security::setSecurityHeaders();

// Verifica rate limiting
$clientIP = getClientIP();
if (!Security::rateLimit($clientIP, 30, 60)) {
    jsonResponse([
        'success' => false,
        'error' => 'Muitas requisições. Tente novamente em alguns segundos.'
    ], 429);
}

try {
    $conn = getConnection();
    
    // Verificar e validar ID
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        jsonResponse([
            'success' => false,
            'error' => 'ID da notícia não fornecido'
        ], 400);
    }
    
    $id = Security::validateInt($_GET['id'], 1);
    
    if ($id === false) {
        jsonResponse([
            'success' => false,
            'error' => 'ID inválido'
        ], 400);
    }
    
    // Buscar notícia específica
    $stmt = $conn->prepare("SELECT * FROM noticias WHERE id = :id AND ativo = 1");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $noticia = $stmt->fetch();
    
    if (!$noticia) {
        jsonResponse([
            'success' => false,
            'error' => 'Notícia não encontrada'
        ], 404);
    }
    
    // Buscar notícias relacionadas (mesma categoria, exceto a atual)
    $stmt = $conn->prepare("SELECT id, titulo, categoria, imagem, data_publicacao FROM noticias WHERE categoria = :categoria AND id != :id AND ativo = 1 ORDER BY data_publicacao DESC LIMIT 3");
    $stmt->bindParam(':categoria', $noticia['categoria'], PDO::PARAM_STR);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $relacionadas = $stmt->fetchAll();
    
    jsonResponse([
        'success' => true,
        'data' => $noticia,
        'relacionadas' => $relacionadas
    ]);
    
} catch(PDOException $e) {
    logError('Erro na API get_noticia', [
        'id' => $id ?? null,
        'error' => $e->getMessage()
    ]);
    
    jsonResponse([
        'success' => false,
        'error' => env('APP_ENV') === 'production' 
            ? 'Erro ao buscar notícia' 
            : $e->getMessage()
    ], 500);
}
?>
