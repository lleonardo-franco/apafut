<?php
/**
 * Teste de renderização do sidebar
 */
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Teste Sidebar</title>
    <link rel="icon" type="image/x-icon" href="/apafut/assets/logo.ico">
    <link rel="shortcut icon" type="image/x-icon" href="/apafut/assets/logo.ico">
    <link rel="apple-touch-icon" href="/apafut/assets/logo.png">
</head>
<body>
    <h1>Teste de Renderização do Sidebar</h1>
    
    <h2>1. Include do Sidebar</h2>
    <?php
    if (file_exists('includes/sidebar.php')) {
        echo "<p style='color:green'>✓ Arquivo sidebar.php existe</p>";
        include 'includes/sidebar.php';
    } else {
        echo "<p style='color:red'>✗ Arquivo sidebar.php NÃO encontrado!</p>";
    }
    ?>
    
    <h2>2. Código fonte do arquivo</h2>
    <pre style="background:#f5f5f5; padding:10px; overflow:auto;">
<?php
$conteudo = file_get_contents('includes/sidebar.php');
echo htmlspecialchars($conteudo);
?>
    </pre>
</body>
</html>
