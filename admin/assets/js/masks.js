// Máscaras para inputs
document.addEventListener('DOMContentLoaded', function() {
    
    // Máscara de Data (DD/MM/YYYY)
    const dataInputs = document.querySelectorAll('input[name="data_nascimento"], input[name="data_publicacao"]');
    dataInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 8) {
                value = value.substring(0, 8);
            }
            if (value.length >= 4) {
                value = value.replace(/(\d{2})(\d{2})(\d{0,4})/, '$1/$2/$3');
            } else if (value.length >= 2) {
                value = value.replace(/(\d{2})(\d{0,2})/, '$1/$2');
            }
            e.target.value = value;
        });
    });
    
    // Máscara de Altura (X.XXm)
    const alturaInputs = document.querySelectorAll('input[name="altura"]');
    alturaInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 3) {
                value = value.substring(0, 3);
            }
            if (value.length >= 2) {
                value = value.replace(/(\d{1})(\d{2})/, '$1.$2m');
            } else if (value.length === 1) {
                value = value + 'm';
            }
            e.target.value = value;
        });
        
        input.addEventListener('blur', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value && !e.target.value.endsWith('m')) {
                if (value.length === 3) {
                    e.target.value = value.substring(0, 1) + '.' + value.substring(1) + 'm';
                } else if (value.length === 2) {
                    e.target.value = '1.' + value + 'm';
                }
            }
        });
    });
    
    // Máscara de Peso (XXXkg)
    const pesoInputs = document.querySelectorAll('input[name="peso"]');
    pesoInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 3) {
                value = value.substring(0, 3);
            }
            if (value) {
                e.target.value = value + 'kg';
            } else {
                e.target.value = '';
            }
        });
        
        input.addEventListener('blur', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value) {
                e.target.value = value + 'kg';
            }
        });
    });
    
    // Máscara de Telefone ((XX) XXXXX-XXXX)
    const telefoneInputs = document.querySelectorAll('input[type="tel"], input[name="telefone"]');
    telefoneInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 11) {
                value = value.substring(0, 11);
            }
            if (value.length >= 11) {
                value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            } else if (value.length >= 7) {
                value = value.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
            } else if (value.length >= 2) {
                value = value.replace(/(\d{2})(\d{0,5})/, '($1) $2');
            }
            e.target.value = value;
        });
    });
    
    // Máscara de CPF (XXX.XXX.XXX-XX)
    const cpfInputs = document.querySelectorAll('input[name="cpf"]');
    cpfInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 11) {
                value = value.substring(0, 11);
            }
            if (value.length >= 9) {
                value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{0,2})/, '$1.$2.$3-$4');
            } else if (value.length >= 6) {
                value = value.replace(/(\d{3})(\d{3})(\d{0,3})/, '$1.$2.$3');
            } else if (value.length >= 3) {
                value = value.replace(/(\d{3})(\d{0,3})/, '$1.$2');
            }
            e.target.value = value;
        });
    });
    
    // Máscara de CEP (XXXXX-XXX)
    const cepInputs = document.querySelectorAll('input[name="cep"]');
    cepInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 8) {
                value = value.substring(0, 8);
            }
            if (value.length >= 5) {
                value = value.replace(/(\d{5})(\d{0,3})/, '$1-$2');
            }
            e.target.value = value;
        });
    });
    
    // Máscara de Preço (R$ X.XXX,XX)
    const precoInputs = document.querySelectorAll('input[name*="preco"], input[name*="valor"]');
    precoInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value) {
                value = (parseInt(value) / 100).toFixed(2);
                value = value.replace('.', ',');
                value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                e.target.value = 'R$ ' + value;
            } else {
                e.target.value = '';
            }
        });
    });
    
});

// Função para limpar máscaras antes de submeter formulário
function removeMasks(formId) {
    const form = document.getElementById(formId);
    if (form) {
        form.addEventListener('submit', function(e) {
            // Remove máscaras de telefone, CPF, CEP, preço
            const maskedInputs = form.querySelectorAll('[name="telefone"], [name="cpf"], [name="cep"], [name*="preco"], [name*="valor"]');
            maskedInputs.forEach(input => {
                input.value = input.value.replace(/\D/g, '');
            });
        });
    }
}
