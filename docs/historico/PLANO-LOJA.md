# Plano de Execução — Finalizar a Loja

**Status: concluído.** As 6 sessões foram executadas — catálogo com filtros, detalhe do produto, carrinho, checkout simulado e histórico no perfil estão no ar, com testes automatizados cobrindo os fluxos principais. O item 5 da Sessão 6 (fotos reais de produto) ficou de fora — era opcional e exigiria imagens que não existem no repositório.

Escopo combinado: catálogo com filtros, página de detalhe do produto, carrinho de compras (sessão), checkout simulado (sem gateway de pagamento — confirma o pedido do mesmo jeito que a reserva de quadra confirma na hora) e histórico de pedidos no perfil. Sem CRUD de produtos para dono/admin (fica para uma v2).

Cada sessão abaixo é **um prompt independente** — copie o bloco inteiro e cole numa conversa nova do Claude Code. Cada um assume que o anterior já foi mergeado. Marque o checkbox conforme for terminando.

## O que já existe (não recriar)

- `resources/views/loja.blade.php` — vitrine estática, query inline, sem filtro. Vai ser substituída por um componente Livewire.
- `app/Models/Produto.php` — só tem `nome`, `descricao`, `preco`, `imagem`.
- `database/seeders/DemoSeeder.php` — cadastra ~8 produtos fixos (linha ~169).
- CSS em `public/css/style.css` (a partir da linha ~613, seção "Loja") **já tem classes prontas e não usadas**: `.produto-btn-add` (botão adicionar ao carrinho), `.produto-btn-esgotado` / `.produto-overlay-esgotado` (produto sem estoque) e `.produto-btn-favorito` (favoritar — fora do escopo atual, deixar para depois, igual a aba "Quadras Favoritas" do perfil que já está marcada como trabalho futuro). Ou seja, o visual já foi pensado para ter estoque — vale usar isso em vez de inventar um padrão novo.
- Padrão de referência a seguir em tudo: `app/Livewire/Quadras/Listagem.php` + `resources/views/livewire/quadras/listagem.blade.php` (filtros com `#[Computed]` e `wire:model.live`, cards com `.card-quadra`, seleção inline tipo `wire:click="selecionarX($id)"`, confirmação sem gateway de pagamento) e `app/Livewire/Perfil/MinhasReservas.php` (histórico do usuário).

## Cronograma sugerido

Sem prazo fixo definido — organizado em 6 sessões que podem ser feitas uma por dia ou agrupadas (ex.: 1+2 num dia, 3+4 no outro). Ordem importa, é sequencial.

| # | Sessão | Entrega | Tempo estimado |
|---|--------|---------|-----------------|
| 1 | Banco de dados e modelos | migrations, enum `PedidoStatus`, models `Pedido`/`ItemPedido`, seeder atualizado | ~1h |
| 2 | Vitrine + detalhe do produto | `/loja` em Livewire com filtros, `/loja/{produto}` com estoque/esgotado | ~1h30 |
| 3 | Carrinho de compras | helper de sessão + página `/carrinho` + contador no sub-nav | ~1h30 |
| 4 | Checkout simulado | finalizar pedido, baixa de estoque, página de confirmação | ~1h |
| 5 | Histórico no perfil | aba "Meus Pedidos" ao lado de "Minhas Reservas" | ~45min |
| 6 | Polimento + roteiro de apresentação | ajustes visuais, teste manual ponta a ponta, atualizar `ROTEIRO-APRESENTACAO.md` | ~45min |

Depois de cada sessão: `php artisan migrate:fresh && php artisan db:seed --class=DemoSeeder` e testar manualmente no navegador antes de ir para a próxima.

---

## [x] Sessão 1 — Banco de dados e modelos

