<?php
require_once 'auth.php';
Auth::require();

$user = Auth::user();
$noticiaId = Security::validateInt($_GET['id'] ?? 0, 1);

if (!$noticiaId) {
    header('Location: noticias.php');
    exit;
}

try {
    $conn = getConnection();
    
    // Verificar se notícia existe
    $stmt = $conn->prepare("SELECT titulo FROM noticias WHERE id = :id");
    $stmt->bindParam(':id', $noticiaId);
    $stmt->execute();
    $noticia = $stmt->fetch();
    
    if (!$noticia) {
        header('Location: noticias.php?error=notfound');
        exit;
    }
    
    // Excluir notícia
    $stmt = $conn->prepare("DELETE FROM noticias WHERE id = :id");
    $stmt->bindParam(':id', $noticiaId);
    $stmt->execute();
    
    logError('Notícia excluída', [
        'id' => $noticiaId,
        'titulo' => $noticia['titulo'],
        'user' => $user['email']
    ]);
    
    header('Location: noticias.php?success=deleted');
    exit;
    
} catch (Exception $e) {
    logError('Erro ao excluir notícia', [
        'id' => $noticiaId,
        'error' => $e->getMessage()
    ]);
    header('Location: noticias.php?error=delete');
    exit;
}
