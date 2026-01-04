# Instruções para Criar a Tabela de Diretoria

## Passo 1: Executar SQL no phpMyAdmin

1. Acesse: http://localhost/phpmyadmin
2. Selecione o banco de dados do projeto (apafut)
3. Clique na aba "SQL"
4. Copie e cole o conteúdo do arquivo: `sql/create_diretoria_table.sql`
5. Clique em "Executar"

## Passo 2: Verificar Criação

A tabela `diretoria` será criada com os seguintes campos:
- id (PRIMARY KEY)
- nome
- cargo
- foto
- ordem
- ativo
- created_at
- updated_at

E já virá com 8 membros de exemplo pré-cadastrados.

## Passo 3: Acessar o Painel Admin

1. Acesse: http://localhost/apafut/admin/
2. Faça login
3. No menu lateral, clique em "Diretoria"
4. Você verá os membros cadastrados

## Passo 4: Fazer Upload das Fotos

1. No painel admin, clique em cada membro
2. Faça upload da foto correspondente
3. As fotos serão salvas em: `assets/diretoria/`

## Observações

- A seção de diretoria aparece na página `historia.html` entre as estatísticas e as unidades
- O design segue o padrão da imagem fornecida (foto + fundo verde escuro com nome e cargo)
- É possível ordenar os membros alterando o campo "ordem"
- Membros inativos não aparecem no site público

## Posição na Página História

A seção foi inserida estrategicamente após as estatísticas (20+ anos, 3 unidades, etc.) e antes da seção de unidades, pois:
- Faz sentido apresentar a diretoria logo após mostrar os números da organização
- Dá credibilidade mostrando quem está à frente da APAFUT
- Mantém um fluxo lógico: História → Números → Líderes → Unidades
