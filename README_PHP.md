# Apafut - Caxias do Sul

Site institucional da Associação de Pais e Amigos do Futebol de Caxias do Sul.

## 🚀 Como Rodar o Projeto

### Pré-requisitos

- **PHP 8.0+** com extensões: `pdo`, `pdo_mysql`, `gd`, `mbstring`
- **MySQL 8.0+** ou MariaDB 10.5+
- **Git** (para clonar o repositório)

### 1️⃣ Clonar o Projeto

```bash
git clone <url-do-repositorio>
cd apafut
```

### 2️⃣ Configurar Banco de Dados

**Criar banco de dados:**

```bash
# Windows (PowerShell)
mysql -u root -p -e "CREATE DATABASE apafut_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Linux/Mac
mysql -u root -p -e "CREATE DATABASE apafut_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

**Importar estrutura e dados:**

```bash
# Windows (PowerShell)
Get-Content sql/database.sql | mysql -u root -p apafut_db

# Linux/Mac
mysql -u root -p apafut_db < sql/database.sql
```

**Aplicar índices de performance (IMPORTANTE):**

```bash
# Windows (PowerShell)
Get-Content sql/performance_indexes.sql | mysql -u root -p apafut_db

# Linux/Mac
mysql -u root -p apafut_db < sql/performance_indexes.sql
```

**Criar tabela de assinaturas:**

```bash
# Windows (PowerShell)
Get-Content sql/create_assinaturas_table.sql | mysql -u root -p apafut_db

# Linux/Mac
mysql -u root -p apafut_db < sql/create_assinaturas_table.sql
```

### 3️⃣ Configurar Credenciais do Banco

Edite o arquivo `config/db.php` e configure suas credenciais:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'apafut_db');
define('DB_USER', 'root');
define('DB_PASS', 'sua_senha_aqui');
define('DB_CHARSET', 'utf8mb4');
```

### 4️⃣ Configurar Permissões

```bash
# Windows (PowerShell)
New-Item -ItemType Directory -Path cache,logs -Force
icacls cache /grant Everyone:F
icacls logs /grant Everyone:F

# Linux/Mac
mkdir -p cache logs
chmod 755 cache logs
```

### 5️⃣ Iniciar o Servidor

**Servidor PHP Built-in (Desenvolvimento):**

```bash
php -S localhost:8000
```

