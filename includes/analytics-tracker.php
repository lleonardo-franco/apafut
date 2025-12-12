<?php
// Analytics Tracker - Rastreia visitas ao site
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Gera um ID de sessão único se não existir
if (!isset($_SESSION['analytics_id'])) {
    $_SESSION['analytics_id'] = bin2hex(random_bytes(16));
}

// Não rastrear se for admin
$is_admin = strpos($_SERVER['REQUEST_URI'], '/admin/') !== false;
if ($is_admin) {
    return;
}

// Obter a URL atual
$url = $_SERVER['REQUEST_URI'];
$path = parse_url($url, PHP_URL_PATH);

// Rastrear SOMENTE páginas principais (HTML/PHP)
// Ignorar todos os recursos estáticos
$extensoesIgnoradas = [
    '.jpg', '.jpeg', '.png', '.gif', '.ico', '.svg', '.webp', // Imagens
    '.css', '.js',                                              // Estilos e scripts
    '.mp4', '.webm', '.ogg', '.mp3', '.wav',                   // Mídia
    '.pdf', '.zip', '.rar',                                     // Arquivos
    '.woff', '.woff2', '.ttf', '.eot'                          // Fontes
];

foreach ($extensoesIgnoradas as $ext) {
    if (substr($path, -strlen($ext)) === $ext) {
        return; // Não rastrear recursos estáticos
    }
}

// Rastrear apenas páginas .php, .html ou raiz (/)
$ehPaginaPrincipal = (
    $path === '/' || 
    $path === '' ||
    substr($path, -4) === '.php' || 
    substr($path, -5) === '.html'
);

if (!$ehPaginaPrincipal) {
    return; // Não rastrear outros tipos de requisição
}

require_once __DIR__ . '/../config/db.php';

// Função para converter URL em título amigável
function getTituloAmigavel($url) {
    // Remover query string para análise
    $path = parse_url($url, PHP_URL_PATH) ?: '/';
    
    // Mapeamento de URLs para títulos amigáveis
    $mapeamento = [
        '/' => 'Página Inicial',
        '/index.php' => 'Página Inicial',
        '/historia.html' => 'História do Clube',
        '/noticia.php' => 'Notícia',
        '/noticia.html' => 'Notícia',
    ];
    
    // Verificar mapeamento direto
    if (isset($mapeamento[$path])) {
        return $mapeamento[$path];
    }
    
    // Verificar se é uma notícia específica
    if (strpos($path, 'noticia') !== false) {
        return 'Notícia';
    }
    
    // Para imagens e recursos, usar categoria
    if (preg_match('/\.(jpg|jpeg|png|gif|ico)$/i', $path)) {
        return 'Imagem/Recurso';
    }
    
    if (preg_match('/\.(css|js)$/i', $path)) {
        return 'Arquivo de Estilo/Script';
    }
    
    // Para outros casos, extrair nome do arquivo e limpar
    $basename = basename($path);
    
    // Remover extensões
    $basename = preg_replace('/\.(php|html)$/i', '', $basename);
    
    // Converter para título legível
    $titulo = ucwords(str_replace(['-', '_'], ' ', $basename));
    
    // Se ficou vazio, retornar "Página"
    if (empty($titulo)) {
        return 'Página Inicial';
    }
    
    return $titulo;
}

try {
    $pdo = getConnection();
    
    // Captura informações da visita
    $url = $_SERVER['REQUEST_URI'];
    $referrer = $_SERVER['HTTP_REFERER'] ?? 'Direto';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $session_id = $_SESSION['analytics_id'];
    
    // Extrai título da página com função amigável
    $titulo = getTituloAmigavel($url);
    
    // Insere no banco
    $stmt = $pdo->prepare("
        INSERT INTO analytics_pageviews 
        (url, titulo, referrer, user_agent, ip, session_id) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $url,
        $titulo,
        $referrer,
        $user_agent,
        $ip,
        $session_id
    ]);
    
} catch (Exception $e) {
    // Silenciosamente ignora erros de analytics
    error_log('Analytics Error: ' . $e->getMessage());
}
