<?php
// Carrega helpers e variáveis de ambiente
require_once __DIR__ . '/../src/helpers.php';
loadEnv(__DIR__ . '/../.env');

// Configurações do banco de dados a partir do .env
define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_PORT', env('DB_PORT', '3306'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));
define('DB_NAME', env('DB_NAME', 'apafut_db'));

// Criar conexão
function getConnection() {
    try {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            DB_HOST,
            DB_PORT,
            DB_NAME
        );
        
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];
        
        $conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $conn;
        
    } catch(PDOException $e) {
        // Log do erro
        logError('Erro na conexão com o banco de dados', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
        
        // Em produção, não exiba detalhes do erro
        if (env('APP_ENV') === 'production') {
            die('Erro no sistema. Por favor, tente novamente mais tarde.');
        }
        
        die("Erro na conexão: " . $e->getMessage());
    }
}
?>
