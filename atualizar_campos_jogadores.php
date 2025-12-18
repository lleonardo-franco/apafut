<?php
require_once 'config/db.php';

try {
    $conn = getConnection();
    
    echo "=== ATUALIZAÇÃO DOS CAMPOS DE JOGADORES ===\n\n";
    
    // Adicionar campos se não existirem
    echo "1. Adicionando novos campos...\n";
    
    $campos = [
        "ADD COLUMN nome_completo VARCHAR(255) AFTER nome",
        "ADD COLUMN cidade VARCHAR(100) AFTER nome_completo",
        "ADD COLUMN altura VARCHAR(20) AFTER cidade",
        "ADD COLUMN peso VARCHAR(20) AFTER altura",
        "ADD COLUMN data_nascimento VARCHAR(50) AFTER peso",
        "ADD COLUMN carreira TEXT AFTER data_nascimento"
    ];
    
    foreach ($campos as $campo) {
        try {
            $conn->exec("ALTER TABLE jogadores $campo");
            echo "   ✓ Campo adicionado: $campo\n";
        } catch (PDOException $e) {
            // Campo já existe
            if (strpos($e->getMessage(), 'Duplicate column') !== false) {
                echo "   - Campo já existe: $campo\n";
            } else {
                throw $e;
            }
        }
    }
    
    echo "\n2. Preenchendo valores padrão...\n";
    $stmt = $conn->exec("
        UPDATE jogadores 
        SET nome_completo = COALESCE(nome_completo, nome),
            cidade = COALESCE(cidade, 'Caxias do Sul (RS)'),
            altura = COALESCE(altura, '1.75m'),
            peso = COALESCE(peso, '70kg'),
            data_nascimento = COALESCE(data_nascimento, '01/01/2000'),
            carreira = COALESCE(carreira, 'Em formação pela Apafut')
        WHERE altura IS NULL OR altura = ''
    ");
    
    echo "   ✓ $stmt registros atualizados\n\n";
    
    echo "=== ATUALIZAÇÃO CONCLUÍDA COM SUCESSO! ===\n";
    echo "Agora você pode editar os jogadores no painel admin.\n";
    
} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
    exit(1);
}