```
Estou finalizando a "loja" do projeto AlugaQuadra (Laravel + Livewire + Bootstrap).
Preciso da base de dados para suportar categoria/estoque de produtos e pedidos.

Contexto do padrão do projeto:
- app/Models/Reserva.php e app/Enums/ReservaStatus.php são o modelo a espelhar para
  o novo domínio de pedidos (mesmo estilo de fillable, casts, enum com label()).
- app/Models/Produto.php hoje só tem nome, descricao, preco, imagem.
- database/seeders/DemoSeeder.php cadastra produtos perto da linha 169 (array de
  ['nome' => ..., 'descricao' => ..., 'preco' => ...]).
- public/css/style.css (linha ~613, seção "Loja") já tem CSS pronto para um estado
  "esgotado" (.produto-btn-esgotado, .produto-overlay-esgotado), então o produto
  precisa de um campo de estoque.

Faça:
1. Migration adicionando à tabela `produtos`: `categoria` (string, nullable) e
   `estoque` (unsignedInteger, default 0).
2. Migration criando `pedidos`: id, user_id (FK para users, cascadeOnDelete), status
   (string), total (decimal 10,2), timestamps.
3. Migration criando `itens_pedido`: id, pedido_id (FK cascadeOnDelete), produto_id
   (FK), quantidade (unsignedInteger), preco_unitario (decimal 8,2), timestamps.
4. Enum app/Enums/PedidoStatus.php com casos Pendente, Confirmado, Cancelado e
   método label(), no mesmo estilo de ReservaStatus.
5. Model app/Models/Pedido.php: fillable, cast de status para o enum, cast de total
   para decimal:2, belongsTo(User::class), hasMany(ItemPedido::class).
6. Model app/Models/ItemPedido.php: fillable, belongsTo(Pedido::class),
   belongsTo(Produto::class).
7. Adicionar em app/Models/User.php um relation pedidos(): HasMany, no mesmo padrão
   de reservas() que já existe lá.
8. Atualizar app/Models/Produto.php: incluir categoria e estoque no fillable, cast
   estoque para integer, e um accessor `disponivel` (bool) que é `estoque > 0`.
9. Atualizar database/factories/ProdutoFactory.php para gerar categoria e estoque
   também.
10. Atualizar o array de produtos em DemoSeeder.php (~linha 169): adicionar
    'categoria' e 'estoque' em cada item, usando categorias que façam sentido pros
    produtos existentes (ex.: "Vestuário", "Calçados", "Acessórios", "Hidratação").
    Deixe pelo menos 1 produto com estoque = 0 para dar pra testar o estado
    esgotado depois.

Não crie o carrinho nem telas ainda — só a base de dados e os models. Rode
`php artisan migrate:fresh && php artisan db:seed --class=DemoSeeder` no final para
confirmar que roda sem erro, e rode `vendor/bin/pint` se o projeto usa (confirme
olhando pint.json).
```

## [x] Sessão 2 — Vitrine da loja + página de detalhe do produto

```
Continuando a loja do AlugaQuadra. A Sessão 1 já adicionou `categoria` e `estoque`
em produtos, e os models Pedido/ItemPedido (não usados ainda nesta sessão).

Objetivo: substituir resources/views/loja.blade.php (hoje é uma query inline sem
filtro) por um componente Livewire de listagem com filtros, no mesmo padrão de
app/Livewire/Quadras/Listagem.php + resources/views/livewire/quadras/listagem.blade.php
(props públicas de filtro, #[Computed] pra query, wire:model.live nos selects).
Depois criar a página de detalhe do produto.

Faça:
1. app/Livewire/Loja/Listagem.php: propriedades públicas `categoria` (string) e
   `busca` (string) para filtro. #[Computed] `produtos()` filtrando por categoria
   exata e por `nome`/`descricao` com LIKE no `busca`, orderBy nome.
   #[Computed] `categorias()` retornando categorias distintas (mesmo padrão de
   `cidades()`/`bairros()` em Quadras/Listagem.php, com distinct()->pluck()).
2. resources/views/livewire/loja/listagem.blade.php: barra de filtro (categoria em
   select, busca em input de texto com wire:model.live.debounce.300ms), grid de
   cards reaproveitando as classes .produto-card, .produto-img-container,
   .produto-titulo que já existem no CSS (mesma estrutura visual do
   loja.blade.php atual, não invente um layout novo). Cada card:
   - se `$produto->disponivel`, um link "Ver produto" pra rota de detalhe;
   - se não, mostrar o overlay .produto-overlay-esgotado e desabilitar o link,
     com um botão .produto-btn-esgotado (texto "Esgotado", disabled).
3. Trocar a rota `/loja` em routes/web.php para apontar pro componente Livewire
   full-page (Route::get('/loja', \App\Livewire\Loja\Listagem::class)->name('loja'))
   OU manter uma view fina resources/views/loja.blade.php que só faz @extends +
   @include('partials.sub-nav') + <livewire:loja.listagem /> — decida olhando como
   quadras.blade.php faz isso (@extends('layouts.bootstrap') + sub-nav +
   <livewire:quadras.listagem /> dentro de um .container) e siga o mesmo padrão
   exato, não o outro.
4. Nova rota GET /loja/{produto} chamada `loja.produto`, apontando pra
   app/Livewire/Loja/Detalhe.php (componente Livewire recebendo Produto via route
   model binding no mount, com abort 404 se não existir).
5. resources/views/livewire/loja/detalhe.blade.php: imagem grande, nome,
   descrição completa, preço, categoria, um seletor de quantidade (input number,
   min 1, max = estoque do produto), e um botão "Adicionar ao carrinho" usando a
   classe .produto-btn-add. Se `estoque === 0`, mostrar o estado esgotado (mesmo
   visual do card) e não mostrar o seletor de quantidade. NÃO implemente a lógica
   de adicionar ao carrinho ainda (isso é a Sessão 3) — pode deixar o wire:click
   apontando pra um método `adicionarAoCarrinho()` vazio/com TODO por enquanto, ou
   melhor: deixe o botão sem wire:click ainda e adicione um comentário curto no
   componente Livewire indicando que a Sessão 3 vai implementar.
6. No sub-nav (resources/views/partials/sub-nav.blade.php), o link "Loja" já
   existe — não mexer nele.

Teste no navegador: acesse /loja, filtre por categoria e por busca, clique num
produto disponível e veja a página de detalhe; confira que o produto com
estoque = 0 aparece esgotado tanto no grid quanto (se acessado direto pela URL)
na página de detalhe.
```

