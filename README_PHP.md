# Apafut - Caxias do Sul

Site institucional da Associação de Pais e Amigos do Futebol de Caxias do Sul.

## 🚀 Como Rodar o Projeto

### Pré-requisitos

- PHP 8.0 ou superior
- MySQL/MariaDB 5.7 ou superior
- Composer (opcional)

### 1️⃣ Clonar o Projeto

```bash
git clone <url-do-repositorio>
cd apafut
```

### 2️⃣ Configurar Variáveis de Ambiente

Copie o arquivo de exemplo e configure suas credenciais:

```bash
cp .env.example .env
```

Edite o arquivo `.env`:

```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=apafut_db
DB_USER=root
DB_PASS=sua_senha_aqui
APP_ENV=development
```

### 3️⃣ Criar o Banco de Dados

Execute o script SQL no MySQL:

```bash
# Windows (PowerShell)
Get-Content database.sql | mysql -u root -p

# Linux/Mac
mysql -u root -p < database.sql
```

Ou via phpMyAdmin:
1. Acesse phpMyAdmin
2. Crie um banco chamado `apafut_db`
3. Importe o arquivo `database.sql`

### 4️⃣ Iniciar o Servidor

#### Opção 1: Servidor PHP Built-in (Recomendado)

```bash
php -S localhost:8000
```

Acesse: [http://localhost:8000](http://localhost:8000)

#### Opção 2: XAMPP/WAMP

1. Coloque a pasta do projeto em `htdocs/` ou `www/`
2. Inicie Apache e MySQL
3. Acesse: [http://localhost/apafut](http://localhost/apafut)

## 📁 Estrutura do Projeto

```
apafut/
├── assets/              # Arquivos estáticos (imagens, logos)
│   ├── css/            # Arquivos CSS
│   ├── js/             # Arquivos JavaScript
│   └── images/         # Imagens adicionais
├── api/                # APIs REST em JSON
├── config/             # Configurações (conexão DB)
├── src/                # Classes PHP (Security, helpers)
├── .env                # Variáveis de ambiente (NÃO COMMITAR)
├── .env.example        # Template de variáveis
├── database.sql        # Script do banco de dados
├── index.html          # Página inicial
├── historia.html       # Página de história
└── noticia.php         # Página de notícia individual
```

## 🔒 Segurança

- **NUNCA commite o arquivo `.env`** (contém senhas)
- Arquivo `.htaccess` já configurado com proteções
- Classe `Security.php` implementa sanitização e validação
- Todas as queries usam prepared statements

## 📖 Documentação Completa

Para detalhes sobre segurança, APIs e desenvolvimento, consulte:
- [SEGURANCA.md](SEGURANCA.md) - Documentação completa de segurança e estrutura

## ❓ Problemas Comuns

### Erro de conexão com banco

Verifique:
1. MySQL está rodando
2. Credenciais no `.env` estão corretas
3. Banco `apafut_db` foi criado

### Imagens não aparecem

Certifique-se de que as imagens estão na pasta `assets/`

### Página em branco

1. Verifique logs de erro: `logs/error.log`
2. Ative exibição de erros: `ini_set('display_errors', 1);`

## 🛠️ Desenvolvimento

### Testar APIs

Acesse diretamente no navegador:
- [http://localhost:8000/api/get_noticias.php](http://localhost:8000/api/get_noticias.php)
- [http://localhost:8000/api/get_jogadores.php](http://localhost:8000/api/get_jogadores.php)

### Banco de Dados

Acessar via terminal:
```bash
mysql -u root -p
USE apafut_db;
SHOW TABLES;
```

## 📧 Contato

Para dúvidas ou suporte, entre em contato com a equipe de desenvolvimento.
