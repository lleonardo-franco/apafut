<?php
require_once 'auth.php';
Auth::require();

$id = Security::validateInt($_GET['id'] ?? 0, 1);
if ($id === false) {
    header('Location: diretoria.php');
    exit;
}

try {
    $conn = getConnection();
    
    // Buscar membro para deletar foto
    $stmt = $conn->prepare("SELECT foto FROM diretoria WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $membro = $stmt->fetch();
    
    if ($membro) {
        // Deletar membro
        $stmt = $conn->prepare("DELETE FROM diretoria WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        // Deletar foto se existir
        if (!empty($membro['foto']) && file_exists('../' . $membro['foto'])) {
            unlink('../' . $membro['foto']);
        }
    }
    
} catch (Exception $e) {
    logError('Erro ao excluir membro da diretoria', ['error' => $e->getMessage()]);
}

header('Location: diretoria.php?success=deleted');
exit;