Acesse: **[http://localhost:8000](http://localhost:8000)**

**XAMPP/WAMP/MAMP (Alternativa):**

1. Coloque a pasta do projeto em `htdocs/` ou `www/`
2. Inicie Apache e MySQL
3. Acesse: [http://localhost/apafut](http://localhost/apafut)

### 6️⃣ Acessar Painel Admin

- **URL:** [http://localhost:8000/admin](http://localhost:8000/admin)
- **Usuário:** admin
- **Senha:** admin123

⚠️ **IMPORTANTE:** Altere a senha padrão em produção!

## 📁 Estrutura do Projeto

```
apafut/
├── admin/              # Painel administrativo
│   ├── analytics.php   # Dashboard de analytics
│   ├── noticias.php    # Gerenciar notícias
│   ├── jogadores.php   # Gerenciar jogadores
│   ├── planos.php      # Gerenciar planos
│   └── depoimentos.php # Gerenciar depoimentos
├── api/                # APIs REST em JSON
│   ├── get_noticias.php
│   ├── get_jogadores.php
│   ├── get_planos.php
│   └── get_depoimentos.php
├── assets/             # Arquivos estáticos
│   ├── css/           # Arquivos CSS
│   ├── js/            # JavaScript
│   ├── images/        # Imagens e uploads
│   │   ├── jogadores/ # Fotos de jogadores
│   │   └── noticias/  # Imagens de notícias
│   └── videos/        # Vídeos de depoimentos
├── cache/             # Cache de queries (gerado automaticamente)
├── config/            # Configurações
│   ├── db.php        # Conexão com banco
│   └── security-headers.php # Headers de segurança
├── includes/          # Componentes reutilizáveis
│   ├── analytics-tracker.php
│   ├── sidebar.php
│   └── topbar.php
├── logs/              # Logs de erros
├── sql/               # Scripts SQL
│   ├── database.sql   # Estrutura inicial
│   ├── performance_indexes.sql # Índices
│   └── create_assinaturas_table.sql
├── src/               # Classes PHP
│   ├── BotProtection.php  # Proteção contra bots
│   ├── Cache.php          # Sistema de cache
│   ├── CDN.php            # Helper CDN
│   ├── ImageOptimizer.php # Otimização de imagens
│   ├── Security.php       # Funções de segurança
│   └── SEO.php            # SEO avançado
├── checkout.php       # Checkout de planos
├── historia.html      # Página sobre o clube
├── index.php          # Página inicial
├── noticia.php        # Detalhes de notícia
├── obrigado.php       # Página de agradecimento
├── processar-checkout.php # Processamento de checkout
├── robots.txt         # Diretivas para crawlers
├── sitemap.xml        # Mapa do site para SEO
└── .gitignore         # Arquivos ignorados pelo Git
```

## 🔒 Segurança Implementada

- ✅ **Content Security Policy (CSP)** - Proteção contra XSS
- ✅ **CSRF Tokens** - Proteção contra CSRF em formulários
- ✅ **Prepared Statements** - Prevenção de SQL Injection
- ✅ **Sanitização de Inputs** - Validação e limpeza de dados
- ✅ **Proteção contra Bots** - Bloqueio de scrapers maliciosos
- ✅ **Rate Limiting** - Limite de requisições por IP
- ✅ **Honeypot** - Campos invisíveis para detectar bots
- ✅ **Headers de Segurança** - X-Frame-Options, HSTS, etc.

## ⚡ Performance & Otimização

- ✅ **Sistema de Cache** - Cache de queries pesadas (1 hora TTL)
- ✅ **Índices de Banco** - 6 índices para queries 40% mais rápidas
- ✅ **Lazy Loading** - Carregamento preguiçoso de imagens
- ✅ **Imagens Otimizadas** - Suporte a WebP e responsive images
- ✅ **CDN Ready** - Preparado para integração com CDN

## 🎯 SEO Avançado

- ✅ **Meta Tags Dinâmicas** - Title, description, keywords por página
- ✅ **Open Graph** - Otimizado para Facebook, WhatsApp, LinkedIn
- ✅ **Twitter Cards** - Cards otimizados para Twitter/X
- ✅ **Schema.org JSON-LD** - Dados estruturados para rich results
- ✅ **Sitemap.xml** - Mapa do site para indexação
- ✅ **Robots.txt** - Diretivas para crawlers

## 📖 Documentação Completa

Para documentação detalhada, consulte:

- **[IMPLEMENTACOES.md](IMPLEMENTACOES.md)** - Guia completo de todas as funcionalidades avançadas implementadas (SEO, Cache, Proteção contra Bots, Otimização de Imagens, CDN)

## ❓ Problemas Comuns

### Erro: "Call to undefined method"

**Solução:** Reinicie o servidor PHP após editar arquivos de classe.

```bash
# Pare o servidor (Ctrl+C) e reinicie:
php -S localhost:8000
```

### Erro de conexão com banco

**Verifique:**
1. MySQL está rodando: `mysql -u root -p`
2. Credenciais em `config/db.php` estão corretas
3. Banco `apafut_db` foi criado
4. Todos os scripts SQL foram importados

### Imagens não aparecem no admin

**Solução:** Verifique permissões das pastas:

```bash
# Windows
icacls assets\images\jogadores /grant Everyone:F
icacls assets\images\noticias /grant Everyone:F

# Linux/Mac
chmod 755 assets/images/jogadores
chmod 755 assets/images/noticias
```

### Cache não funciona

**Solução:** Verifique permissões da pasta cache:

```bash
# Windows
icacls cache /grant Everyone:F

# Linux/Mac
chmod 755 cache
```

### Font Awesome não carrega

**Solução:** Limpe cache do navegador (Ctrl+Shift+Del) e recarregue.

### Página em branco

**Solução:**
1. Verifique logs: `logs/php_errors.log`
2. Ative exibição de erros temporariamente em `config/db.php`:
   ```php
   ini_set('display_errors', 1);
   error_reporting(E_ALL);
   ```

## 🛠️ Desenvolvimento

### Testar APIs

Acesse diretamente no navegador ou use ferramentas como Postman:

- **Notícias:** [http://localhost:8000/api/get_noticias.php](http://localhost:8000/api/get_noticias.php)
- **Jogadores:** [http://localhost:8000/api/get_jogadores.php](http://localhost:8000/api/get_jogadores.php)
- **Planos:** [http://localhost:8000/api/get_planos.php](http://localhost:8000/api/get_planos.php)
- **Depoimentos:** [http://localhost:8000/api/get_depoimentos.php](http://localhost:8000/api/get_depoimentos.php)

### Limpar Cache

```bash
# Windows (PowerShell)
Remove-Item cache\*.cache -Force

# Linux/Mac
rm -f cache/*.cache
```

### Verificar Banco de Dados

```bash
mysql -u root -p apafut_db

# Comandos úteis:
SHOW TABLES;
DESCRIBE noticias;
SELECT * FROM planos WHERE ativo = 1;
SHOW INDEX FROM noticias;
```

### Gerar Sitemap Dinâmico (Futuro)

Atualmente o sitemap é estático. Para torná-lo dinâmico:

1. Crie `generate-sitemap.php`
2. Busque todas notícias ativas do banco
3. Gere XML com URLs dinâmicas
4. Configure cron job para regenerar diariamente

## 📊 Monitoramento

### Analytics

Acesse: [http://localhost:8000/admin/analytics.php](http://localhost:8000/admin/analytics.php)

Métricas disponíveis:
- Visualizações de página
- Páginas mais acessadas
- Tráfego por dia/semana/mês
- Sessões únicas

### Logs

```bash
# Ver logs de erro PHP
Get-Content logs/php_errors.log -Tail 20

# Ver logs do servidor
# (logs aparecem no terminal onde rodou php -S)
```

## 🚀 Deploy em Produção

### Checklist Pré-Deploy

- [ ] Alterar senha do admin
- [ ] Configurar `config/db.php` com credenciais de produção
- [ ] Atualizar URLs em `src/SEO.php` (trocar localhost por domínio real)
- [ ] Atualizar `sitemap.xml` com URLs reais
- [ ] Atualizar `robots.txt` com URL do sitemap real
- [ ] Configurar HTTPS/SSL
- [ ] Ativar HSTS em `config/security-headers.php`
- [ ] Revisar permissões de pastas (755 para pastas, 644 para arquivos)
- [ ] Configurar CDN em `src/CDN.php` (opcional)
- [ ] Configurar backups automáticos do banco
- [ ] Testar todos formulários e checkout

### Backup do Banco

```bash
# Criar backup
mysqldump -u root -p apafut_db > backup_$(date +%Y%m%d).sql

# Restaurar backup
mysql -u root -p apafut_db < backup_20251212.sql
```

## 📚 Documentação Adicional

- **[IMPLEMENTACOES.md](IMPLEMENTACOES.md)** - Documentação completa das implementações avançadas (SEO, Cache, Bot Protection)
- **Font Awesome:** [https://fontawesome.com/](https://fontawesome.com/)
- **PHP 8.4 Docs:** [https://www.php.net/](https://www.php.net/)
- **MySQL 8.0 Docs:** [https://dev.mysql.com/doc/](https://dev.mysql.com/doc/)

## 📧 Suporte

Para dúvidas ou problemas:
1. Verifique a seção **Problemas Comuns** acima
2. Consulte [IMPLEMENTACOES.md](IMPLEMENTACOES.md)
3. Entre em contato com a equipe de desenvolvimento

---

**Versão:** 2.0 | **Última atualização:** Dezembro 2025
