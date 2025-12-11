<?php
/**
 * Classe de Autenticação para Painel Administrativo
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../src/Security.php';

class Auth {
    
    /**
     * Inicia sessão segura
     */
    public static function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            // Configurações de sessão segura
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_only_cookies', 1);
            ini_set('session.cookie_secure', isset($_SERVER['HTTPS'])); // HTTPS apenas em produção
            
            session_name('APAFUT_ADMIN_SESSION');
            session_start();
            
            // Regenera ID da sessão periodicamente
            if (!isset($_SESSION['created'])) {
                $_SESSION['created'] = time();
            } else if (time() - $_SESSION['created'] > 1800) { // 30 minutos
                session_regenerate_id(true);
                $_SESSION['created'] = time();
            }
        }
    }
    
    /**
     * Faz login do usuário
     */
    public static function login($email, $senha) {
        try {
            // Validar inputs
            $email = Security::sanitizeEmail($email);
            if (!$email) {
                return ['success' => false, 'message' => 'Email inválido'];
            }
            
            // Rate limiting
            $clientIP = getClientIP();
            if (!Security::rateLimit('login_' . $clientIP, 5, 300)) {
                return ['success' => false, 'message' => 'Muitas tentativas de login. Tente novamente em 5 minutos.'];
            }
            
            $conn = getConnection();
            
            // Buscar usuário
            $stmt = $conn->prepare("SELECT * FROM usuarios_admin WHERE email = :email AND ativo = 1");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $usuario = $stmt->fetch();
            
            if (!$usuario) {
                logError('Tentativa de login com email inexistente', ['email' => $email, 'ip' => $clientIP]);
                return ['success' => false, 'message' => 'Email ou senha incorretos'];
            }
            
            // Verificar senha
            if (!Security::verifyPassword($senha, $usuario['senha'])) {
                logError('Tentativa de login com senha incorreta', ['email' => $email, 'ip' => $clientIP]);
                return ['success' => false, 'message' => 'Email ou senha incorretos'];
            }
            
            // Login bem-sucedido
            self::startSession();
            
            // Armazenar dados na sessão
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_user_id'] = $usuario['id'];
            $_SESSION['admin_user_name'] = $usuario['nome'];
            $_SESSION['admin_user_email'] = $usuario['email'];
            $_SESSION['admin_user_level'] = $usuario['nivel_acesso'];
            $_SESSION['admin_login_time'] = time();
            
            // Atualizar último acesso
            $stmt = $conn->prepare("UPDATE usuarios_admin SET ultimo_acesso = NOW() WHERE id = :id");
            $stmt->bindParam(':id', $usuario['id']);
            $stmt->execute();
            
            // Log de sucesso
            logError('Login administrativo bem-sucedido', [
                'user_id' => $usuario['id'],
                'email' => $email,
                'ip' => $clientIP
            ]);
            
            return ['success' => true, 'message' => 'Login realizado com sucesso'];
            
        } catch (Exception $e) {
            logError('Erro no processo de login', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Erro ao processar login'];
        }
    }
    
    /**
     * Verifica se usuário está logado
     */
    public static function check() {
        self::startSession();
        
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            return false;
        }
        
        // Verificar timeout de sessão (2 horas)
        if (isset($_SESSION['admin_login_time'])) {
            $sessionLifetime = env('SESSION_LIFETIME', 7200);
            if (time() - $_SESSION['admin_login_time'] > $sessionLifetime) {
                self::logout();
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Requer autenticação (redireciona se não estiver logado)
     */
    public static function require() {
        if (!self::check()) {
            header('Location: /admin/index.php');
            exit;
        }
    }
    
    /**
     * Faz logout do usuário
     */
    public static function logout() {
        self::startSession();
        
        // Log de logout
        if (isset($_SESSION['admin_user_email'])) {
            logError('Logout administrativo', [
                'email' => $_SESSION['admin_user_email'],
                'ip' => getClientIP()
            ]);
        }
        
        // Limpar sessão
        $_SESSION = [];
        
        // Destruir cookie de sessão
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        // Destruir sessão
        session_destroy();
    }
    
    /**
     * Obtém dados do usuário logado
     */
    public static function user() {
        self::startSession();
        
        if (!self::check()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['admin_user_id'] ?? null,
            'nome' => $_SESSION['admin_user_name'] ?? null,
            'email' => $_SESSION['admin_user_email'] ?? null,
            'nivel_acesso' => $_SESSION['admin_user_level'] ?? null
        ];
    }
    
    /**
     * Verifica se usuário é admin
     */
    public static function isAdmin() {
        $user = self::user();
        return $user && $user['nivel_acesso'] === 'admin';
    }
    
    /**
     * Obtém iniciais do nome
     */
    public static function getInitials($nome) {
        $palavras = explode(' ', $nome);
        if (count($palavras) >= 2) {
            return strtoupper(substr($palavras[0], 0, 1) . substr($palavras[1], 0, 1));
        }
        return strtoupper(substr($nome, 0, 2));
    }
}
