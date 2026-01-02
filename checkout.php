<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');
mb_internal_encoding('UTF-8');
require_once 'config/security-headers.php';
require_once 'config/db.php';
require_once 'src/Security.php';
require_once 'src/BotProtection.php';
require_once 'src/SEO.php';

// Proteção contra bots
BotProtection::check();

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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/checkout.css">
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
                <div class="progress-line" id="progressLine"></div>
                
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
                
                <!-- Bot Protection Honeypot -->
                <?= BotProtection::renderHoneypot() ?>
                
                <!-- Step 1: Personal Information -->
                <div class="form-step active" data-step="1">
                    <h2>Dados Pessoais</h2>
                    <p class="form-step-subtitle">Informe seus dados para prosseguir com a assinatura</p>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="nome">Nome Completo <span class="required">*</span></label>
                            <input type="text" id="nome" name="nome" required>
                            <span class="error-message">Por favor, informe seu nome completo</span>
                        </div>

                        <div class="form-group">
                            <label for="cpf">CPF <span class="required">*</span></label>
                            <input type="text" id="cpf" name="cpf" maxlength="14" required>
                            <span class="error-message">CPF inválido</span>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">E-mail <span class="required">*</span></label>
                            <input type="email" id="email" name="email" required>
                            <span class="error-message">E-mail inválido</span>
                        </div>

                        <div class="form-group">
                            <label for="telefone">Telefone <span class="required">*</span></label>
                            <input type="tel" id="telefone" name="telefone" maxlength="15" required>
                            <span class="error-message">Telefone inválido</span>
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
                    <p class="form-step-subtitle">Onde você mora? Precisamos dessas informações para enviar correspondências</p>

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
                    <p class="form-step-subtitle">Escolha como deseja realizar o pagamento</p>

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

                    <div id="cardFields" class="card-fields">
                        <!-- Credit Card Preview -->
                        <div class="card-preview-container">
                            <div class="credit-card" id="creditCard">
                                <div class="card-front">
                                    <div class="card-bg"></div>
                                    <div class="card-top-row">
                                        <div class="card-chip">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 50 40">
                                                <rect x="2" y="2" width="46" height="36" rx="4" fill="url(#chipGradient)" stroke="#000" stroke-width="0.5"/>
                                                <rect x="8" y="10" width="12" height="10" rx="1" fill="rgba(0,0,0,0.2)"/>
                                                <rect x="22" y="10" width="12" height="10" rx="1" fill="rgba(0,0,0,0.2)"/>
                                                <rect x="36" y="10" width="6" height="10" rx="1" fill="rgba(0,0,0,0.2)"/>
                                                <rect x="8" y="22" width="18" height="8" rx="1" fill="rgba(0,0,0,0.2)"/>
                                                <rect x="28" y="22" width="14" height="8" rx="1" fill="rgba(0,0,0,0.2)"/>
                                                <defs>
                                                    <linearGradient id="chipGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                                        <stop offset="0%" style="stop-color:#f9d423"/>
                                                        <stop offset="100%" style="stop-color:#e8b710"/>
                                                    </linearGradient>
                                                </defs>
                                            </svg>
                                        </div>
                                        <div class="card-brand" id="cardBrand">
                                            <i class="fab fa-cc-visa"></i>
                                        </div>
                                    </div>
                                    <div class="card-number" id="cardNumberDisplay">
                                        <span>####</span>
                                        <span>####</span>
                                        <span>####</span>
                                        <span>####</span>
                                    </div>
                                    <div class="card-bottom-row">
                                        <div class="card-holder">
                                            <label>NOME DO TITULAR</label>
                                            <div id="cardHolderDisplay"></div>
                                        </div>
                                        <div class="card-expiry-front">
                                            <label>VALIDADE</label>
                                            <div id="cardExpiryDisplay"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-back">
                                    <div class="card-stripe"></div>
                                    <div class="card-signature">
                                        <div class="card-cvv-container">
                                            <label>CVV</label>
                                            <div id="cardCvvDisplay">***</div>
                                        </div>
                                    </div>
                                    <div class="card-back-brand" id="cardBrandBack">
                                        <i class="fab fa-cc-visa"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card Form -->
                        <div class="card-form">
                            <div class="form-group">
                                <label for="card_number">Número do Cartão <span class="required">*</span></label>
                                <input type="text" id="card_number" name="card_number" maxlength="19" placeholder="0000 0000 0000 0000">
                            </div>

                            <div class="form-group">
                                <label for="card_name">Nome no Cartão <span class="required">*</span></label>
                                <input type="text" id="card_name" name="card_name" placeholder="COMO ESTÁ NO CARTÃO">
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="card_expiry">Validade <span class="required">*</span></label>
                                    <input type="text" id="card_expiry" name="card_expiry" maxlength="5" placeholder="MM/AA">
                                </div>

                                <div class="form-group">
                                    <label for="card_cvv">CVV <span class="required">*</span></label>
                                    <input type="text" id="card_cvv" name="card_cvv" maxlength="4" placeholder="000">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="card_installments">Parcelas <span class="required">*</span></label>
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
                    <p class="form-step-subtitle">Revise as informações antes de finalizar</p>

                    <div id="reviewData" class="review-data">
                        <!-- Data will be populated by JavaScript -->
                    </div>

                    <div class="form-group terms-group">
                        <label class="terms-label">
                            <input type="checkbox" id="terms" name="terms" required class="terms-checkbox">
                            <span>Li e aceito os <a href="#" class="terms-link">termos de uso</a> e a <a href="#" class="terms-link">política de privacidade</a></span>
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

            <h4 class="benefits-title">Benefícios Inclusos:</h4>
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

        // Máscaras de input com validações
        document.getElementById('cpf').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            e.target.value = value;
            
            // Validação básica de CPF
            const cpfLimpo = value.replace(/\D/g, '');
            if (cpfLimpo.length === 11) {
                if (validarCPF(cpfLimpo)) {
                    e.target.classList.add('valid');
                    e.target.classList.remove('error');
                } else {
                    e.target.classList.add('error');
                    e.target.classList.remove('valid');
                }
            }
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
            
            // Buscar CEP automaticamente
            if (value.replace(/\D/g, '').length === 8) {
                buscarCEP(value.replace(/\D/g, ''));
            }
        });

        document.getElementById('email').addEventListener('blur', function(e) {
            const email = e.target.value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (emailRegex.test(email)) {
                e.target.classList.add('valid');
                e.target.classList.remove('error');
            } else if (email) {
                e.target.classList.add('error');
                e.target.classList.remove('valid');
            }
        });

        document.getElementById('card_number')?.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/(\d{4})(?=\d)/g, '$1 ');
            e.target.value = value;
        });

        document.getElementById('card_expiry')?.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/(\d{2})(\d)/, '$1/$2');
            e.target.value = value;
        });

        document.getElementById('card_cvv')?.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '');
        });

        // Validação de CPF
        function validarCPF(cpf) {
            cpf = cpf.replace(/\D/g, '');
            
            if (cpf.length !== 11 || /^(\d)\1{10}$/.test(cpf)) {
                return false;
            }
            
            let soma = 0;
            let resto;
            
            for (let i = 1; i <= 9; i++) {
                soma += parseInt(cpf.substring(i - 1, i)) * (11 - i);
            }
            
            resto = (soma * 10) % 11;
            if (resto === 10 || resto === 11) resto = 0;
            if (resto !== parseInt(cpf.substring(9, 10))) return false;
            
            soma = 0;
            for (let i = 1; i <= 10; i++) {
                soma += parseInt(cpf.substring(i - 1, i)) * (12 - i);
            }
            
            resto = (soma * 10) % 11;
            if (resto === 10 || resto === 11) resto = 0;
            if (resto !== parseInt(cpf.substring(10, 11))) return false;
            
            return true;
        }

        // Buscar CEP via API
        async function buscarCEP(cep) {
            try {
                const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
                const data = await response.json();
                
                if (!data.erro) {
                    document.getElementById('endereco').value = data.logradouro || '';
                    document.getElementById('bairro').value = data.bairro || '';
                    document.getElementById('cidade').value = data.localidade || '';
                    document.getElementById('estado').value = data.uf || '';
                    
                    // Adicionar animação de sucesso
                    document.getElementById('cep').classList.add('valid');
                    
                    // Focar no próximo campo vazio
                    if (!document.getElementById('numero').value) {
                        document.getElementById('numero').focus();
                    }
                }
            } catch (error) {
                console.error('Erro ao buscar CEP:', error);
            }
        }

        // Selecionar método de pagamento
        function selectPayment(method) {
            document.querySelectorAll('.payment-method').forEach(el => {
                el.classList.remove('selected');
            });
            event.currentTarget.classList.add('selected');

            const cardFields = document.getElementById('cardFields');
            if (method === 'card') {
                cardFields.classList.add('active');
                // Tornar campos do cartão obrigatórios
                document.getElementById('card_number').required = true;
                document.getElementById('card_name').required = true;
                document.getElementById('card_expiry').required = true;
                document.getElementById('card_cvv').required = true;
            } else {
                cardFields.classList.remove('active');
                // Remover obrigatoriedade dos campos do cartão
                document.getElementById('card_number').required = false;
                document.getElementById('card_name').required = false;
                document.getElementById('card_expiry').required = false;
                document.getElementById('card_cvv').required = false;
            }
        }

        // Credit Card Animation
        function setupCardAnimation() {
            const cardNumberInput = document.getElementById('card_number');
            const cardNameInput = document.getElementById('card_name');
            const cardExpiryInput = document.getElementById('card_expiry');
            const cardCvvInput = document.getElementById('card_cvv');
            const creditCard = document.getElementById('creditCard');

            // Detect card brand based on number
            function detectCardBrand(number) {
                const cleanNumber = number.replace(/\s/g, '');
                const cardBrand = document.getElementById('cardBrand');
                const cardBrandBack = document.getElementById('cardBrandBack');
                
                let brand = 'default';
                let iconClass = 'fab fa-credit-card';

                // Visa: starts with 4
                if (/^4/.test(cleanNumber)) {
                    brand = 'visa';
                    iconClass = 'fab fa-cc-visa';
                }
                // Mastercard: starts with 51-55 or 2221-2720
                else if (/^5[1-5]/.test(cleanNumber) || /^2(22[1-9]|2[3-9][0-9]|[3-6][0-9]{2}|7[0-1][0-9]|720)/.test(cleanNumber)) {
                    brand = 'mastercard';
                    iconClass = 'fab fa-cc-mastercard';
                }
                // American Express: starts with 34 or 37
                else if (/^3[47]/.test(cleanNumber)) {
                    brand = 'amex';
                    iconClass = 'fab fa-cc-amex';
                }
                // Discover: starts with 6011, 622126-622925, 644-649, or 65
                else if (/^6011|^64[4-9]|^65/.test(cleanNumber)) {
                    brand = 'discover';
                    iconClass = 'fab fa-cc-discover';
                }
                // Diners Club: starts with 36, 38, or 300-305
                else if (/^3(?:0[0-5]|[68])/.test(cleanNumber)) {
                    brand = 'diners';
                    iconClass = 'fab fa-cc-diners-club';
                }
                // JCB: starts with 2131, 1800, or 35
                else if (/^(?:2131|1800|35)/.test(cleanNumber)) {
                    brand = 'jcb';
                    iconClass = 'fab fa-cc-jcb';
                }
                // Elo: Brazilian card
                else if (/^(4011|4312|4389|4514|4576|5041|5066|5090|6277|6362|6363|6504|6505|6516)/.test(cleanNumber)) {
                    brand = 'elo';
                    iconClass = 'fas fa-credit-card';
                }

                if (cardBrand) {
                    cardBrand.innerHTML = `<i class="${iconClass}"></i>`;
                    cardBrand.className = `card-brand ${brand}`;
                }
                if (cardBrandBack) {
                    cardBrandBack.innerHTML = `<i class="${iconClass}"></i>`;
                    cardBrandBack.className = `card-back-brand ${brand}`;
                }
            }

            // Update card number
            if (cardNumberInput) {
                cardNumberInput.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\s/g, '');
                    
                    // Detect brand
                    detectCardBrand(value);
                    
                    // Format number
                    let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
                    e.target.value = formattedValue;

                    const display = document.getElementById('cardNumberDisplay');
                    const groups = formattedValue.split(' ');
                    const spans = display.querySelectorAll('span');
                    
                    spans.forEach((span, index) => {
                        if (groups[index]) {
                            span.textContent = groups[index].padEnd(4, '#');
                            span.classList.add('filled');
                        } else {
                            span.textContent = '####';
                            span.classList.remove('filled');
                        }
                    });
                });
            }

            // Update card holder name
            if (cardNameInput) {
                cardNameInput.addEventListener('input', function(e) {
                    const value = e.target.value.toUpperCase();
                    document.getElementById('cardHolderDisplay').textContent = value || 'LEONARDO LIMA';
                });
            }

            // Update expiry date
            if (cardExpiryInput) {
                cardExpiryInput.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.length >= 2) {
                        value = value.slice(0, 2) + '/' + value.slice(2, 4);
                    }
                    e.target.value = value;
                    document.getElementById('cardExpiryDisplay').textContent = value || '12/34';
                });
            }

            // Update CVV and flip card
            if (cardCvvInput) {
                cardCvvInput.addEventListener('focus', function() {
                    creditCard.classList.add('flipped');
                });

                cardCvvInput.addEventListener('blur', function() {
                    creditCard.classList.remove('flipped');
                });

                cardCvvInput.addEventListener('input', function(e) {
                    const value = e.target.value.replace(/\D/g, '');
                    e.target.value = value;
                    document.getElementById('cardCvvDisplay').textContent = value.padEnd(3, '*');
                });
            }

            // Only numbers for card number and CVV
            if (cardNumberInput) {
                cardNumberInput.addEventListener('keypress', function(e) {
                    if (!/[0-9]/.test(e.key)) {
                        e.preventDefault();
                    }
                });
            }

            if (cardCvvInput) {
                cardCvvInput.addEventListener('keypress', function(e) {
                    if (!/[0-9]/.test(e.key)) {
                        e.preventDefault();
                    }
                });
            }
        }

        // Initialize card animation
        setupCardAnimation();

        // Atualizar barra de progresso
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

        // Validar campos do step atual
        function validateStep(step) {
            const currentStepEl = document.querySelector(`.form-step[data-step="${step}"]`);
            const inputs = currentStepEl.querySelectorAll('input[required], select[required]');
            let isValid = true;

            inputs.forEach(input => {
                const errorMsg = input.nextElementSibling;
                
                // Validar se está vazio
                if (!input.value.trim()) {
                    input.classList.add('error');
                    if (errorMsg && errorMsg.classList.contains('error-message')) {
                        errorMsg.classList.add('show');
                    }
                    isValid = false;
                    return;
                }
                
                // Validações específicas
                if (input.type === 'email') {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(input.value)) {
                        input.classList.add('error');
                        if (errorMsg && errorMsg.classList.contains('error-message')) {
                            errorMsg.classList.add('show');
                        }
                        isValid = false;
                        return;
                    }
                }
                
                if (input.id === 'cpf') {
                    const cpfLimpo = input.value.replace(/\D/g, '');
                    if (!validarCPF(cpfLimpo)) {
                        input.classList.add('error');
                        if (errorMsg && errorMsg.classList.contains('error-message')) {
                            errorMsg.textContent = 'CPF inválido';
                            errorMsg.classList.add('show');
                        }
                        isValid = false;
                        return;
                    }
                }
                
                // Se passar nas validações, remover erro
                input.classList.remove('error');
                input.classList.add('valid');
                if (errorMsg && errorMsg.classList.contains('error-message')) {
                    errorMsg.classList.remove('show');
                }
            });

            return isValid;
        }

        // Próximo step
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
            } else {
                // Rolar para o primeiro campo com erro
                const firstError = document.querySelector('.form-step.active input.error, .form-step.active select.error');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }
            }
        }

        // Step anterior
        function prevStep() {
            if (currentStep > 1) {
                currentStep--;
                updateProgress();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        }

        // Popular dados da revisão
        function populateReview() {
            const reviewData = document.getElementById('reviewData');
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
            const paymentText = paymentMethod ? paymentMethod.parentElement.querySelector('h3').textContent : 'Não selecionado';

            let enderecoCompleto = document.getElementById('endereco').value + ', ' + document.getElementById('numero').value;
            if (document.getElementById('complemento').value) {
                enderecoCompleto += ' - ' + document.getElementById('complemento').value;
            }

            reviewData.innerHTML = `
                <div class="review-section">
                    <h4><i class="fas fa-user"></i> Dados Pessoais</h4>
                    <p><strong>Nome:</strong> ${document.getElementById('nome').value}</p>
                    <p><strong>CPF:</strong> ${document.getElementById('cpf').value}</p>
                    <p><strong>E-mail:</strong> ${document.getElementById('email').value}</p>
                    <p><strong>Telefone:</strong> ${document.getElementById('telefone').value}</p>
                </div>

                <div class="review-section">
                    <h4><i class="fas fa-map-marker-alt"></i> Endereço</h4>
                    <p>${enderecoCompleto}</p>
                    <p>${document.getElementById('bairro').value} - ${document.getElementById('cidade').value}/${document.getElementById('estado').value}</p>
                    <p><strong>CEP:</strong> ${document.getElementById('cep').value}</p>
                </div>

                <div class="review-section">
                    <h4><i class="fas fa-credit-card"></i> Pagamento</h4>
                    <p><strong>Forma de Pagamento:</strong> ${paymentText}</p>
                    ${paymentMethod && paymentMethod.value === 'card' && document.getElementById('card_installments') ? `<p><strong>Parcelas:</strong> ${document.getElementById('card_installments').options[document.getElementById('card_installments').selectedIndex].text}</p>` : ''}
                </div>
            `;
        }

        // Submit do formulário
        document.getElementById('checkoutForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!document.getElementById('terms').checked) {
                alert('Por favor, aceite os termos de uso para continuar.');
                return;
            }

            // Validar o step atual
            if (!validateStep(currentStep)) {
                return;
            }

            // Mostrar loading no botão
            const submitBtn = document.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="loading"></span> Processando...';
            submitBtn.disabled = true;

            // Enviar formulário
            setTimeout(() => {
                this.submit();
            }, 1000);
        });

        // Remover erro ao digitar
        document.querySelectorAll('input, select').forEach(input => {
            input.addEventListener('input', function() {
                this.classList.remove('error');
                const errorMsg = this.nextElementSibling;
                if (errorMsg && errorMsg.classList.contains('error-message')) {
                    errorMsg.classList.remove('show');
                }
            });
        });

        // Atalhos de teclado
        document.addEventListener('keydown', function(e) {
            // Enter no input avança
            if (e.target.tagName === 'INPUT' && e.key === 'Enter' && e.target.type !== 'submit') {
                e.preventDefault();
                const nextInput = e.target.closest('.form-group').nextElementSibling?.querySelector('input, select');
                if (nextInput) {
                    nextInput.focus();
                }
            }
        });

        // Inicializar
        updateProgress();

        // Animação de entrada
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                document.querySelector('.checkout-main').style.opacity = '1';
                document.querySelector('.order-summary').style.opacity = '1';
            }, 100);
        });
    </script>
</body>
</html>
