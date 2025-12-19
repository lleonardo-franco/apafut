<?php
/**
 * Sistema de Modo Manutenção
 * 
 * Verifica se existe o arquivo 'maintenance.lock' na raiz
 * Se existir, redireciona para página de manutenção
 * Administradores podem acessar com senha especial
 */

// Configurações
define('MAINTENANCE_FILE', __DIR__ . '/maintenance.lock');
define('MAINTENANCE_PASSWORD', 'apafut2025admin');
define('MAINTENANCE_PAGE', '/manutencao.html');

// Paths permitidos mesmo em manutenção
$allowed_paths = [
    '/admin/',
    '/api/',
    '/assets/',
    '/manutencao.html',
    '/ativar-manutencao.php',
    '/desativar-manutencao.php'
];

/**
 * Verifica se o modo manutenção está ativo
 */
function isMaintenanceMode() {
    return file_exists(MAINTENANCE_FILE);
}

/**
 * Verifica se o usuário tem permissão para acessar
 */
function hasMaintenanceAccess() {
    // Verifica cookie de acesso
    if (isset($_COOKIE['maintenance_access'])) {
        $hash = hash('sha256', MAINTENANCE_PASSWORD . date('Y-m-d'));
        if ($_COOKIE['maintenance_access'] === $hash) {
            return true;
        }
    }
    
    // Verifica parâmetro na URL
    if (isset($_GET['access']) && $_GET['access'] === MAINTENANCE_PASSWORD) {
        // Define cookie válido por 24h
        $hash = hash('sha256', MAINTENANCE_PASSWORD . date('Y-m-d'));
        setcookie('maintenance_access', $hash, time() + 86400, '/');
        return true;
    }
    
    return false;
}

/**
 * Verifica se o path atual é permitido
 */
function isAllowedPath() {
    global $allowed_paths;
    $current_path = $_SERVER['REQUEST_URI'];
    
    foreach ($allowed_paths as $path) {
        if (strpos($current_path, $path) === 0) {
            return true;
        }
    }
    
    return false;
}

/**
 * Redireciona para página de manutenção
 */
function redirectToMaintenance() {
    if ($_SERVER['REQUEST_URI'] !== MAINTENANCE_PAGE) {
        header('Location: ' . MAINTENANCE_PAGE);
        exit;
    }
}

// Execução principal
if (isMaintenanceMode() && !hasMaintenanceAccess() && !isAllowedPath()) {
    redirectToMaintenance();
}
