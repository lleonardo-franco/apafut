# 🚀 Sistema de Minificação Automática

Este projeto possui um sistema automatizado de minificação de assets (CSS e JS) para melhorar a performance do site.

## 📋 Opções de Uso

### 1. Minificação Manual (Uma vez)

Execute o comando para minificar todos os arquivos:

```bash
php minify-assets.php
```

Ou usando Composer:

```bash
composer minify
```

### 2. Watch Mode (Minificação Automática)

Inicia um processo que monitora mudanças e minifica automaticamente:

```bash
php watch-assets.php
```

Ou usando Composer:

```bash
composer watch
```

**💡 Dica:** Mantenha este processo rodando em um terminal separado durante o desenvolvimento.

### 3. Tasks do VS Code

Pressione `Ctrl+Shift+B` ou `Cmd+Shift+B` (Mac) e escolha:

- **Minificar Assets**: Minifica uma vez
- **Minificar Assets (Watch Mode)**: Inicia o watcher
- **Minificar CSS Específico**: Minifica o arquivo aberto

### 4. Minificação ao Salvar (VS Code)

**Instale a extensão:**
- Nome: `Run on Save`
- ID: `emeraldwalk.RunOnSave`

Depois, ao salvar qualquer arquivo CSS ou JS, ele será minificado automaticamente!

### 5. Comandos Composer Individuais

```bash
# Minificar apenas CSS
composer minify-css

# Minificar apenas JS
composer minify-js
```

## 📁 Arquivos Monitorados

O sistema minifica automaticamente:

- `assets/css/*.css` → `assets/css/*.min.css`
- `assets/js/*.js` → `assets/js/*.min.js`

**⚠️ Importante:** Arquivos `.min.css` e `.min.js` são ignorados para evitar loop infinito.

## 🎯 Economia de Espaço

O sistema mostra em tempo real:
- Tamanho original
- Tamanho minificado
- Percentual de economia

Exemplo de output:
```
✓ assets/css/style.css
  → assets/css/style.min.css
  📦 145.32 KB → 98.76 KB (economia: 32.05%)
```

## 🔧 Integração com Produção

O sistema minifica automaticamente após:
- `composer install`
- `composer update`

## 📊 Benefícios

- ✅ Redução de 30-50% no tamanho dos arquivos
- ✅ Carregamento mais rápido das páginas
- ✅ Menor consumo de banda
- ✅ Melhor pontuação no Google PageSpeed
- ✅ Melhor experiência do usuário

## 🛠️ Troubleshooting

### Watch mode não funciona?

Certifique-se de que o PHP está instalado e acessível via terminal:

```bash
php -v
```

### Permissões?

Se houver erro de permissão, execute:

```bash
# Windows (PowerShell como Admin)
icacls assets /grant Users:F /t

# Linux/Mac
chmod -R 755 assets/
```

## 📝 Notas

- O watcher verifica mudanças a cada 2 segundos
- Apenas arquivos não-minificados são processados
- O sistema é automaticamente executado em produção via Composer
