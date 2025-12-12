# 🧪 Checklist de Testes - APAFUT

## ✅ Guia Rápido para Validar Todas as Implementações

---

## 1. Editor WYSIWYG (TinyMCE)

### Teste no Admin
- [ ] Acessar `admin/noticia-criar.php`
- [ ] Verificar que o campo "Conteúdo" tem barra de ferramentas rica
- [ ] Testar formatação: **negrito**, *itálico*, cores
- [ ] Inserir uma imagem (upload ou arrastar)
- [ ] Criar lista ordenada e não ordenada
- [ ] Inserir um link
- [ ] Testar preview em tela cheia
- [ ] Salvar e verificar formatação mantida

### Teste na Edição
- [ ] Editar notícia existente
- [ ] Verificar que conteúdo mantém formatação
- [ ] Fazer alterações e salvar
- [ ] Confirmar mudanças no site público

**Status: [x] PASSOU [ ] FALHOU**

---

## 2. Agendamento de Publicação

### Teste SQL
```sql
-- Verificar estrutura da tabela
DESCRIBE noticias;
-- Deve mostrar: status e data_agendamento
```
- [ ] Colunas `status` e `data_agendamento` existem

### Teste UI - Criar
- [ ] Acessar `admin/noticia-criar.php`
- [ ] Verificar dropdown "Status da Publicação"
- [ ] Selecionar "Agendado"
- [ ] Campo de data/hora deve aparecer
- [ ] Selecionar "Rascunho"
- [ ] Campo de data/hora deve desaparecer
- [ ] Criar notícia agendada para futuro próximo (ex: daqui 5 min)

### Teste UI - Editar
- [ ] Acessar `admin/noticia-editar.php` de notícia existente
- [ ] Alterar status para "Agendado"
- [ ] Definir data futura
- [ ] Salvar
- [ ] Verificar que notícia não aparece no site ainda

### Teste Funcional
- [ ] Criar notícia com status "Rascunho"
- [ ] Confirmar que NÃO aparece no site
- [ ] Editar para status "Publicado"
- [ ] Confirmar que APARECE no site
- [ ] Editar para "Agendado" (data futura)
- [ ] Confirmar que NÃO aparece no site
- [ ] Aguardar data/hora definida
- [ ] Confirmar que APARECE no site automaticamente

**Status: [ ] PASSOU [ ] FALHOU**

---

## 3. Gestão de Assinaturas

### Teste Dashboard
- [ ] Acessar `admin/assinaturas.php`
- [ ] Verificar 4 cards de estatísticas (Total, Pendentes, Aprovadas, Canceladas)
- [ ] Números batem com dados reais

### Teste Filtros
- [ ] Testar filtro por status (Pendente, Aprovado, Cancelado)
- [ ] Testar filtro por plano
- [ ] Testar busca por nome
- [ ] Testar busca por CPF
- [ ] Testar busca por email
- [ ] Combinar filtros (status + plano + busca)

### Teste Paginação
- [ ] Se houver mais de 20 assinaturas, verificar paginação
- [ ] Clicar em "Próxima"
- [ ] Clicar em "Anterior"
- [ ] Números de página funcionam

### Teste Ações - Aprovar
- [ ] Encontrar assinatura pendente
- [ ] Clicar em "Aprovar"
- [ ] Confirmar ação
- [ ] Badge deve mudar para "Aprovado" (verde)
- [ ] Estatísticas devem atualizar

### Teste Ações - Cancelar
- [ ] Encontrar assinatura aprovada
- [ ] Clicar em "Cancelar"
- [ ] Confirmar ação
- [ ] Badge deve mudar para "Cancelado" (vermelho)

### Teste Detalhes
- [ ] Clicar em "Ver Detalhes" de qualquer assinatura
- [ ] Verificar todas informações exibidas:
  - [ ] Nome completo
  - [ ] CPF
  - [ ] Email
  - [ ] Telefone
  - [ ] Data de nascimento
  - [ ] Plano escolhido
  - [ ] Endereço (se preenchido)
  - [ ] Data da assinatura
