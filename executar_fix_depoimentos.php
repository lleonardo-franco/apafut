<?php
require_once 'config/db.php';

try {
    $conn = getConnection();
    $sql = "ALTER TABLE depoimentos MODIFY COLUMN tipo_depoimento ENUM('video_local', 'video_url', 'texto', 'video_com_texto') DEFAULT 'video_local'";
    $conn->exec($sql);
    echo "✅ Coluna tipo_depoimento atualizada com sucesso!\n";
    
    // Verificar a estrutura atualizada
    $stmt = $conn->query("DESCRIBE depoimentos");
    echo "\n📋 Estrutura da coluna tipo_depoimento:\n";
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($row['Field'] === 'tipo_depoimento') {
            echo "Campo: " . $row['Field'] . "\n";
            echo "Tipo: " . $row['Type'] . "\n";
            echo "Default: " . $row['Default'] . "\n";
        }
    }
} catch(Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
