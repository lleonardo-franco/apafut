<?php
require_once 'auth.php';
require_once '../src/Cache.php';
Auth::require();

$user = Auth::user();
$bannerId = Security::validateInt($_GET['id'] ?? 0, 1);

if (!$bannerId) {
    header('Location: banners.php');
    exit;
}

try {
    $conn = getConnection();
    
    // Buscar banner para deletar imagem
    $stmt = $conn->prepare("SELECT * FROM banners WHERE id = :id");
    $stmt->bindParam(':id', $bannerId, PDO::PARAM_INT);
    $stmt->execute();
    $banner = $stmt->fetch();
    
    if (!$banner) {
        header('Location: banners.php');
        exit;
    }
    
    // Deletar banner
    $stmt = $conn->prepare("DELETE FROM banners WHERE id = :id");
    $stmt->bindParam(':id', $bannerId, PDO::PARAM_INT);
    $stmt->execute();
    
    // Deletar imagem se não for padrão
    if ($banner['imagem'] && file_exists('../' . $banner['imagem']) && !str_contains($banner['imagem'], 'banner1.jpg') && !str_contains($banner['imagem'], 'banner2.jpg') && !str_contains($banner['imagem'], 'banner3.jpg')) {
        @unlink('../' . $banner['imagem']);
    }
    
    // Limpar cache
    Cache::delete('banners_ativos');
    
    logError('Banner excluído', [
        'id' => $bannerId,
        'titulo' => $banner['titulo'],
        'user' => $user['email']
    ]);
    
    header('Location: banners.php?success=deleted');
    exit;
    
} catch (Exception $e) {
    logError('Erro ao excluir banner', [
        'id' => $bannerId,
        'error' => $e->getMessage(),
        'user' => $user['email']
    ]);
    
    header('Location: banners.php');
    exit;
}