## [x] Sessão 3 — Carrinho de compras

```
Continuando a loja do AlugaQuadra. Sessões 1 e 2 já criaram categoria/estoque nos
produtos e as páginas /loja e /loja/{produto} (esta última com um botão
"Adicionar ao carrinho" ainda sem lógica, em
app/Livewire/Loja/Detalhe.php).

Não existe carrinho persistido no banco (nem deveria — o carrinho é só um
rascunho antes do pedido). Objetivo desta sessão: um carrinho guardado na sessão
do Laravel, reaproveitado em 3 lugares (detalhe do produto, página do carrinho,
contador no menu).

Faça:
1. Criar app/Support/Carrinho.php — uma classe com métodos estáticos que leem e
   escrevem um array na sessão (chave 'carrinho', formato [produto_id => quantidade]):
   - adicionar(int $produtoId, int $quantidade = 1): respeita o estoque do
     produto (não deixa passar do disponível somando o que já está no carrinho).
   - atualizarQuantidade(int $produtoId, int $quantidade): idem, remove do
     carrinho se quantidade <= 0.
   - remover(int $produtoId).
   - itens(): Collection de objetos/arrays com produto (Eloquent model),
     quantidade e subtotal, carregando os Produto::whereIn(...) de uma vez
     (não fazer 1 query por item).
   - total(): soma dos subtotais.
   - quantidadeTotal(): soma das quantidades (pro badge do menu).
   - limpar(): esvazia a sessão (vai ser usado no checkout da Sessão 4).
2. Em app/Livewire/Loja/Detalhe.php: implementar o wire:click do botão
   "Adicionar ao carrinho" chamando Carrinho::adicionar(), com uma mensagem de
   sucesso inline (mesmo padrão da propriedade `$mensagemSucesso` em
   Quadras/Listagem.php). Se o usuário não estiver logado, siga o padrão de
   Salas/Listagem.php (propriedade tipo `precisaLogin`) e mostre um link pra
   /login em vez de deixar adicionar ao carrinho anônimo — carrinho exige login
   porque o checkout vai criar um Pedido vinculado ao user_id.
3. Nova rota GET /carrinho chamada `carrinho.index`, protegida por
   middleware(['auth']), apontando pra app/Livewire/Loja/Carrinho.php.
4. resources/views/livewire/loja/carrinho.blade.php: lista os itens
   (Carrinho::itens()), cada linha com nome do produto, preço unitário, um input
   de quantidade editável (wire:model.live disparando `atualizarQuantidade`),
   subtotal, botão remover. Rodapé com o total geral e um botão "Finalizar
   Pedido" (ainda sem ação — isso é a Sessão 4, pode deixar wire:click num método
   vazio/TODO). Estado vazio: "Seu carrinho está vazio" com link pra /loja, igual
   ao padrão de empty state usado em @empty nas outras listagens.
5. No sub-nav (resources/views/partials/sub-nav.blade.php), adicionar um link
   pro carrinho ao lado do link "Loja" já existente, mesmo estilo
   (.sub-nav-link, ícone bi-cart), com um badge mostrando
   \App\Support\Carrinho::quantidadeTotal() quando maior que zero. Como o
   sub-nav é um Blade partial comum (não Livewire), o badge só atualiza no
   próximo carregamento de página — isso é esperado e aceitável, não precisa
   virar Livewire só por causa disso.

Teste: adicione produtos ao carrinho a partir da página de detalhe, confira que
o badge do menu atualiza ao navegar, ajuste quantidades e remova itens na
página /carrinho, e confirme que tentar adicionar mais que o estoque disponível
é bloqueado.
```

