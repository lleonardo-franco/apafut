<?php
class BotProtection {
    private static $blockedUserAgents = [
        'sqlmap', 'nikto', 'nmap', 'masscan', 'havij',
        'acunetix', 'grabber', 'loader', 'miner'
    ];
    
    private static $legitimateBots = [
        'Googlebot', 'Bingbot', 'Slurp', 'DuckDuckBot',
        'Baiduspider', 'YandexBot', 'facebookexternalhit',
        'Twitterbot', 'LinkedInBot', 'WhatsApp'
    ];
    
    public static function check() {
        // Verifica User-Agent
        if (!self::checkUserAgent()) {
            return false;
        }
        
        // Verifica honeypot em POST
        if (!self::checkHoneypot()) {
            return false;
        }
        
        // Verifica comportamento suspeito
        if (!self::checkBehavior()) {
            return false;
        }
        
        return true;
    }
    
    private static function checkUserAgent() {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        if (empty($userAgent)) {
            self::blockAccess('Empty User-Agent');
            return false;
        }
        
        // Verifica User-Agents bloqueados
        foreach (self::$blockedUserAgents as $bot) {
            if (stripos($userAgent, $bot) !== false) {
                self::blockAccess('Blocked User-Agent: ' . $bot);
                return false;
            }
        }
        
        // Permite bots legítimos
        foreach (self::$legitimateBots as $bot) {
            if (stripos($userAgent, $bot) !== false) {
                return true;
            }
        }
        
        return true;
    }
    
    private static function checkHoneypot() {
        // Honeypot fields (campos invisíveis para humanos)
        $honeypotFields = ['website', 'url', 'link', 'homepage'];
        
        foreach ($honeypotFields as $field) {
            if (!empty($_POST[$field])) {
                self::blockAccess('Honeypot triggered: ' . $field);
                return false;
            }
        }
        
        return true;
    }
    
    private static function checkBehavior() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $now = time();
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        // Verifica requisições muito rápidas
        if (isset($_SESSION['last_request'])) {
            $timeDiff = $now - $_SESSION['last_request'];
            
            if ($timeDiff < 1) { // Menos de 1 segundo
                $_SESSION['suspicious_count'] = ($_SESSION['suspicious_count'] ?? 0) + 1;
                
                if ($_SESSION['suspicious_count'] > 10) {
                    self::blockAccess('Too many fast requests');
                    return false;
                }
            } else {
                $_SESSION['suspicious_count'] = max(0, ($_SESSION['suspicious_count'] ?? 0) - 1);
            }
        }
        
        $_SESSION['last_request'] = $now;
        
        // Verifica tentativas de SQL Injection
        $suspicious = ['union', 'select', 'drop', 'insert', 'update', 'delete', '--', '/*', '*/', 'xp_', 'sp_'];
        $queryString = strtolower($_SERVER['QUERY_STRING'] ?? '');
        
        foreach ($suspicious as $keyword) {
            if (strpos($queryString, $keyword) !== false) {
                self::blockAccess('SQL Injection attempt');
                return false;
            }
        }
        
        return true;
    }
    
    private static function blockAccess($reason) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $uri = $_SERVER['REQUEST_URI'] ?? 'unknown';
        
        error_log("BOT BLOCKED - Reason: $reason | IP: $ip | UA: $ua | URI: $uri");
        
        http_response_code(403);
        die('Access Denied');
    }
    
    public static function renderHoneypot() {
        return '<input type="text" name="website" value="" style="position:absolute;left:-9999px;width:1px;height:1px" tabindex="-1" autocomplete="off" aria-hidden="true">';
    }
}
