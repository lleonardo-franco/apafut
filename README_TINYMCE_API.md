# 🔑 Configuração da API Key do TinyMCE

## Obter sua API Key Gratuita

O TinyMCE requer uma API key para funcionar em produção. A chave gratuita permite até **1.000 carregamentos/mês** sem custo.

### Passo a Passo:

1. **Cadastre-se gratuitamente:**
   - Acesse: https://www.tiny.cloud/auth/signup/
   - Preencha nome, email e senha
   - Confirme o email

2. **Obtenha sua API Key:**
   - Faça login em: https://www.tiny.cloud/my-account/dashboard/
   - Na página "Dashboard", copie a **API Key** exibida
   - Exemplo: `qagffr3pkuv17a8on1afax661irst1hbr4e6tbv888sz91jc`

3. **Configure no projeto:**
   - Abra `admin/noticia-criar.php`
   - Encontre a linha:
   ```html
   <script src="https://cdn.tiny.cloud/1/SUA_API_KEY_AQUI/tinymce/6/tinymce.min.js"
   ```
   - Substitua `SUA_API_KEY_AQUI` pela sua chave real
   
   - Faça o mesmo em `admin/noticia-editar.php`

4. **Teste:**
   - Acesse o admin: `admin/noticia-criar.php`
   - O editor deve carregar sem avisos
   - Se aparecer mensagem de domínio não aprovado, adicione seu domínio no dashboard do TinyMCE

---

## Configuração Avançada (Opcional)

### Adicionar Domínio Aprovado

Se você ver o aviso "This domain is not registered", adicione seu domínio:

1. Acesse: https://www.tiny.cloud/my-account/domains/
2. Clique em **Add Domain**
3. Digite seu domínio (ex: `apafut.com.br` ou `localhost` para desenvolvimento)
4. Clique em **Add Domain**

### Limites da Conta Gratuita

- ✅ **1.000 carregamentos/mês** (renovado mensalmente)
- ✅ **Todos os plugins incluídos**
- ✅ **Uso comercial permitido**
- ✅ **Sem marca d'água**
- ✅ **Suporte por email**

Se exceder 1.000 carregamentos:
- O editor mostrará um aviso mas continuará funcionando
- Considere upgrade para plano pago se necessário

---

## Solução de Problemas

### Editor não carrega
- ✅ Verifique se a API key está correta (40 caracteres)
- ✅ Verifique conexão com internet
- ✅ Limpe cache do navegador

### Aviso "Domain not approved"
- ✅ Adicione o domínio no painel do TinyMCE
- ✅ Para localhost, adicione `localhost` e `127.0.0.1`

### Limite excedido
- ✅ Aguarde renovação mensal
- ✅ Considere plano pago (a partir de $49/mês)
- ✅ Use editor alternativo temporariamente

---

## Alternativas (Sem API Key)

Se não quiser usar API key, considere:

### 1. TinyMCE Self-Hosted (mais complexo)
```html
<!-- Baixar TinyMCE e hospedar localmente -->
<script src="assets/tinymce/tinymce.min.js"></script>
```

### 2. Summernote (mais simples)
```html
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
<script>
  $('#conteudo').summernote({ height: 500 });
</script>
```

### 3. CKEditor (alternativa robusta)
```html
<script src="https://cdn.ckeditor.com/ckeditor5/41.0.0/classic/ckeditor.js"></script>
<script>
  ClassicEditor.create(document.querySelector('#conteudo'));
</script>
```

---

## Configuração Atual

**Status:** ⚠️ API Key precisa ser configurada

**Arquivos que usam TinyMCE:**
- `admin/noticia-criar.php`
- `admin/noticia-editar.php`

**Linha para alterar:**
```html
<script src="https://cdn.tiny.cloud/1/SUA_API_KEY_AQUI/tinymce/6/tinymce.min.js"></script>
                                   ^^^^^^^^^^^^^^^^^^
                                   Substitua aqui!
```

---

## Checklist de Configuração

- [ ] Criar conta no TinyMCE Cloud
- [ ] Obter API Key
- [ ] Substituir `SUA_API_KEY_AQUI` em `noticia-criar.php`
- [ ] Substituir `SUA_API_KEY_AQUI` em `noticia-editar.php`
- [ ] Adicionar domínio aprovado (se necessário)
- [ ] Testar editor no admin
- [ ] Confirmar que não há avisos no console

---

**APAFUT - Caxias do Sul**  
**Atualizado: 12 de dezembro de 2025**