## [x] Sessão 4 — Checkout simulado

```
Continuando a loja do AlugaQuadra. Sessões 1-3 já criaram o banco (Pedido,
ItemPedido, PedidoStatus), a vitrine, a página de detalhe e o carrinho de sessão
(app/Support/Carrinho.php), com um botão "Finalizar Pedido" em
app/Livewire/Loja/Carrinho.php ainda sem lógica.

Não existe gateway de pagamento no projeto e não é objetivo criar um — o
"checkout" aqui confirma o pedido na hora, do mesmo jeito que
Quadras/Listagem::reservar() confirma uma reserva sem cobrança real. Siga esse
mesmo espírito: sem tela de cartão de crédito, sem etapa de "processando
pagamento" fake.

Faça:
1. Em app/Livewire/Loja/Carrinho.php, implementar `finalizarPedido()`:
   - abort_unless(auth()->check(), 403) (a rota /carrinho já exige auth, mas o
     padrão do projeto reforça isso no método também — veja reservar() em
     Quadras/Listagem.php).
   - Se o carrinho estiver vazio, não faz nada (ou erro simples).
   - Dentro de uma transação (DB::transaction): cria um Pedido (user_id, status
     Confirmado, total = Carrinho::total()), cria um ItemPedido por item
     (produto_id, quantidade, preco_unitario = preço do produto no momento —
     não o preço atual, congela o valor), decrementa o estoque de cada Produto
     (Produto::where('id', ...)->decrement('estoque', $quantidade) — não deixar
     ficar negativo, se a validação de estoque já acontece no
     Carrinho::adicionar/atualizarQuantidade da Sessão 3 isso é só uma garantia
     a mais).
   - Depois do sucesso, Carrinho::limpar() e redirect para uma página de
     confirmação (rota nova, ver item 3).
2. Nova rota GET /loja/pedido/{pedido} chamada `loja.pedido.confirmacao`,
   protegida por auth, apontando pra uma view (pode ser Livewire ou Blade
   simples — decida pelo que for mais direto) que só mostra o pedido se
   `$pedido->user_id === auth()->id()` (senão abort 403). Mostrar: itens do
   pedido com quantidade e preço unitário congelado, total, status, data, e um
   botão/link "Voltar para a loja".
3. resources/views/livewire/loja/carrinho.blade.php: ligar o botão "Finalizar
   Pedido" no wire:click="finalizarPedido", com wire:loading.attr="disabled"
   (mesmo padrão usado no botão "Confirmar Reserva" de
   livewire/quadras/listagem.blade.php).

Teste: monte um carrinho com 2-3 produtos diferentes, finalize o pedido, confira
que a página de confirmação mostra os itens certos e o total bate, que o
estoque dos produtos comprados diminuiu (visite /loja de novo), e que o
carrinho ficou vazio depois. Tente acessar a confirmação de um pedido de outro
usuário trocando o {pedido} na URL e confirme que dá 403.
```

## [x] Sessão 5 — Histórico de pedidos no perfil

```
Continuando a loja do AlugaQuadra. Pedido e ItemPedido já existem (Sessão 1) e o
checkout já cria pedidos de verdade (Sessão 4).

Objetivo: uma aba "Meus Pedidos" no perfil, ao lado de "Minhas Reservas", no
mesmo padrão. Veja resources/views/perfil.blade.php (a lista de abas
custom-tabs, com os botões nav-link e os tab-pane correspondentes) e
app/Livewire/Perfil/MinhasReservas.php + resources/views/livewire/perfil/minhas-reservas.blade.php
como referência direta de estilo e estrutura.

Faça:
1. app/Livewire/Perfil/MeusPedidos.php: #[Computed] `pedidos()` retornando
   Auth::user()->pedidos()->with('itens.produto')->latest()->get() (ajuste o
   nome da relation hasMany de itens em Pedido conforme foi nomeada na Sessão 1
   — confirme lendo app/Models/Pedido.php antes de assumir o nome).
2. resources/views/livewire/perfil/meus-pedidos.blade.php: lista dos pedidos,
   cada um mostrando data, status (com um badge — pode usar o mesmo estilo de
   badge de status já usado em algum lugar do painel, procure por "badge" em
   resources/views/livewire/painel/reservas/listagem.blade.php se existir esse
   padrão), itens comprados (nome do produto + quantidade + preço unitário) e o
   total. Empty state: "Você ainda não fez nenhum pedido" com link pra /loja.
3. Em resources/views/perfil.blade.php: adicionar um novo <li>/<button> na lista
   de abas (custom-tabs) chamado "Meus Pedidos" com um ícone bi-bag-check, e o
   tab-pane correspondente com <livewire:perfil.meus-pedidos /> dentro de um
   card, no mesmo formato dos outros tab-panes (card border-0 shadow-sm,
   border-radius 20px). Posicione logo depois da aba "Minhas Reservas".

Teste: faça um pedido pela loja, vá em /perfil, clique na aba "Meus Pedidos" e
confirme que ele aparece com os itens e o total certos.
```

