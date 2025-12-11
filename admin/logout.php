<?php
require_once 'auth.php';

// Fazer logout
Auth::logout();

// Redirecionar para página de login
header('Location: index.php');
exit;
