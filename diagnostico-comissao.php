<?php
/**
 * SCRIPT DE DIAGNÓSTICO - COMISSÃO TÉCNICA
 * Execute este arquivo no servidor para verificar o que está funcionando
 */

header('Content-Type: text/html; charset=UTF-8');
echo "<h1>Diagnóstico - Sistema de Comissão Técnica</h1>";
echo "<style>body{font-family:Arial;padding:20px;} .ok{color:green;} .erro{color:red;} .aviso{color:orange;} pre{background:#f5f5f5;padding:10px;border-radius:4px;}</style>";

// 1. Verificar conexão com banco
echo "<h2>1. Verificando Conexão com Banco de Dados</h2>";
try {
    require_once 'config/db.php';
    $conn = getConnection();
    echo "✓ <span class='ok'>Conectado ao banco de dados com sucesso</span><br>";
} catch (Exception $e) {
    echo "✗ <span class='erro'>Erro ao conectar: " . $e->getMessage() . "</span><br>";
    die();
}

// 2. Verificar se a tabela existe
echo "<h2>2. Verificando Tabela comissao_tecnica</h2>";
try {
    $stmt = $conn->query("SHOW TABLES LIKE 'comissao_tecnica'");
    if ($stmt->rowCount() > 0) {
        echo "✓ <span class='ok'>Tabela 'comissao_tecnica' existe</span><br>";
        
        // Mostrar estrutura
        $stmt = $conn->query("DESCRIBE comissao_tecnica");
        $campos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<details><summary>Estrutura da tabela</summary><pre>";
        print_r($campos);
        echo "</pre></details>";
    } else {
        echo "✗ <span class='erro'>Tabela 'comissao_tecnica' NÃO existe!</span><br>";
        echo "<p><strong>SOLUÇÃO:</strong> Execute o arquivo sql/create_comissao_table.sql no phpMyAdmin</p>";
    }
} catch (Exception $e) {
    echo "✗ <span class='erro'>Erro ao verificar tabela: " . $e->getMessage() . "</span><br>";
}

// 3. Verificar dados na tabela
echo "<h2>3. Verificando Dados na Tabela</h2>";
try {
    $stmt = $conn->query("SELECT COUNT(*) as total FROM comissao_tecnica");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $total = $row['total'];
    
    if ($total > 0) {
        echo "✓ <span class='ok'>Existem {$total} registros na tabela</span><br>";
        
        $stmt = $conn->query("SELECT * FROM comissao_tecnica ORDER BY ordem ASC");
        $membros = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<details><summary>Ver todos os registros</summary><pre>";
        print_r($membros);
        echo "</pre></details>";
    } else {
        echo "✗ <span class='aviso'>A tabela está VAZIA!</span><br>";
        echo "<p><strong>SOLUÇÃO:</strong> Adicione membros pelo painel admin ou execute os INSERTs do SQL</p>";
    }
} catch (Exception $e) {
    echo "✗ <span class='erro'>Erro ao buscar dados: " . $e->getMessage() . "</span><br>";
}

