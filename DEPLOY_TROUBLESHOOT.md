# Configuração de Deploy - APAFUT

## ⚠️ Problema de Deploy via GitHub Actions

O erro "Timeout (control socket)" geralmente ocorre por:

### 1. **Hostinger usa SFTP, não FTP**
A Hostinger geralmente requer **SFTP** (SSH File Transfer Protocol) na porta 22, não FTP tradicional.

### 2. **Credenciais FTP no GitHub**

Verifique se as **Secrets** estão configuradas corretamente no GitHub:

#### Acessar Secrets:
1. Vá em: https://github.com/lleonardo-franco/apafut/settings/secrets/actions
2. Clique em **"New repository secret"**

#### Credenciais necessárias:

**`FTP_SERVER`** - Endereço do servidor
```
Hostinger: ftp.apafutoficial.com.br
OU: srv###.main-hosting.eu (exemplo)
```

**`FTP_USERNAME`** - Usuário FTP
```
Exemplo: u123456789 ou email@apafutoficial.com.br
```

**`FTP_PASSWORD`** - Senha do FTP
```
A senha configurada no painel Hostinger
```

---

## 🚀 Soluções Disponíveis

### Opção 1: Deploy via SFTP (RECOMENDADO)
Já configurado no arquivo `.github/workflows/deploy.yml`

**Requer:**
- Porta 22 (SFTP)
- Credenciais SSH habilitadas no painel Hostinger

**Para encontrar credenciais SFTP na Hostinger:**
1. Acesse o painel Hostinger
2. Vá em **"FTP Accounts"** ou **"Arquivos" → "Gerenciador FTP"**
3. Anote: `Server`, `Username`, `Port` (22 para SFTP)

---

### Opção 2: Deploy Manual via FTP
Arquivo: `.github/workflows/deploy-manual.yml`

**Como usar:**
1. Vá em: https://github.com/lleonardo-franco/apafut/actions
2. Selecione **"Deploy Manual via FTP"**
3. Clique em **"Run workflow"**
4. Escolha o ambiente e execute

---

### Opção 3: Deploy via Git na Hostinger
A Hostinger suporta deploy automático via Git!

**Configurar na Hostinger:**
1. Acesse o painel → **"Avançado"** → **"Git"**
2. Clique em **"Criar repositório"**
3. Cole a URL do GitHub: `https://github.com/lleonardo-franco/apafut.git`
4. Defina branch: `main`
5. Caminho de deploy: `/domains/apafutoficial.com.br/public_html`
6. Salve e clique em **"Pull"**

**Vantagens:**
- ✅ Deploy automático a cada push
- ✅ Não depende de GitHub Actions
- ✅ Sem configuração de FTP/SFTP
- ✅ Logs direto no painel

---

### Opção 4: Deploy Manual via FileZilla

**Download:** https://filezilla-project.org/

**Configuração:**
- **Host:** `sftp://ftp.apafutoficial.com.br` (ou IP do servidor)
- **Porta:** `22` (SFTP) ou `21` (FTP)
- **Protocolo:** SFTP
- **Usuário:** Seu usuário FTP
- **Senha:** Sua senha FTP

**Passos:**
1. Conecte-se ao servidor
2. Navegue até `/domains/apafutoficial.com.br/public_html/`
3. Arraste os arquivos locais para o servidor

---

## 🔧 Verificar Configuração Atual

### Testar conexão SFTP local:
```bash
# No PowerShell
sftp usuario@ftp.apafutoficial.com.br
# Digite a senha quando solicitado
# Se conectar, o SFTP está funcionando
```

### Verificar Secrets do GitHub:
```bash
# Ver se as secrets existem (não mostra valores)
# Vá em: https://github.com/lleonardo-franco/apafut/settings/secrets/actions
```

---

## 📋 Checklist de Solução

- [ ] Verificar se FTP_SERVER, FTP_USERNAME, FTP_PASSWORD estão configurados no GitHub
- [ ] Confirmar qual porta a Hostinger usa (21 para FTP, 22 para SFTP)
- [ ] Testar conexão local com FileZilla
- [ ] Considerar usar Git Deploy nativo da Hostinger (mais simples)
- [ ] Verificar se o IP do GitHub Actions está bloqueado no firewall Hostinger

---

## 🎯 Recomendação Final

**Use o Git Deploy da Hostinger** - É a forma mais simples e confiável:

1. Painel Hostinger → **Avançado** → **Git**
2. Adicionar repositório: `https://github.com/lleonardo-franco/apafut.git`
3. Branch: `main`
4. Deploy automático a cada push

Sem necessidade de FTP, SFTP ou GitHub Actions! 🚀
