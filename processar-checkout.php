<?php
session_start();
require_once 'config/security-headers.php';
require_once 'config/db.php';
require_once 'src/Security.php';

// Validar token CSRF
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Security::validateCsrfToken($_POST['csrf_token'] ?? '')) {
    die('Requisição inválida');
}

// Rate limiting
$ip = $_SERVER['REMOTE_ADDR'];
if (!Security::rateLimit($ip, 5, 60)) {
    die('Muitas requisições. Aguarde um momento.');
}

// Validar e sanitizar dados
$plano_id = Security::validateInt($_POST['plano_id'] ?? 0, 1);
$nome = Security::sanitizeString($_POST['nome'] ?? '');
$cpf = Security::sanitizeString($_POST['cpf'] ?? '');
$email = Security::sanitizeEmail($_POST['email'] ?? '');
$telefone = Security::sanitizeString($_POST['telefone'] ?? '');

// Validações
if (!$plano_id || !$nome || !$cpf || !$email || !$telefone) {
    die('Dados incompletos');
}

// Validar CPF (implementação básica)
$cpf_limpo = preg_replace('/[^0-9]/', '', $cpf);
if (strlen($cpf_limpo) !== 11) {
    die('CPF inválido');
}

// Validar email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die('E-mail inválido');
}

try {
    $pdo = getConnection();
    
    // Verificar se o plano existe e está ativo
    $stmt = $pdo->prepare("SELECT id, preco_anual FROM planos WHERE id = ? AND ativo = 1");
    $stmt->execute([$plano_id]);
    $plano = $stmt->fetch();
    
    if (!$plano) {
        die('Plano não encontrado');
    }
    
    // Inserir assinatura (você precisará criar esta tabela)
    $stmt = $pdo->prepare("
        INSERT INTO assinaturas (
            plano_id, nome, cpf, email, telefone, 
            endereco, numero, complemento, bairro, cidade, estado, cep,
            forma_pagamento, status, valor, data_criacao
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendente', ?, NOW())
    ");
    
    $stmt->execute([
        $plano_id,
        $nome,
        $cpf_limpo,
        $email,
        $telefone,
        Security::sanitizeString($_POST['endereco'] ?? ''),
        Security::sanitizeString($_POST['numero'] ?? ''),
        Security::sanitizeString($_POST['complemento'] ?? ''),
        Security::sanitizeString($_POST['bairro'] ?? ''),
        Security::sanitizeString($_POST['cidade'] ?? ''),
        Security::sanitizeString($_POST['estado'] ?? ''),
        Security::sanitizeString($_POST['cep'] ?? ''),
        Security::sanitizeString($_POST['payment_method'] ?? ''),
        $plano['preco_anual']
    ]);
    
    $assinatura_id = $pdo->lastInsertId();
    
    // Redirecionar para página de sucesso
    header('Location: obrigado.php?id=' . $assinatura_id);
    exit;
    
} catch (Exception $e) {
    error_log("Erro ao processar checkout: " . $e->getMessage());
    die('Erro ao processar sua solicitação. Tente novamente.');
}