// 4. Verificar membros ativos
echo "<h2>4. Verificando Membros Ativos</h2>";
try {
    $stmt = $conn->query("SELECT COUNT(*) as total FROM comissao_tecnica WHERE ativo = 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalAtivos = $row['total'];
    
    if ($totalAtivos > 0) {
        echo "✓ <span class='ok'>Existem {$totalAtivos} membros ATIVOS</span><br>";
    } else {
        echo "✗ <span class='aviso'>NÃO há membros ativos!</span><br>";
        echo "<p><strong>SOLUÇÃO:</strong> Ative pelo menos um membro no painel admin</p>";
    }
} catch (Exception $e) {
    echo "✗ <span class='erro'>Erro: " . $e->getMessage() . "</span><br>";
}

// 5. Verificar arquivos PHP
echo "<h2>5. Verificando Arquivos do Sistema</h2>";
$arquivos = [
    'admin/comissao.php' => 'Listagem admin',
    'admin/comissao-criar.php' => 'Criar membro admin',
    'admin/comissao-editar.php' => 'Editar membro admin',
    'admin/comissao-excluir.php' => 'Excluir membro admin',
    'api/get_comissao.php' => 'API comissão',
    'assets/images/comissao/' => 'Pasta de fotos'
];

foreach ($arquivos as $arquivo => $descricao) {
    if (file_exists($arquivo)) {
        echo "✓ <span class='ok'>{$descricao}: {$arquivo}</span><br>";
    } else {
        echo "✗ <span class='erro'>{$descricao}: {$arquivo} NÃO encontrado!</span><br>";
    }
}

// 6. Verificar sidebar
echo "<h2>6. Verificando Menu Lateral (Sidebar)</h2>";
if (file_exists('admin/includes/sidebar.php')) {
    $conteudo = file_get_contents('admin/includes/sidebar.php');
    if (strpos($conteudo, 'comissao.php') !== false) {
        echo "✓ <span class='ok'>Link 'Comissão Técnica' encontrado no sidebar</span><br>";
        
        // Verificar encoding
        if (strpos($conteudo, 'Comissão') !== false || strpos($conteudo, 'Comiss') !== false) {
            echo "✓ <span class='ok'>Texto 'Comissão' presente no arquivo</span><br>";
        } else {
            echo "✗ <span class='erro'>Texto 'Comissão' NÃO encontrado (problema de encoding?)</span><br>";
        }
    } else {
        echo "✗ <span class='erro'>Link para comissao.php NÃO encontrado no sidebar</span><br>";
        echo "<p><strong>SOLUÇÃO:</strong> Adicione o link manualmente no arquivo admin/includes/sidebar.php</p>";
    }
} else {
    echo "✗ <span class='erro'>Arquivo sidebar.php não encontrado!</span><br>";
}

// 7. Testar API
echo "<h2>7. Testando API get_comissao.php</h2>";
try {
    $stmt = $conn->prepare("SELECT * FROM comissao_tecnica WHERE ativo = 1 ORDER BY ordem ASC, id ASC");
    $stmt->execute();
    $comissao = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($comissao) > 0) {
        echo "✓ <span class='ok'>API retornaria " . count($comissao) . " membros</span><br>";
        echo "<details><summary>Ver dados da API</summary><pre>";
        echo json_encode($comissao, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        echo "</pre></details>";
    } else {
        echo "✗ <span class='aviso'>API não retornaria nenhum membro</span><br>";
    }
} catch (Exception $e) {
    echo "✗ <span class='erro'>Erro na API: " . $e->getMessage() . "</span><br>";
}

// 8. Verificar cache
echo "<h2>8. Cache</h2>";
if (is_dir('cache')) {
    echo "✓ <span class='ok'>Pasta cache/ existe</span><br>";
    echo "<p><strong>Recomendação:</strong> Limpe o cache executando:</p>";
    echo "<pre>rm -rf cache/*</pre>";
    echo "<p>Ou delete manualmente os arquivos na pasta cache/</p>";
} else {
    echo "✗ <span class='aviso'>Pasta cache/ não encontrada</span><br>";
}

// 9. Resumo e ações
echo "<h2>9. RESUMO E PRÓXIMOS PASSOS</h2>";
echo "<ol>";
echo "<li>Se a tabela não existe: Execute o SQL no phpMyAdmin</li>";
echo "<li>Se não há dados: Adicione membros pelo painel admin em admin/comissao.php</li>";
echo "<li>Se o link não aparece no menu: Limpe o cache do navegador (Ctrl+Shift+R)</li>";
echo "<li>Se os cards não aparecem: Limpe o cache do servidor (delete arquivos em cache/)</li>";
echo "<li>Execute a minificação: php minify-assets.php</li>";
echo "</ol>";

echo "<hr>";
echo "<p><strong>Data do diagnóstico:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>
