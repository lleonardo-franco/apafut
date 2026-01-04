# INSTRUÇÕES - Sistema de Comissão Técnica

## O que foi implementado?

Foi adicionado um sistema completo de **Comissão Técnica** ao site da APAFUT, incluindo:

### 1. Banco de Dados
- Tabela `comissao_tecnica` criada com os seguintes campos:
  - id, nome, cargo, foto, descricao, ativo, ordem, created_at

### 2. Backend
- API: `api/get_comissao.php` - Busca membros ativos da comissão
- Painel Admin completo:
  - `admin/comissao.php` - Listagem com filtros
  - `admin/comissao-criar.php` - Criar novo membro
  - `admin/comissao-editar.php` - Editar membro existente
  - `admin/comissao-excluir.php` - Excluir membro

### 3. Frontend
- Nova aba "Comissão Técnica" na seção de Profissionais
- Cards com fotos, nomes e cargos
- Modal com detalhes ao clicar em cada membro
- Carrossel com navegação por setas
- Sistema de abas para alternar entre "Elenco" e "Comissão Técnica"

---

## PASSO A PASSO PARA ATIVAR O SISTEMA

### 1. Executar o SQL
Execute o arquivo SQL para criar a tabela no banco de dados:

```sql
-- Copie e execute o conteúdo do arquivo:
sql/create_comissao_table.sql
```

**Ou execute diretamente:**
1. Abra o phpMyAdmin
2. Selecione o banco de dados `u754804453_apafut`
3. Vá na aba "SQL"
4. Cole e execute o seguinte código:

```sql
CREATE TABLE IF NOT EXISTS comissao_tecnica (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cargo VARCHAR(100) NOT NULL,
    foto VARCHAR(255),
    descricao TEXT,
    ativo TINYINT(1) DEFAULT 1,
    ordem INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO comissao_tecnica (nome, cargo, foto, descricao, ordem) VALUES
('Carlos Mendes', 'Técnico Principal', 'assets/images/comissao/tecnico.jpg', 'Experiente treinador com mais de 15 anos de carreira', 1),
('João Silva', 'Auxiliar Técnico', 'assets/images/comissao/auxiliar.jpg', 'Especialista em táticas e análise de adversários', 2),
('Pedro Santos', 'Preparador Físico', 'assets/images/comissao/preparador.jpg', 'Graduado em Educação Física com especialização em alto rendimento', 3),
('Ana Costa', 'Fisioterapeuta', 'assets/images/comissao/fisio.jpg', 'Responsável pela recuperação e prevenção de lesões', 4);
```

### 2. Criar pasta para fotos
Crie a pasta para as fotos da comissão:
```
assets/images/comissao/
```

### 3. Testar o Sistema

#### No Painel Admin:
1. Acesse: `http://localhost/apafut/admin/`
2. Faça login
3. No menu lateral, clique em **"Comissão Técnica"**
4. Você verá os 4 membros exemplo já cadastrados
5. Teste adicionar, editar e excluir membros

#### No Site:
1. Acesse: `http://localhost/apafut/`
2. Role até a seção **"PROFISSIONAL"**
3. Você verá duas abas:
   - **Elenco** (jogadores - como antes)
   - **Comissão Técnica** (nova funcionalidade!)
4. Clique na aba "Comissão Técnica"
5. Navegue pelos cards usando as setas
6. Clique em qualquer card para ver os detalhes no modal

---

## Funcionalidades Implementadas

### Painel Admin
✅ Listagem de todos os membros
✅ Filtros por nome/cargo
✅ Criar novo membro com upload de foto
✅ Editar membro existente
✅ Excluir membro
✅ Definir ordem de exibição
✅ Ativar/desativar membros
✅ Link no menu lateral

### Site Público
✅ Aba separada "Comissão Técnica"
✅ Cards estilizados com foto e cargo
✅ Modal com detalhes completos
✅ Carrossel com navegação
✅ Suporte touch/swipe em mobile
✅ Design responsivo
✅ Cache automático (3600s)

---

## Cargos Disponíveis

O sistema já vem com os seguintes cargos pré-configurados:
- Técnico Principal
- Auxiliar Técnico
- Preparador Físico
- Preparador de Goleiros
- Fisioterapeuta
- Médico
- Nutricionista
- Analista de Desempenho
- Outro (para cargos personalizados)

---

## Personalização

### Adicionar novos cargos:
Edite os arquivos:
- `admin/comissao.php` (linha ~103)
- `admin/comissao-criar.php` (linha ~152)
- `admin/comissao-editar.php` (linha ~200)

### Alterar fotos padrão:
Substitua as imagens em:
```
assets/images/comissao/default.jpg
```

---

## Estrutura de Arquivos Criados/Modificados

### Novos Arquivos:
```
sql/create_comissao_table.sql
api/get_comissao.php
admin/comissao.php
admin/comissao-criar.php
admin/comissao-editar.php
admin/comissao-excluir.php
```

### Arquivos Modificados:
```
admin/includes/sidebar.php (adicionado link menu)
index.php (adicionadas abas e seção comissão)
assets/css/style.css (estilos para abas e comissão)
assets/js/script.js (funcionalidades JS)
```

---

## Suporte e Dúvidas

Se tiver algum problema:
1. Verifique se a tabela foi criada corretamente
2. Verifique se a pasta `assets/images/comissao/` existe
3. Limpe o cache do navegador (Ctrl+Shift+R)
4. Verifique o console do navegador (F12) para erros JavaScript

---

**Sistema pronto para uso! 🎉**