- [ ] Testar botões de ação na página de detalhes
- [ ] Voltar para lista

**Status: [ ] PASSOU [ ] FALHOU**

---

## 4. Lazy Loading Avançado

### Teste Visual
- [ ] Abrir `index.php` no navegador
- [ ] Abrir DevTools > Network
- [ ] Recarregar página
- [ ] Verificar que imagens não são carregadas todas de uma vez
- [ ] Fazer scroll lento para baixo
- [ ] Observar skeleton screens (fundo cinza animado)
- [ ] Ver imagens "fade in" ao aparecer

### Teste Performance
```
DevTools > Network:
- [ ] Carregamento inicial < 2s
- [ ] Imagens carregam só quando visíveis
- [ ] Total de KB reduzido significativamente
```

### Teste Console
- [ ] Abrir DevTools > Console
- [ ] Não deve haver erros de JavaScript
- [ ] Deve mostrar: "LazyLoader initialized"

**Status: [ ] PASSOU [ ] FALHOU**

---

## 5. Compressão de Assets

### Verificar Arquivos Gerados
```powershell
# No terminal, verificar se existem:
dir assets/css/*.min.css
dir assets/js/*.min.js
```
- [ ] `style.min.css` existe
- [ ] `noticia.min.css` existe
- [ ] `historia.min.css` existe
- [ ] `script.min.js` existe
- [ ] `lazy-loader.min.js` existe

### Teste de Carregamento
```
DevTools > Network > Disable cache:
```
- [ ] `index.php` carrega `style.min.css` (não style.css)
- [ ] `index.php` carrega `script.min.js` (não script.js)
- [ ] `noticia.php` carrega `.min` versions
- [ ] `historia.html` carrega `.min` versions

### Teste de Tamanho
```
DevTools > Network > Size:
```
- [ ] `style.min.css` ≈ 42 KB (32% menor que original)
- [ ] `script.min.js` ≈ 8 KB (46% menor que original)
- [ ] Total da página reduzido em 30-40%

### Teste Funcional
- [ ] Site funciona normalmente com assets minificados
- [ ] Animações funcionam
- [ ] JavaScript interativo funciona
- [ ] Estilos aplicados corretamente

**Status: [ ] PASSOU [ ] FALHOU**

---

## 6. Acessibilidade WCAG

### Teste Skip Link
- [ ] Abrir `index.php`
- [ ] Pressionar `Tab` (primeira tecla)
- [ ] Link "Pular para o conteúdo principal" deve aparecer
- [ ] Pressionar `Enter`
- [ ] Deve pular para o conteúdo

### Teste Navegação por Teclado
- [ ] Abrir `index.php`
- [ ] **Sem usar mouse**, navegar apenas com `Tab`
- [ ] Deve passar por todos links/botões na ordem correta
- [ ] Focus indicator visível em cada elemento
- [ ] `Enter` ou `Space` ativa botões
- [ ] `Escape` fecha modais/menus (se houver)

### Teste ARIA Labels
```
DevTools > Elements > Search:
```
- [ ] Procurar por `aria-label` - deve encontrar vários
- [ ] Procurar por `role="banner"` no header
- [ ] Procurar por `role="main"` no conteúdo
- [ ] Procurar por `role="navigation"` nos menus
- [ ] Procurar por `role="contentinfo"` no footer
- [ ] Procurar por `aria-hidden="true"` nos ícones

### Teste com Screen Reader (Opcional)
Se tiver NVDA ou JAWS instalado:
- [ ] Ativar screen reader
- [ ] Navegar pelo site
- [ ] Confirmar que lê corretamente:
  - [ ] Títulos de seção
  - [ ] Links com descrição
  - [ ] Botões com função
  - [ ] Imagens com alt text

### Teste de Contraste
```
DevTools > Lighthouse > Accessibility:
```
- [ ] Executar audit
- [ ] Score deve ser > 90
- [ ] Sem erros de contraste de cor

