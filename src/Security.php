<?php
/**
 * Classe de Segurança
 * Fornece funções para proteção contra XSS, validação de inputs e proteção CSRF
 */

class Security {
    
    /**
     * Sanitiza string para prevenir XSS
     */
    public static function sanitizeString($string) {
        if (is_null($string)) return null;
        return htmlspecialchars(trim($string), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Sanitiza HTML permitindo apenas tags seguras
     */
    public static function sanitizeHtml($html, $allowedTags = '<p><br><strong><em><ul><ol><li><a>') {
        if (is_null($html)) return null;
        return strip_tags($html, $allowedTags);
    }
    
    /**
     * Valida e sanitiza email
     */
    public static function sanitizeEmail($email) {
        $email = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : false;
    }
    
    /**
     * Valida e sanitiza URL
     */
    public static function sanitizeUrl($url) {
        $url = filter_var(trim($url), FILTER_SANITIZE_URL);
        return filter_var($url, FILTER_VALIDATE_URL) ? $url : false;
    }
    
    /**
     * Valida inteiro
     */
    public static function validateInt($value, $min = null, $max = null) {
        $value = filter_var($value, FILTER_VALIDATE_INT);
        if ($value === false) return false;
        
        if (!is_null($min) && $value < $min) return false;
        if (!is_null($max) && $value > $max) return false;
        
        return $value;
    }
    
    /**
     * Valida float/decimal
     */
    public static function validateFloat($value, $min = null, $max = null) {
        $value = filter_var($value, FILTER_VALIDATE_FLOAT);
        if ($value === false) return false;
        
        if (!is_null($min) && $value < $min) return false;
        if (!is_null($max) && $value > $max) return false;
        
        return $value;
    }
    
    /**
     * Gera token CSRF
     */
    public static function generateCsrfToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Valida token CSRF
     */
    public static function validateCsrfToken($token) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Limita taxa de requisições (rate limiting básico)
     */
    public static function rateLimit($identifier, $maxAttempts = 5, $timeWindow = 60) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $key = 'rate_limit_' . md5($identifier);
        $now = time();
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['attempts' => 1, 'start_time' => $now];
            return true;
        }
        
        $data = $_SESSION[$key];
        
        // Reset se passou o tempo
        if (($now - $data['start_time']) > $timeWindow) {
            $_SESSION[$key] = ['attempts' => 1, 'start_time' => $now];
            return true;
        }
        
        // Incrementa tentativas
        $data['attempts']++;
        $_SESSION[$key] = $data;
        
        return $data['attempts'] <= $maxAttempts;
    }
    
    /**
     * Valida senha forte
     */
    public static function validateStrongPassword($password) {
        // Mínimo 8 caracteres, 1 maiúscula, 1 minúscula, 1 número
        $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/';
        return preg_match($pattern, $password);
    }
    
    /**
     * Hash de senha seguro
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_ARGON2ID);
    }
    
    /**
     * Verifica senha hasheada
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Sanitiza nome de arquivo
     */
    public static function sanitizeFilename($filename) {
        // Remove caracteres perigosos
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
        // Remove múltiplos pontos
        $filename = preg_replace('/\.+/', '.', $filename);
        return $filename;
    }
    
    /**
     * Valida extensão de arquivo
     */
    public static function validateFileExtension($filename, $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf']) {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($ext, $allowedExtensions);
    }
    
    /**
     * Gera token aleatório seguro
     */
    public static function generateToken($length = 32) {
        return bin2hex(random_bytes($length));
    }
    
    /**
     * Previne clickjacking via header
     */
    public static function setSecurityHeaders() {
        header('X-Frame-Options: SAMEORIGIN');
        header('X-Content-Type-Options: nosniff');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Apenas HTTPS em produção
        if (isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'production') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }
}
