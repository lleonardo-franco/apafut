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
    
    // Buscar notícia
    $stmt = $conn->prepare("SELECT id, titulo, ativo FROM noticias WHERE id = :id");
    $stmt->bindParam(':id', $noticiaId);
    $stmt->execute();
    $noticia = $stmt->fetch();
    
    if (!$noticia) {
        header('Location: noticias.php?error=notfound');
        exit;
    }
    
    // Alternar status
    $novoStatus = $noticia['ativo'] ? 0 : 1;
    $stmt = $conn->prepare("UPDATE noticias SET ativo = :ativo WHERE id = :id");
    $stmt->bindParam(':ativo', $novoStatus);
    $stmt->bindParam(':id', $noticiaId);
    $stmt->execute();
    
    $statusTexto = $novoStatus ? 'ativada' : 'desativada';
    
    logError('Notícia ' . $statusTexto, [
        'id' => $noticiaId,
        'titulo' => $noticia['titulo'],
        'user' => $user['email']
    ]);
    
    header('Location: noticias.php?success=' . $statusTexto);
    exit;
    
} catch (Exception $e) {
    logError('Erro ao alternar status da notícia', [
        'id' => $noticiaId,
        'error' => $e->getMessage()
    ]);
    header('Location: noticias.php?error=toggle');
    exit;
}
