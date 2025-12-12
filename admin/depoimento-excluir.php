<?php
require_once '../config/db.php';
require_once 'auth.php';

$id = (int)($_POST['id'] ?? 0);

if ($id > 0) {
    try {
        $pdo = getConnection();
        
        // Verificar se o depoimento existe
        $stmt = $pdo->prepare("SELECT * FROM depoimentos WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $depoimento = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($depoimento) {
            // Deletar arquivo de vídeo
            if (!empty($depoimento['video']) && file_exists('../' . $depoimento['video'])) {
                unlink('../' . $depoimento['video']);
            }
            
            // Excluir depoimento
            $stmt = $pdo->prepare("DELETE FROM depoimentos WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                header('Location: depoimentos.php?msg=deleted');
                exit;
            } else {
                header('Location: depoimentos.php?erro=deletefail');
                exit;
            }
        } else {
            header('Location: depoimentos.php?erro=notfound');
            exit;
        }
    } catch (PDOException $e) {
        error_log("Erro ao excluir depoimento: " . $e->getMessage());
        header('Location: depoimentos.php?erro=database');
        exit;
    }
} else {
    header('Location: depoimentos.php');
    exit;
}
