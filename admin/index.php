<?php
// Garante que a URL termine com /
if (substr($_SERVER['REQUEST_URI'], -1) !== '/' && !strpos($_SERVER['REQUEST_URI'], '.php')) {
    header('Location: ' . $_SERVER['REQUEST_URI'] . '/');
    exit;
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/security-headers.php';
require_once 'auth.php';

// Inicia sessão segura
Auth::startSession();

// Gera token CSRF
$csrfToken = Security::generateCsrfToken();

// Se já estiver logado, redireciona para dashboard
if (Auth::check()) {
    header('Location: dashboard.php');
    exit;
}

// Processar login
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Proteção CSRF
    if (!isset($_POST['csrf_token']) || !Security::validateCsrfToken($_POST['csrf_token'])) {
        $error = 'Token de segurança inválido. Recarregue a página e tente novamente.';
        logError('Tentativa de login sem token CSRF válido', ['ip' => getClientIP()]);
    }
    // Honeypot anti-bot (campo invisível que humanos não preenchem)
    elseif (!empty($_POST['website'])) {
        // Bot detectado - registra mas não informa
        logError('Bot detectado no login (honeypot preenchido)', ['ip' => getClientIP()]);
        sleep(2); // Delay para desestimular bots
        $error = 'Email ou senha incorretos';
    }
    // Validação de entrada
    else {
        $email = $_POST['email'] ?? '';
        $senha = $_POST['senha'] ?? '';
        
        if (empty($email) || empty($senha)) {
            $error = 'Por favor, preencha todos os campos';
        } elseif (strlen($senha) < 4) {
            $error = 'Senha muito curta';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Email inválido';
        } else {
            $result = Auth::login($email, $senha);
            
            if ($result['success']) {
                // Regenera sessão após login bem-sucedido
                session_regenerate_id(true);
                header('Location: dashboard.php');
                exit;
            } else {
                $error = $result['message'];
                // Regenera token CSRF após erro
                $csrfToken = Security::generateCsrfToken();
                // Pequeno delay para dificultar ataques de força bruta
                usleep(500000); // 0.5 segundo
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Painel Administrativo Apafut</title>
    <!-- Lato Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <!-- FontAwesome -->
    <script src="https://kit.fontawesome.com/15d6bd6a1c.js" crossorigin="anonymous"></script>
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
    <div class="login-wrapper">
        <!-- Left Side - Login Form -->
        <div class="login-left">
            <div class="login-content">
                <div class="brand-section">
                    <div class="brand-logo">
                        <img src="../assets/logo.png" alt="Logo Apafut">
                    </div>
                    <h1>APAFUT</h1>
                </div>

                <div class="welcome-section">
                    <h2>Bem-vindo de volta!</h2>
                    <p>Entre para acessar o painel administrativo e gerenciar o conteúdo do site da APAFUT.</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <span><?= htmlspecialchars($success) ?></span>
                    </div>
                <?php endif; ?>

                <form class="login-form" method="POST" action="">
                    <!-- CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    
                    <!-- Honeypot anti-bot (campo invisível) -->
                    <input type="text" name="website" style="display:none !important;" tabindex="-1" autocomplete="off">
                    
                    <div class="form-group">
                        <label for="email">E-mail</label>
                        <div class="input-wrapper">
                            <i class="fas fa-envelope"></i>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                placeholder="Digite seu e-mail"
                                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                required
                                maxlength="255"
                                autocomplete="email"
                            >
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="senha">Senha</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock"></i>
                            <input 
                                type="password" 
                                id="senha" 
                                name="senha" 
                                placeholder="Digite sua senha"
                                required
                                maxlength="255"
                                autocomplete="current-password"
                            >
                            <button type="button" class="toggle-password" onclick="togglePassword()" aria-label="Mostrar/ocultar senha">
                                <i class="fas fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-signin">
                        <span>Entrar</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </form>
            </div>
        </div>

        <!-- Right Side - Visual Panel -->
        <div class="login-right">
            <div class="right-content">
                <h2>Formando Campeões<br>Dentro e Fora do Campo</h2>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        function togglePassword() {
            const passwordInput = document.getElementById('senha');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Previne múltiplos submits e adiciona loading
        let formSubmitted = false;
        document.querySelector('.login-form').addEventListener('submit', function(e) {
            if (formSubmitted) {
                e.preventDefault();
                return false;
            }
            
            formSubmitted = true;
            const btn = this.querySelector('.btn-signin');
            btn.classList.add('loading');
            btn.disabled = true;
        });
        
        // Limpa honeypot se JavaScript estiver habilitado (bots geralmente não executam JS)
        document.querySelector('input[name="website"]').value = '';
    </script>
</body>
</html>
