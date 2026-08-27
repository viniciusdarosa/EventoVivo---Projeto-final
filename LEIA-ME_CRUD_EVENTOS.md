# CRUD de Eventos — EventoVivo

Arquivos novos/alterados neste pacote (copie mantendo a mesma estrutura de pastas do seu projeto):

```
Telas/CadastrarEvento.php          (novo)
Telas/EditarEvento.php             (novo)
Telas/ExcluirEvento.php            (novo)
Telas/CRUD_Eventos.php             (substitui o existente — agora é a listagem funcional)
Telas/Componentes/funcoes_eventos.php  (novo — funções auxiliares usadas pelas 4 telas acima)
Css/crud_eventos.css               (mesmo arquivo, com estilos novos ADICIONADOS ao final)
uploads/eventos/.htaccess          (novo — bloqueia execução de PHP dentro dessa pasta)
sql/eventos.sql                    (novo — cria as tabelas eventos e categorias_eventos)
```

## Passo a passo

1. **Banco de dados**: rode `sql/eventos.sql` no banco `eventovivo`. Ele cria
   `categorias_eventos` (com algumas categorias iniciais) e `eventos`.
   A FK `eventos.usuario_id` assume uma tabela `usuario` com coluna
   `id_usuario`. Se o nome estiver diferente na sua tabela de usuários,
   ajuste essa linha do SQL antes de rodar.

2. **Copie os arquivos** para as pastas correspondentes do seu projeto,
   sobrescrevendo `Telas/CRUD_Eventos.php` e `Css/crud_eventos.css`.

3. **Sessão do usuário**: as 4 telas exigem `$_SESSION['id_usuario']`
   (é o organizador do evento). Como o `Login.php` do projeto ainda é só
   a tela (sem processamento em PHP), essas páginas vão redirecionar
   para `Login.php` até que o login grave esse valor na sessão, por
   exemplo:
   ```php
   session_start();
   $_SESSION['id_usuario'] = $linha['id_usuario']; // depois de validar e-mail/senha
   ```
   Para testar o CRUD isoladamente antes disso, você pode criar um
   usuário de teste direto no banco e, temporariamente, forçar
   `$_SESSION['id_usuario'] = 1;` no início de uma das páginas — só não
   esqueça de remover depois.

## Decisões tomadas (e por quê)

- **mysqli, não mysql\_\***: seu `config/conexao.php` já usa `new mysqli(...)`
  e o ambiente é EasyPHP **5.3.9** (não 5.2.0), que suporta mysqli, array
  curto, etc. Por isso o CRUD foi feito em mysqli, com **prepared
  statements** (`->prepare()` / `bind_param()`), que é mais seguro contra
  SQL injection do que concatenar strings escapadas manualmente — e é
  mais fácil de defender numa banca.

- **Upload de imagem**: valida tipo MIME, extensão do nome do arquivo
  e também os "magic bytes" do arquivo (via `getimagesize()`), limite de
  2MB, nome do arquivo trocado por `uniqid('evento_') . '.' . $extensao`
  (evita colisão e path traversal), salvo em `uploads/eventos/`. Incluí
  um `.htaccess` nessa pasta para impedir que um `.php` enviado ali seja
  executado.

- **Exclusão de evento** só aceita POST (o botão "Excluir" já envia um
  formulário, com `confirm()` em JavaScript antes), reduzindo risco de
  exclusão acidental via link.

- **Placeholder de imagem**: quando o evento não tem `imagem_capa`, uso
  um SVG embutido (data URI) em vez de exigir um arquivo de imagem extra
  no projeto — segue o mesmo estilo que já era usado no favicon do
  `header.php`.

## Um detalhe pré-existente do projeto (não fiz nada novo aqui)

`Telas/CRUD_Eventos.php` e `Telas/Evento.php` (arquivos originais) já
abrem `<!DOCTYPE html><html><head>...</head>` e, em seguida, incluem
`Componentes/header.php`, que **também** abre `<!DOCTYPE html><html><head>...<body>`.
Ou seja, a página acaba tendo duas tags `<html>`/`<head>`/`<body>`
aninhadas — mantive esse mesmo padrão nas telas novas para ficar
consistente com o que já existia, mas vale considerar limpar isso em
algum momento (o navegador tolera, mas não é HTML válido).
