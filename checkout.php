<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');
mb_internal_encoding('UTF-8');
require_once 'config/security-headers.php';
require_once 'config/db.php';
require_once 'src/Security.php';

// Gerar token CSRF
$csrf_token = Security::generateCsrfToken();

// Validar ID do plano
$plano_id = Security::validateInt($_GET['plano'] ?? 0, 1);
$plano = null;

if ($plano_id) {
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare("SELECT id, nome, tipo, preco_anual, parcelas, beneficios, destaque FROM planos WHERE id = ? AND ativo = 1");
        $stmt->execute([$plano_id]);
        $plano = $stmt->fetch();
    } catch (Exception $e) {
        error_log("Erro ao buscar plano: " . $e->getMessage());
    }
}

if (!$plano) {
    header('Location: index.php#planos');
    exit;
}

// Formatar preço
$preco_formatado = 'R$ ' . number_format($plano['preco_anual'], 2, ',', '.');
$beneficios = array_filter(array_map('trim', explode('|', $plano['beneficios'])));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - <?= htmlspecialchars($plano['nome']) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --vermelho-primario: #eb3835;
            --azul-secundario: #111D69;
            --branco-padrao: #FFFFFF;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Lato', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, var(--azul-secundario) 0%, #1a2b8f 50%, var(--vermelho-primario) 100%);
            min-height: 100vh;
            padding: 20px 20px 40px 20px;
        }

        .back-to-site {
            max-width: 1200px;
            margin: 0 auto 20px auto;
        }

        .back-to-site a {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: white;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            padding: 10px 18px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 8px;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .back-to-site a:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateX(-4px);
        }

        .checkout-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 30px;
            align-items: start;
        }

        .checkout-main {
            background: white;
            border-radius: 20px;
            box-shadow: 0 25px 70px rgba(0, 0, 0, 0.4);
            overflow: hidden;
        }

        .checkout-header {
            background: linear-gradient(135deg, var(--azul-secundario) 0%, #1a2b8f 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .checkout-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: var(--vermelho-primario);
            border-radius: 50%;
            opacity: 0.1;
        }

        .checkout-header::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -5%;
            width: 200px;
            height: 200px;
            background: var(--vermelho-primario);
            border-radius: 50%;
            opacity: 0.15;
        }

        .checkout-header h1 {
            font-size: 32px;
            margin-bottom: 10px;
            font-weight: 700;
            position: relative;
            z-index: 1;
        }

        .checkout-header p {
            opacity: 0.95;
            font-size: 16px;
            position: relative;
            z-index: 1;
        }

        /* Progress Steps */
        .progress-steps {
            display: flex;
            justify-content: space-between;
            padding: 45px 60px;
            background: linear-gradient(to bottom, #f8f9fa, #ffffff);
            position: relative;
        }

        .progress-steps::before {
            content: '';
            position: absolute;
            top: 65px;
            left: 60px;
            right: 60px;
            height: 4px;
            background: linear-gradient(to right, #e8e8e8, #f0f0f0);
            z-index: 0;
            border-radius: 2px;
        }

        .progress-line {
            position: absolute;
            top: 65px;
            left: 60px;
            height: 4px;
            background: linear-gradient(90deg, var(--azul-secundario), var(--vermelho-primario));
            transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1;
            border-radius: 2px;
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 2;
        }

        .step-circle {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: white;
            border: 4px solid #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: #999;
            margin-bottom: 12px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .step.active .step-circle {
            background: linear-gradient(135deg, var(--azul-secundario), #1a2b8f);
            border-color: var(--azul-secundario);
            color: white;
            transform: scale(1.15);
            box-shadow: 0 4px 16px rgba(17, 29, 105, 0.4);
        }

        .step.completed .step-circle {
            background: linear-gradient(135deg, var(--vermelho-primario), #d32f2c);
            border-color: var(--vermelho-primario);
            color: white;
            box-shadow: 0 4px 12px rgba(235, 56, 53, 0.3);
        }

        .step-label {
            font-size: 14px;
            color: #666;
            font-weight: 500;
            text-align: center;
        }

        .step.active .step-label {
            color: var(--azul-secundario);
            font-weight: 700;
        }

        .step.completed .step-label {
            color: var(--vermelho-primario);
            font-weight: 600;
        }

        /* Form Content */
        .form-content {
            padding: 40px 60px;
        }

        .form-step {
            display: none;
        }

        .form-step.active {
            display: block;
            animation: fadeIn 0.4s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(15px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-step h2 {
            font-size: 26px;
            color: var(--azul-secundario);
            margin-bottom: 10px;
            font-weight: 700;
        }

        .form-step p {
            color: #666;
            margin-bottom: 35px;
            font-size: 15px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: var(--azul-secundario);
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s ease;
            font-family: inherit;
            background: white;
        }

        .form-group input:hover,
        .form-group select:hover {
            border-color: #ccc;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--azul-secundario);
            box-shadow: 0 0 0 4px rgba(17, 29, 105, 0.1);
            transform: translateY(-1px);
        }

        .form-group input.error {
            border-color: var(--vermelho-primario);
            animation: shake 0.4s ease;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-8px); }
            75% { transform: translateX(8px); }
        }

        .error-message {
            color: var(--vermelho-primario);
            font-size: 13px;
            margin-top: 5px;
            display: none;
        }

        /* Payment Methods */
        .payment-methods {
            display: grid;
            gap: 16px;
            margin-bottom: 30px;
        }

        .payment-method {
            border: 3px solid #e8e8e8;
            border-radius: 14px;
            padding: 22px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 18px;
            background: white;
        }

        .payment-method:hover {
            border-color: var(--azul-secundario);
            background: linear-gradient(to right, rgba(17, 29, 105, 0.02), rgba(235, 56, 53, 0.02));
            transform: translateX(4px);
        }

        .payment-method.selected {
            border-color: var(--vermelho-primario);
            background: linear-gradient(to right, rgba(17, 29, 105, 0.05), rgba(235, 56, 53, 0.05));
            box-shadow: 0 4px 20px rgba(235, 56, 53, 0.15);
        }

        .payment-method input[type="radio"] {
            width: 22px;
            height: 22px;
            accent-color: var(--vermelho-primario);
        }

        .payment-icon {
            width: 54px;
            height: 54px;
            background: linear-gradient(135deg, var(--azul-secundario), #1a2b8f);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 26px;
            box-shadow: 0 4px 12px rgba(17, 29, 105, 0.25);
        }

        .payment-method.selected .payment-icon {
            background: linear-gradient(135deg, var(--vermelho-primario), #d32f2c);
            box-shadow: 0 4px 12px rgba(235, 56, 53, 0.35);
        }

        .payment-info h3 {
            font-size: 17px;
            color: var(--azul-secundario);
            margin-bottom: 5px;
            font-weight: 700;
        }

        .payment-info p {
            font-size: 13px;
            color: #666;
            margin: 0;
        }

        /* Navigation Buttons */
        .form-navigation {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid #f0f0f0;
        }

        .btn {
            padding: 16px 36px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-back {
            background: #f5f5f5;
            color: #666;
            border: 2px solid #e0e0e0;
        }

        .btn-back:hover {
            background: #e8e8e8;
            border-color: #ccc;
        }

        .btn-next {
            background: linear-gradient(135deg, var(--vermelho-primario), #d32f2c);
            color: white;
            margin-left: auto;
            box-shadow: 0 4px 16px rgba(235, 56, 53, 0.3);
        }

        .btn-next:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(235, 56, 53, 0.45);
        }

        .btn-next:active {
            transform: translateY(-1px);
        }

        /* Order Summary */
        .order-summary {
            background: white;
            border-radius: 20px;
            padding: 32px;
            box-shadow: 0 25px 70px rgba(0, 0, 0, 0.4);
            position: sticky;
            top: 20px;
            border: 2px solid rgba(17, 29, 105, 0.1);
        }

        .order-summary h3 {
            font-size: 22px;
            color: var(--azul-secundario);
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 700;
        }

        .plan-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 24px;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .plan-badge.prata {
            background: linear-gradient(135deg, #8e9eab 0%, #c8d0d8 100%);
            color: #333;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        .plan-badge.ouro {
            background: linear-gradient(135deg, #f7971e 0%, #ffd200 100%);
            color: #fff;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
            box-shadow: 0 2px 8px rgba(247, 151, 30, 0.35);
        }

        .plan-badge.diamante {
            background: linear-gradient(135deg, var(--azul-secundario), #1a2b8f);
            color: #fff;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
            box-shadow: 0 2px 8px rgba(17, 29, 105, 0.35);
        }

        .plan-name {
            font-size: 26px;
            color: var(--azul-secundario);
            margin: 18px 0;
            font-weight: 700;
        }

        .plan-price {
            font-size: 40px;
            color: var(--vermelho-primario);
            font-weight: 800;
            margin-bottom: 12px;
        }

        .plan-period {
            color: #777;
            font-size: 15px;
            margin-bottom: 26px;
            display: block;
            font-weight: 500;
        }

        .benefits-list {
            list-style: none;
            margin-bottom: 28px;
            padding: 20px;
            background: linear-gradient(to bottom, rgba(17, 29, 105, 0.02), rgba(235, 56, 53, 0.02));
            border-radius: 12px;
        }

        .benefits-list li {
            padding: 12px 0;
            color: #555;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .benefits-list li:last-child {
            border-bottom: none;
        }

        .benefits-list li i {
            color: var(--vermelho-primario);
            font-size: 18px;
            flex-shrink: 0;
        }

        .summary-divider {
            height: 2px;
            background: linear-gradient(to right, var(--azul-secundario), var(--vermelho-primario));
            margin: 28px 0;
            opacity: 0.2;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 14px;
            color: #666;
            font-size: 15px;
        }

        .summary-total {
            display: flex;
            justify-content: space-between;
            font-size: 24px;
            font-weight: 800;
            color: var(--azul-secundario);
            margin-top: 20px;
            padding: 20px;
            background: linear-gradient(135deg, rgba(17, 29, 105, 0.05), rgba(235, 56, 53, 0.05));
            border-radius: 12px;
        }

        .summary-total span:last-child {
            color: var(--vermelho-primario);
        }

        .security-badge {
            background: linear-gradient(135deg, var(--azul-secundario), #1a2b8f);
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            margin-top: 24px;
            box-shadow: 0 4px 12px rgba(17, 29, 105, 0.2);
        }

        .security-badge i {
            color: var(--branco-padrao);
            font-size: 28px;
            margin-bottom: 10px;
            display: block;
        }

        .security-badge p {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.9);
            margin: 0;
            font-weight: 600;
        }

        @media (max-width: 1024px) {
            .checkout-container {
                grid-template-columns: 1fr;
            }

            .order-summary {
                position: static;
            }

            .progress-steps {
                padding: 30px 20px;
            }

            .progress-steps::before {
                left: 20px;
                right: 20px;
            }

            .progress-line {
                left: 20px;
            }

            .form-content {
                padding: 30px 20px;
            }
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            .step-label {
                font-size: 12px;
            }

            .step-circle {
                width: 40px;
                height: 40px;
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="back-to-site">
        <a href="index.php">
            <i class="fas fa-arrow-left"></i>
            Voltar ao Site
        </a>
    </div>
    
    <div class="checkout-container">
        <!-- Main Checkout Form -->
        <div class="checkout-main">
            <div class="checkout-header">
                <h1><i class="fas fa-shopping-cart"></i> Finalizar Assinatura</h1>
                <p>Complete seus dados para se tornar um sócio da APAFUT</p>
            </div>

            <!-- Progress Steps -->
            <div class="progress-steps">
                <div class="progress-line" id="progressLine" style="width: 0%"></div>
                
                <div class="step active" data-step="1">
                    <div class="step-circle">
                        <i class="fas fa-user"></i>
                    </div>
                    <span class="step-label">Dados Pessoais</span>
                </div>

                <div class="step" data-step="2">
                    <div class="step-circle">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <span class="step-label">Endereço</span>
                </div>

                <div class="step" data-step="3">
                    <div class="step-circle">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <span class="step-label">Pagamento</span>
                </div>

                <div class="step" data-step="4">
                    <div class="step-circle">
                        <i class="fas fa-check"></i>
                    </div>
                    <span class="step-label">Confirmação</span>
                </div>
            </div>

            <!-- Form Steps -->
            <form id="checkoutForm" class="form-content" method="POST" action="processar-checkout.php">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <input type="hidden" name="plano_id" value="<?= htmlspecialchars($plano['id']) ?>">
                
                <!-- Step 1: Personal Information -->
                <div class="form-step active" data-step="1">
                    <h2>Dados Pessoais</h2>
                    <p>Informe seus dados para prosseguir com a assinatura</p>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="nome">Nome Completo *</label>
                            <input type="text" id="nome" name="nome" required>
                            <span class="error-message">Por favor, informe seu nome completo</span>
                        </div>

                        <div class="form-group">
                            <label for="cpf">CPF *</label>
                            <input type="text" id="cpf" name="cpf" maxlength="14" required>
                            <span class="error-message">CPF inválido</span>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">E-mail *</label>
                            <input type="email" id="email" name="email" required>
                            <span class="error-message">E-mail inválido</span>
                        </div>

                        <div class="form-group">
                            <label for="telefone">Telefone *</label>
                            <input type="tel" id="telefone" name="telefone" maxlength="15" required>
                            <span class="error-message">Telefone inválido</span>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="data_nascimento">Data de Nascimento *</label>
                            <input type="date" id="data_nascimento" name="data_nascimento" required>
                            <span class="error-message">Data inválida</span>
                        </div>

                        <div class="form-group">
                            <label for="rg">RG</label>
                            <input type="text" id="rg" name="rg">
                        </div>
                    </div>

                    <div class="form-navigation">
                        <button type="button" class="btn btn-next" onclick="nextStep()">
                            Próximo
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>

                <!-- Step 2: Address -->
                <div class="form-step" data-step="2">
                    <h2>Endereço</h2>
                    <p>Onde você mora? Precisamos dessas informações para enviar correspondências</p>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="cep">CEP *</label>
                            <input type="text" id="cep" name="cep" maxlength="9" required>
                            <span class="error-message">CEP inválido</span>
                        </div>

                        <div class="form-group">
                            <label for="estado">Estado *</label>
                            <select id="estado" name="estado" required>
                                <option value="">Selecione</option>
                                <option value="RS">Rio Grande do Sul</option>
                                <option value="SC">Santa Catarina</option>
                                <option value="PR">Paraná</option>
                                <option value="SP">São Paulo</option>
                                <option value="RJ">Rio de Janeiro</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="cidade">Cidade *</label>
                            <input type="text" id="cidade" name="cidade" required>
                            <span class="error-message">Informe a cidade</span>
                        </div>

                        <div class="form-group">
                            <label for="bairro">Bairro *</label>
                            <input type="text" id="bairro" name="bairro" required>
                            <span class="error-message">Informe o bairro</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="endereco">Endereço *</label>
                        <input type="text" id="endereco" name="endereco" placeholder="Rua, Avenida, etc" required>
                        <span class="error-message">Informe o endereço</span>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="numero">Número *</label>
                            <input type="text" id="numero" name="numero" required>
                            <span class="error-message">Informe o número</span>
                        </div>

                        <div class="form-group">
                            <label for="complemento">Complemento</label>
                            <input type="text" id="complemento" name="complemento" placeholder="Apto, Bloco, etc">
                        </div>
                    </div>

                    <div class="form-navigation">
                        <button type="button" class="btn btn-back" onclick="prevStep()">
                            <i class="fas fa-arrow-left"></i>
                            Voltar
                        </button>
                        <button type="button" class="btn btn-next" onclick="nextStep()">
                            Próximo
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>

                <!-- Step 3: Payment -->
                <div class="form-step" data-step="3">
                    <h2>Forma de Pagamento</h2>
                    <p>Escolha como deseja realizar o pagamento</p>

                    <div class="payment-methods">
                        <label class="payment-method" onclick="selectPayment('pix')">
                            <input type="radio" name="payment_method" value="pix" required>
                            <div class="payment-icon">
                                <i class="fas fa-qrcode"></i>
                            </div>
                            <div class="payment-info">
                                <h3>PIX</h3>
                                <p>Aprovação instantânea</p>
                            </div>
                        </label>

                        <label class="payment-method" onclick="selectPayment('card')">
                            <input type="radio" name="payment_method" value="card" required>
                            <div class="payment-icon">
                                <i class="fas fa-credit-card"></i>
                            </div>
                            <div class="payment-info">
                                <h3>Cartão de Crédito</h3>
                                <p>Parcelamento em até 12x</p>
                            </div>
                        </label>

                        <label class="payment-method" onclick="selectPayment('boleto')">
                            <input type="radio" name="payment_method" value="boleto" required>
                            <div class="payment-icon">
                                <i class="fas fa-barcode"></i>
                            </div>
                            <div class="payment-info">
                                <h3>Boleto Bancário</h3>
                                <p>Aprovação em 1-2 dias úteis</p>
                            </div>
                        </label>
                    </div>

                    <div id="cardFields" style="display: none;">
                        <div class="form-group">
                            <label for="card_number">Número do Cartão *</label>
                            <input type="text" id="card_number" name="card_number" maxlength="19" placeholder="0000 0000 0000 0000">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="card_name">Nome no Cartão *</label>
                                <input type="text" id="card_name" name="card_name" placeholder="Como está no cartão">
                            </div>

                            <div class="form-group">
                                <label for="card_expiry">Validade *</label>
                                <input type="text" id="card_expiry" name="card_expiry" maxlength="5" placeholder="MM/AA">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="card_cvv">CVV *</label>
                                <input type="text" id="card_cvv" name="card_cvv" maxlength="4" placeholder="000">
                            </div>

                            <div class="form-group">
                                <label for="card_installments">Parcelas *</label>
                                <select id="card_installments" name="card_installments">
                                    <option value="1">1x de <?= $preco_formatado ?></option>
                                    <?php 
                                    $max_parcelas = $plano['parcelas'] ?? 12;
                                    for ($i = 2; $i <= $max_parcelas; $i++): 
                                        $valor_parcela = number_format($plano['preco_anual'] / $i, 2, ',', '.');
                                    ?>
                                    <option value="<?= $i ?>"><?= $i ?>x de R$ <?= $valor_parcela ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-navigation">
                        <button type="button" class="btn btn-back" onclick="prevStep()">
                            <i class="fas fa-arrow-left"></i>
                            Voltar
                        </button>
                        <button type="button" class="btn btn-next" onclick="nextStep()">
                            Próximo
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>

                <!-- Step 4: Confirmation -->
                <div class="form-step" data-step="4">
                    <h2>Confirme seus Dados</h2>
                    <p>Revise as informações antes de finalizar</p>

                    <div id="reviewData" style="background: #f8f9fa; padding: 24px; border-radius: 12px; margin-bottom: 24px;">
                        <!-- Data will be populated by JavaScript -->
                    </div>

                    <div class="form-group" style="background: linear-gradient(135deg, rgba(17, 29, 105, 0.05), rgba(235, 56, 53, 0.05)); padding: 20px; border-radius: 12px; border: 2px solid rgba(235, 56, 53, 0.2);">
                        <label style="display: flex; align-items: center; gap: 12px; cursor: pointer; font-size: 14px; color: #333;">
                            <input type="checkbox" id="terms" name="terms" required style="width: 22px; height: 22px; accent-color: var(--vermelho-primario); cursor: pointer;">
                            <span>Li e aceito os <a href="#" style="color: var(--vermelho-primario); text-decoration: none; font-weight: 700;">termos de uso</a> e a <a href="#" style="color: var(--vermelho-primario); text-decoration: none; font-weight: 700;">política de privacidade</a></span>
                        </label>
                    </div>

                    <div class="form-navigation">
                        <button type="button" class="btn btn-back" onclick="prevStep()">
                            <i class="fas fa-arrow-left"></i>
                            Voltar
                        </button>
                        <button type="submit" class="btn btn-next">
                            <i class="fas fa-check-circle"></i>
                            Finalizar Assinatura
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Order Summary -->
        <div class="order-summary">
            <h3>
                <i class="fas fa-shopping-bag"></i>
                Resumo do Pedido
            </h3>

            <span class="plan-badge <?= strtolower($plano['tipo']) ?>">
                <?= htmlspecialchars($plano['tipo']) ?>
            </span>

            <div class="plan-name"><?= htmlspecialchars($plano['nome']) ?></div>

            <div class="plan-price"><?= $preco_formatado ?></div>
            <span class="plan-period">pagamento anual</span>

            <div class="summary-divider"></div>

            <h4 style="font-size: 16px; color: #333; margin-bottom: 12px;">Benefícios Inclusos:</h4>
            <ul class="benefits-list">
                <?php foreach ($beneficios as $beneficio): ?>
                    <li>
                        <i class="fas fa-check-circle"></i>
                        <?= htmlspecialchars(trim($beneficio)) ?>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="summary-divider"></div>

            <div class="summary-row">
                <span>Subtotal</span>
                <span><?= $preco_formatado ?></span>
            </div>

            <div class="summary-row">
                <span>Taxas</span>
                <span>R$ 0,00</span>
            </div>

            <div class="summary-total">
                <span>Total</span>
                <span><?= $preco_formatado ?></span>
            </div>

            <div class="security-badge">
                <i class="fas fa-lock"></i>
                <p><strong>Pagamento 100% Seguro</strong><br>Seus dados estão protegidos</p>
            </div>
        </div>
    </div>

    <script>
        let currentStep = 1;
        const totalSteps = 4;

        // Máscaras de input
        document.getElementById('cpf').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            e.target.value = value;
        });

        document.getElementById('telefone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/^(\d{2})(\d)/g, '($1) $2');
            value = value.replace(/(\d)(\d{4})$/, '$1-$2');
            e.target.value = value;
        });

        document.getElementById('cep').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/(\d{5})(\d)/, '$1-$2');
            e.target.value = value;
        });

        document.getElementById('card_number').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/(\d{4})(?=\d)/g, '$1 ');
            e.target.value = value;
        });

        document.getElementById('card_expiry').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/(\d{2})(\d)/, '$1/$2');
            e.target.value = value;
        });

        function selectPayment(method) {
            document.querySelectorAll('.payment-method').forEach(el => {
                el.classList.remove('selected');
            });
            event.currentTarget.classList.add('selected');

            const cardFields = document.getElementById('cardFields');
            if (method === 'card') {
                cardFields.style.display = 'block';
            } else {
                cardFields.style.display = 'none';
            }
        }

        function updateProgress() {
            const progress = ((currentStep - 1) / (totalSteps - 1)) * 100;
            document.getElementById('progressLine').style.width = progress + '%';

            document.querySelectorAll('.step').forEach((step, index) => {
                const stepNum = index + 1;
                if (stepNum < currentStep) {
                    step.classList.add('completed');
                    step.classList.remove('active');
                } else if (stepNum === currentStep) {
                    step.classList.add('active');
                    step.classList.remove('completed');
                } else {
                    step.classList.remove('active', 'completed');
                }
            });

            document.querySelectorAll('.form-step').forEach((step, index) => {
                if (index + 1 === currentStep) {
                    step.classList.add('active');
                } else {
                    step.classList.remove('active');
                }
            });
        }

        function validateStep(step) {
            const currentStepEl = document.querySelector(`.form-step[data-step="${step}"]`);
            const inputs = currentStepEl.querySelectorAll('input[required], select[required]');
            let isValid = true;

            inputs.forEach(input => {
                if (!input.value || (input.type === 'email' && !input.value.includes('@'))) {
                    input.classList.add('error');
                    input.nextElementSibling?.classList.add('show');
                    isValid = false;
                } else {
                    input.classList.remove('error');
                    input.nextElementSibling?.classList.remove('show');
                }
            });

            return isValid;
        }

        function nextStep() {
            if (validateStep(currentStep)) {
                if (currentStep === 3) {
                    populateReview();
                }
                if (currentStep < totalSteps) {
                    currentStep++;
                    updateProgress();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            }
        }

        function prevStep() {
            if (currentStep > 1) {
                currentStep--;
                updateProgress();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        }

        function populateReview() {
            const reviewData = document.getElementById('reviewData');
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
            const paymentText = paymentMethod ? paymentMethod.parentElement.querySelector('h3').textContent : 'Não selecionado';

            reviewData.innerHTML = `
                <h4 style="margin-bottom: 16px; color: #333;"><i class="fas fa-user"></i> Dados Pessoais</h4>
                <p style="margin-bottom: 8px;"><strong>Nome:</strong> ${document.getElementById('nome').value}</p>
                <p style="margin-bottom: 8px;"><strong>CPF:</strong> ${document.getElementById('cpf').value}</p>
                <p style="margin-bottom: 8px;"><strong>E-mail:</strong> ${document.getElementById('email').value}</p>
                <p style="margin-bottom: 24px;"><strong>Telefone:</strong> ${document.getElementById('telefone').value}</p>

                <h4 style="margin-bottom: 16px; color: #333;"><i class="fas fa-map-marker-alt"></i> Endereço</h4>
                <p style="margin-bottom: 8px;">${document.getElementById('endereco').value}, ${document.getElementById('numero').value}</p>
                <p style="margin-bottom: 8px;">${document.getElementById('bairro').value} - ${document.getElementById('cidade').value}/${document.getElementById('estado').value}</p>
                <p style="margin-bottom: 24px;"><strong>CEP:</strong> ${document.getElementById('cep').value}</p>

                <h4 style="margin-bottom: 16px; color: #333;"><i class="fas fa-credit-card"></i> Pagamento</h4>
                <p><strong>Forma de Pagamento:</strong> ${paymentText}</p>
            `;
        }

        document.getElementById('checkoutForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!document.getElementById('terms').checked) {
                alert('Por favor, aceite os termos de uso para continuar.');
                return;
            }

            // Aqui você enviaria os dados para o backend
            alert('Assinatura realizada com sucesso! Em breve você receberá um e-mail de confirmação.');
            // window.location.href = 'obrigado.php';
        });

        // Inicializar
        updateProgress();
    </script>
</body>
</html>