### Teste Manual de Cores
- [ ] Texto preto/cinza escuro em fundo branco = OK
- [ ] Texto branco em fundo azul (#111D69) = OK (contraste 4.5:1)
- [ ] Texto branco em fundo vermelho (#eb3835) = OK (contraste 4.5:1)

**Status: [ ] PASSOU [ ] FALHOU**

---

## 7. Testes de Integração

### Cache Invalidation
- [ ] Criar novo jogador no admin
- [ ] Verificar que aparece imediatamente em `index.php#jogadores`
- [ ] Editar jogador
- [ ] Verificar mudanças refletidas no site
- [ ] Criar novo plano
- [ ] Verificar que aparece em `index.php#planos`

### Cross-Browser
- [ ] Testar em **Chrome** (últimas 2 versões)
- [ ] Testar em **Firefox** (últimas 2 versões)
- [ ] Testar em **Edge** (últimas 2 versões)
- [ ] Testar em **Safari** (se disponível)

### Mobile Responsiveness
- [ ] Abrir `index.php` em mobile (DevTools > Device Toolbar)
- [ ] Testar iPhone 12/13/14
- [ ] Testar Samsung Galaxy S21
- [ ] Menu hamburguer funciona
- [ ] Botões são clicáveis (44x44px mínimo)
- [ ] Texto legível sem zoom
- [ ] Imagens se ajustam

### Performance Geral
```
DevTools > Lighthouse > Performance:
```
- [ ] Executar audit
- [ ] Performance score > 80
- [ ] FCP (First Contentful Paint) < 1.8s
- [ ] LCP (Largest Contentful Paint) < 2.5s
- [ ] TBT (Total Blocking Time) < 300ms
- [ ] CLS (Cumulative Layout Shift) < 0.1

**Status: [ ] PASSOU [ ] FALHOU**

---

## 8. Testes de Segurança

### SQL Injection
- [ ] Tentar `noticia.php?id=1' OR '1'='1`
- [ ] Deve redirecionar ou erro, não expor dados

### XSS (Cross-Site Scripting)
- [ ] Criar notícia com `<script>alert('XSS')</script>` no título
- [ ] Salvar
- [ ] Ver no site - não deve executar script

### CSRF
- [ ] Verificar que formulários têm proteção CSRF
- [ ] Tokens presentes em forms sensíveis

**Status: [ ] PASSOU [ ] FALHOU**

---

## 📊 Resumo Final

| Funcionalidade | Status | Observações |
|----------------|--------|-------------|
| Editor WYSIWYG | [ ] ✅ [ ] ❌ | |
| Agendamento | [ ] ✅ [ ] ❌ | |
| Gestão Assinaturas | [ ] ✅ [ ] ❌ | |
| Lazy Loading | [ ] ✅ [ ] ❌ | |
| Compressão Assets | [ ] ✅ [ ] ❌ | |
| Acessibilidade | [ ] ✅ [ ] ❌ | |
| Integração | [ ] ✅ [ ] ❌ | |
| Segurança | [ ] ✅ [ ] ❌ | |

---

## 🐛 Bugs Encontrados

| # | Descrição | Severidade | Status |
|---|-----------|------------|--------|
| 1 | | [ ] Crítico [ ] Alto [ ] Médio [ ] Baixo | [ ] Aberto [ ] Corrigido |
| 2 | | [ ] Crítico [ ] Alto [ ] Médio [ ] Baixo | [ ] Aberto [ ] Corrigido |
| 3 | | [ ] Crítico [ ] Alto [ ] Médio [ ] Baixo | [ ] Aberto [ ] Corrigido |

---

## ✅ Aprovação Final

- [ ] Todos os testes passaram
- [ ] Nenhum bug crítico encontrado
- [ ] Performance aceitável
- [ ] Acessibilidade validada
- [ ] Pronto para produção

**Testado por:** ___________________________  
**Data:** _____ / _____ / _____  
**Assinatura:** ___________________________

---

**APAFUT - Caxias do Sul**  
**Versão: 2.0**  
**Data: 12 de dezembro de 2025**
