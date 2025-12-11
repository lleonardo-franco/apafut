<?php
/**
 * Funções Helper Reutilizáveis
 */

/**
 * Carrega variáveis de ambiente do arquivo .env
 */
function loadEnv($path = __DIR__ . '/../.env') {
    if (!file_exists($path)) {
        return false;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Ignora comentários
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        if (!array_key_exists($name, $_ENV)) {
            $_ENV[$name] = $value;
            putenv("$name=$value");
        }
    }
    
    return true;
}

/**
 * Obtém valor de variável de ambiente
 */
function env($key, $default = null) {
    return $_ENV[$key] ?? getenv($key) ?: $default;
}

/**
 * Formata data em português
 */
function formatarData($data, $formato = 'd/m/Y') {
    if (is_string($data)) {
        $data = new DateTime($data);
    }
    
    $meses = [
        'January' => 'Janeiro',
        'February' => 'Fevereiro',
        'March' => 'Março',
        'April' => 'Abril',
        'May' => 'Maio',
        'June' => 'Junho',
        'July' => 'Julho',
        'August' => 'Agosto',
        'September' => 'Setembro',
        'October' => 'Outubro',
        'November' => 'Novembro',
        'December' => 'Dezembro'
    ];
    
    $dias = [
        'Monday' => 'Segunda-feira',
        'Tuesday' => 'Terça-feira',
        'Wednesday' => 'Quarta-feira',
        'Thursday' => 'Quinta-feira',
        'Friday' => 'Sexta-feira',
        'Saturday' => 'Sábado',
        'Sunday' => 'Domingo'
    ];
    
    $dataFormatada = $data->format($formato);
    
    // Substitui meses e dias
    foreach ($meses as $en => $pt) {
        $dataFormatada = str_replace($en, $pt, $dataFormatada);
    }
    
    foreach ($dias as $en => $pt) {
        $dataFormatada = str_replace($en, $pt, $dataFormatada);
    }
    
    return $dataFormatada;
}

/**
 * Formata data relativa (há X dias)
 */
function dataRelativa($data) {
    if (is_string($data)) {
        $data = new DateTime($data);
    }
    
    $agora = new DateTime();
    $diff = $agora->diff($data);
    
    if ($diff->y > 0) {
        return $diff->y === 1 ? 'há 1 ano' : "há {$diff->y} anos";
    }
    
    if ($diff->m > 0) {
        return $diff->m === 1 ? 'há 1 mês' : "há {$diff->m} meses";
    }
    
    if ($diff->d > 0) {
        return $diff->d === 1 ? 'há 1 dia' : "há {$diff->d} dias";
    }
    
    if ($diff->h > 0) {
        return $diff->h === 1 ? 'há 1 hora' : "há {$diff->h} horas";
    }
    
    if ($diff->i > 0) {
        return $diff->i === 1 ? 'há 1 minuto' : "há {$diff->i} minutos";
    }
    
    return 'agora mesmo';
}

/**
 * Trunca texto com reticências
 */
function truncarTexto($texto, $limite = 150, $sufixo = '...') {
    if (mb_strlen($texto) <= $limite) {
        return $texto;
    }
    
    return mb_substr($texto, 0, $limite) . $sufixo;
}

/**
 * Remove acentos de string
 */
function removerAcentos($string) {
    $acentos = [
        'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a', 'ä' => 'a',
        'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
        'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
        'ó' => 'o', 'ò' => 'o', 'õ' => 'o', 'ô' => 'o', 'ö' => 'o',
        'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
        'ç' => 'c', 'ñ' => 'n',
        'Á' => 'A', 'À' => 'A', 'Ã' => 'A', 'Â' => 'A', 'Ä' => 'A',
        'É' => 'E', 'È' => 'E', 'Ê' => 'E', 'Ë' => 'E',
        'Í' => 'I', 'Ì' => 'I', 'Î' => 'I', 'Ï' => 'I',
        'Ó' => 'O', 'Ò' => 'O', 'Õ' => 'O', 'Ô' => 'O', 'Ö' => 'O',
        'Ú' => 'U', 'Ù' => 'U', 'Û' => 'U', 'Ü' => 'U',
        'Ç' => 'C', 'Ñ' => 'N'
    ];
    
    return strtr($string, $acentos);
}

/**
 * Gera slug para URLs
 */
function gerarSlug($texto) {
    $texto = removerAcentos($texto);
    $texto = strtolower($texto);
    $texto = preg_replace('/[^a-z0-9]+/', '-', $texto);
    $texto = trim($texto, '-');
    return $texto;
}

/**
 * Formata número de telefone brasileiro
 */
function formatarTelefone($telefone) {
    $telefone = preg_replace('/[^0-9]/', '', $telefone);
    
    if (strlen($telefone) === 11) {
        return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $telefone);
    } elseif (strlen($telefone) === 10) {
        return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $telefone);
    }
    
    return $telefone;
}

/**
 * Formata CPF
 */
function formatarCPF($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    
    if (strlen($cpf) === 11) {
        return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf);
    }
    
    return $cpf;
}

/**
 * Valida CPF
 */
function validarCPF($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    
    if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) {
        return false;
    }
    
    for ($t = 9; $t < 11; $t++) {
        for ($d = 0, $c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d) {
            return false;
        }
    }
    
    return true;
}

/**
 * Formata valor monetário
 */
function formatarMoeda($valor, $moeda = 'R$') {
    return $moeda . ' ' . number_format($valor, 2, ',', '.');
}

/**
 * Retorna URL base do site
 */
function baseUrl($path = '') {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $base = $protocol . '://' . $host;
    
    return $path ? $base . '/' . ltrim($path, '/') : $base;
}

/**
 * Redireciona para URL
 */
function redirect($url, $statusCode = 302) {
    header("Location: $url", true, $statusCode);
    exit;
}

/**
 * Retorna IP do cliente
 */
function getClientIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    return $_SERVER['REMOTE_ADDR'] ?? '';
}

/**
 * Log de erros personalizado
 */
function logError($message, $context = []) {
    $logFile = __DIR__ . '/../logs/error.log';
    $logDir = dirname($logFile);
    
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? json_encode($context) : '';
    $logMessage = "[$timestamp] $message $contextStr" . PHP_EOL;
    
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

/**
 * Resposta JSON
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Verifica se é requisição AJAX
 */
function isAjax() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Verifica se é requisição POST
 */
function isPost() {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * Verifica se é requisição GET
 */
function isGet() {
    return $_SERVER['REQUEST_METHOD'] === 'GET';
}