## [x] Sessão 6 — Polimento e roteiro de apresentação

```
Última sessão da loja do AlugaQuadra. Catálogo, detalhe, carrinho, checkout
simulado e histórico no perfil já existem (Sessões 1-5). Esta sessão é só
revisão, não features novas.

Faça:
1. Percorra manualmente o fluxo completo (login como jogador@demo.com, se ainda
   existir esse usuário de demo — confira em DemoSeeder.php) : /loja com
   filtros, abrir um produto, adicionar ao carrinho, editar quantidade no
   carrinho, finalizar pedido, ver a confirmação, ver em /perfil > Meus
   Pedidos. Anote qualquer inconsistência visual (cores fora do padrão laranja
   do projeto, cards sem o mesmo raio de borda/sombra dos outros, espaçamento
   diferente do resto do site) e corrija reaproveitando classes já existentes
   em public/css/style.css antes de criar CSS novo.
2. Confirme que um usuário deslogado consegue navegar /loja e ver os produtos,
   mas é redirecionado/avisado para logar ao tentar adicionar ao carrinho ou
   acessar /carrinho diretamente (sem estourar erro 500).
3. Rode `vendor/bin/pint` (se configurado — confira pint.json) e os testes
   existentes (veja como rodar em phpunit.xml / composer.json, provavelmente
   `php artisan test` ou `composer test`) para garantir que nada quebrou nas
   telas de quadras/salas/perfil por causa das mudanças no sub-nav ou no
   perfil.blade.php.
4. Atualize ROTEIRO-APRESENTACAO.md, seção "5. Loja" (linha ~59): hoje ela diz
   que "carrinho e pagamento são trabalho futuro" — isso não é mais verdade.
   Reescreva o passo pra refletir o fluxo real: abrir a loja, filtrar/buscar um
   produto, abrir o detalhe, adicionar ao carrinho, ir em /carrinho, finalizar
   o pedido, ver a confirmação. Deixe claro (pode ser uma frase curta) que o
   pagamento continua sendo simulado — o pedido é confirmado na hora, sem
   cobrança real, do mesmo jeito que a reserva de quadra. Também adicione um
   passo curto mostrando a aba "Meus Pedidos" no perfil, perto de onde já fala
   de "Minhas Reservas" (passo 6 do roteiro).
5. Se sobrou tempo: considere se vale adicionar 1-2 produtos a mais no
   DemoSeeder com fotos reais (veja como Quadra faz isso via QuadraFoto /
   Storage, em app/Livewire/Painel/Quadras/Formulario.php) — hoje o `imagem` de
   Produto é só uma string de path, então basta apontar pra um arquivo em
   public/imagens/. Isso é opcional, só ajuda a apresentação ficar mais bonita.
```

---

## Coisas que ficam de fora de propósito (não implementar sem decidir de novo)

- Gateway de pagamento real (Stripe/Mercado Pago/PIX) — fora do escopo de TCC, mencionar como trabalho futuro se perguntarem na banca.
- CRUD de produtos pelo dono/admin — hoje só existe via seeder/tinker. Se quiser isso depois, o padrão a copiar é `app/Livewire/Painel/Quadras/Formulario.php`.
- Botão de favoritar produto — o CSS (.produto-btn-favorito) já existe mas a funcionalidade não; mesma situação da aba "Quadras Favoritas" do perfil, que já está marcada como não implementada.
- Cancelamento de pedido pelo usuário — hoje o enum PedidoStatus tem "Cancelada" mas nada usa esse valor além do Confirmado padrão do checkout.
