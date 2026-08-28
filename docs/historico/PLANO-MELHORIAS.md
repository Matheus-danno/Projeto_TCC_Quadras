# Plano de Execução — Melhorias e Finalização (AlugaQuadra)

Documento gerado a partir de uma revisão completa do projeto em **26/08/2026**,
com uma segunda passada sob a ótica de **TCC** (o que uma banca cobra além de
"o código funciona": LGPD, documentação de engenharia, validações de domínio,
metodologia de testes).

Mesma mecânica do `PLANO-LOJA.md`: cada sessão abaixo é **um prompt independente** —
copie o bloco inteiro e cole numa conversa nova do Claude Code. Cada sessão assume
que a anterior já foi commitada. Marque o checkbox conforme terminar.

O plano tem **duas trilhas**:

- **Trilha 1 — Código (Sessões 0–8):** dívidas técnicas e robustez.
- **Trilha 2 — TCC / monografia (Sessões A–E):** artefatos acadêmicos e conformidade.
  Depende só da Sessão 0 estar feita; pode correr em paralelo com a Trilha 1.

---

## 1. Diagnóstico — estado atual

### O que já está bom

- **Laravel 12 + Livewire 4 + Fortify + Pest.** 178 testes passando (528 asserções), `pint` limpo.
- Domínio modelado: `Quadra`, `Reserva`, `Sala`, `ParticipacaoSala`, `Produto`, `Pedido`, `ItemPedido`, `QuadraFoto`.
- Fluxos completos: cadastro (jogador e dono), reserva de quadra com detecção de conflito de horário,
  criação de sala/partida (gera reserva vinculada), entrada em sala com pagamento simulado (PIX/cartão),
  loja com catálogo/carrinho/checkout simulado/histórico, painel do dono (dashboard, quadras, reservas,
  financeiro, agendamento manual), área de admin (listagens), perfil (minhas reservas / meus pedidos).
- `App\Services\Overpass\*` — integração com a Overpass API (OpenStreetMap) bem feita: retry, timeout
  escalonado, cache, exceção própria, teste unitário.
- Papéis (`Jogador`, `DonoQuadra`, `Admin`) com middleware `role:` e `Policy` para `Quadra` e `Reserva`.

### Problemas encontrados (ordenados por gravidade)

| # | Gravidade | Problema |
|---|-----------|----------|
| A | **Crítico** | **Trabalho não versionado.** Dezenas de arquivos novos sem `git add` (Admin, Painel, Loja, Overpass, Policies, Middleware, ~10 migrations) e 45 arquivos modificados sem commit. O último commit não reflete o estado real do projeto. Há um arquivo lixo `NUL` (artefato de redirecionamento no Windows) na raiz. Trabalho feito direto na branch `main`. Um `checkout` errado apaga meses de trabalho. |
| B | **Alto** | **Concorrência em reservas.** `Quadras\Listagem::reservar()` e `Salas\Criar::criar()` fazem *verifica-depois-insere* sem transação, sem lock e sem constraint única no banco. Duas requisições simultâneas conseguem reservar o mesmo horário. |
| C | **Alto** | **Concorrência em salas e loja.** `Salas\Pagamento::confirmarPagamento()` checa `count() >= max_participantes` e depois faz `attach()` fora de transação → sala estoura a capacidade. No checkout da loja (`Loja\Carrinho::finalizarPedido()`), se o `decrement` guardado por `where('estoque','>=',qty)` falhar, o `Pedido` e os `ItemPedido` são criados mesmo assim, sem rollback e sem aviso ao usuário. |
| D | **Alto** | **Localização errada.** `config/app.php`: `locale = en`, `faker_locale = en_US`, `timezone = UTC`. O app é 100% pt-BR e usa `translatedFormat('d \d\e F \d\e Y')` — os meses saem em inglês ("d de August de 2026"). |
| E | **Médio** | **Sem README.** Só existem `ROTEIRO-APRESENTACAO.md` e `PLANO-LOJA.md`. Um repositório de TCC precisa de README com stack, setup, mapa de funcionalidades e decisões de arquitetura para a banca. |
| F | **Médio** | **Duas stacks de CSS.** Site público em Bootstrap 5 via **CDN** (`layouts/bootstrap.blade.php`) + `public/css/style.css` escrito à mão (718 linhas); telas de settings/sidebar em Tailwind 4 + Flux. Sem internet no dia da defesa, o site público fica sem estilo. Linguagem visual inconsistente entre "site" e "painel/config". |
| G | **Médio** | **Autorização incompleta.** Só há `Policy` para `Quadra` e `Reserva` (`update`). Não há `SalaPolicy` (quem fecha/cancela uma sala?). A confirmação de pedido valida dono via closure na rota, fora do padrão de policy. |
| H | **Médio** | **Verificação de e-mail pela metade.** `/dashboard` exige `verified`, mas o cadastro loga automaticamente sem verificar e-mail e `MustVerifyEmail` está comentado no `User`. Decidir: está no escopo ou não? |
| I | **Baixo** | **`web.php` é uma pilha de closures** retornando views. Difícil de testar/manter; candidatos a full-page Livewire ou single-action controllers. |
| J | **Baixo** | **Jogador não cancela a própria reserva.** O enum `ReservaStatus` tem `Cancelada`, mas só dono/admin cancelam (`Painel\Reservas\Listagem`). |
| K | **Baixo** | **Restos de scaffolding.** `tests/Feature/ExampleTest.php` e `tests/Unit/ExampleTest.php` ainda presentes. Fim de linha CRLF/LF gerando ruído de diff. PHP local (WSL 8.3) diferente da matriz de CI (8.4/8.5). |
| L | **Baixo** | **String PIX falsa é malformada** (`Salas\Pagamento::codigoPix()` — CRC fixo `ABCD`). Inofensivo, mas um avaliador atento nota. Documentar como simulado. |
| M | **Contexto** | **"Quadras próximas" x quadras cadastradas.** A busca por geolocalização traz quadras do OpenStreetMap, que **não** são as quadras da plataforma. O propósito da feature precisa ficar explícito (mapa de contexto / prova de integração externa), senão parece um bug. |
| N | **Alto** | **Dois caminhos de cadastro vivos e divergentes.** `FortifyServiceProvider` registra `registerView` + `CreateNewUser`, então a rota `/register` do Fortify está no ar e cria usuário só com `name`/`email`/`password` (sem papel, sem perfil). O cadastro real da aplicação é `App\Livewire\Auth\Registrar` em `/registro`, que coleta o perfil completo. Dois fluxos, dados incompatíveis. Consolidar num só. |
| O | **Médio** | **Validação de CPF/CNPJ/UF só de tamanho.** `Registrar` confere 11 dígitos no CPF e `RegistrarDono` 14 no CNPJ — nenhum valida os dígitos verificadores (mód 11). `estado` aceita quaisquer 2 letras. Sem verificação de idade mínima em `data_nascimento`. Para um TCC brasileiro que coleta esses dados, a banca espera validação real. |
| P | **Alto (TCC)** | **LGPD.** Coleta CPF, data de nascimento, endereço, telefone (e CNPJ do dono) sem política de privacidade, sem consentimento explícito, sem minimização (por que exigir CPF e endereço para reservar uma quadra?) e sem base legal registrada. CPF/CNPJ em texto plano. Ponto a favor já existente: exclusão de conta (direito de eliminação). Quase certo que a banca pergunta. |
| Q | **Contexto (TCC)** | **Sem documentação de engenharia.** Não há `docs/` — nenhum diagrama ER, casos de uso por ator, lista de requisitos funcionais/não-funcionais nem diagrama de arquitetura versionados. A monografia precisa desses artefatos e a maioria pode ser derivada do código atual. |

### Fora de escopo (decisões conscientes — só reabrir se a banca pedir)

Gateway de pagamento real, CRUD de produto para dono/admin, favoritar quadra/produto,
avaliações/notas de quadra, notificações por e-mail. Estão listados como trabalho futuro.

---

## 2. Cronograma sugerido

Estimativa total: **~16–19h**. Sugestão de ~3 semanas, uma sessão por dia útil, agrupando
as curtas. A Sessão 0 vem sempre primeiro. Depois dela, a Trilha 2 (TCC) pode andar em
paralelo com a Trilha 1 — útil se você escreve a monografia enquanto programa.

### Trilha 1 — Código

| # | Sessão | Entrega | Tempo | Trata |
|---|--------|---------|-------|-------|
| 0 | Salvaguarda do repositório | commits organizados, branch de trabalho, `.gitignore`/`.gitattributes`, remover `NUL` | ~40min | A, K |
| 1 | Localização pt-BR | locale/timezone/faker + Carbon em português, ajustar testes | ~30min | D |
| 2 | Integridade de reservas | transação + lock + constraint, testes de concorrência | ~1h30 | B |
| 3 | Integridade de salas e loja | lock na entrada em sala, rollback + aviso no checkout, testes | ~1h30 | C |
| 4 | README e decisões de arquitetura | `README.md` + seção de ADRs | ~1h | E, F, L, M |
| 5 | Autorização e políticas | `SalaPolicy`, `authorize()` consistente, policy na confirmação de pedido | ~1h | G |
| 6 | Cancelamento de reserva pelo jogador | self-service com regra de antecedência, refletir nos painéis | ~1h | J |
| 7 | Consolidação visual / apresentação offline | Bootstrap via npm (sem CDN) ou com SRI, alinhar tokens de cor | ~1h30 | F |
| 8 | Limpeza final e CI | remover ExampleTest, alinhar versão PHP, revisar workflows, passada manual ponta a ponta | ~45min | H, I, K |

### Trilha 2 — TCC / monografia

| # | Sessão | Entrega | Tempo | Trata |
|---|--------|---------|-------|-------|
| A | Consolidar cadastro + validações brasileiras | um só fluxo de registro, regra de CPF/CNPJ (mód 11), UF, idade mínima, testes | ~1h30 | N, O |
| B | Adequação à LGPD | política de privacidade, consentimento, minimização de dados, base legal, revisão do fluxo de exclusão | ~2h | P |
| C | Documentação de engenharia (`docs/`) | diagrama ER, casos de uso por ator, requisitos funcionais/não-funcionais, diagrama de arquitetura — em Markdown + Mermaid | ~2h | Q |
| D | Metodologia e evidência de testes | cobertura (`pest --coverage`), capítulo "Testes" da monografia, rastro requisito→teste, capturas de tela dos fluxos | ~1h30 | — |
| E | Ambiente reproduzível para avaliação | Docker/Sail ou passo a passo à prova de falha, seed determinístico, "avalie em 5 minutos", checklist de contingência da defesa | ~1h30 | — |

Depois de cada sessão: `php artisan test` + `vendor/bin/pint --test`, testar no navegador,
e **commitar** antes de ir para a próxima.

---

## [ ] Sessão 0 — Salvaguarda do repositório

```
Contexto: projeto de TCC "AlugaQuadra" (Laravel 12 + Livewire 4 + Fortify + Pest).
Há uma quantidade grande de trabalho no working tree que nunca foi commitado:
dezenas de arquivos novos (app/Livewire/Admin, app/Livewire/Painel, app/Livewire/Loja,
app/Services/Overpass, app/Policies, app/Http/Middleware, ~10 migrations em
database/migrations/2026_08_*, várias views) e ~45 arquivos modificados. Existe também
um arquivo lixo chamado `NUL` na raiz (artefato de redirecionamento no Windows).
Tudo está sendo feito direto na branch `main`.

Objetivo desta sessão: pôr o repositório em segurança, sem mudar comportamento.

Faça:
1. Rode `git status` e `php artisan test` para registrar o ponto de partida (todos os
   178 testes devem passar).
2. Remova o arquivo `NUL` da raiz (no Windows use `del "\\?\%CD%\NUL"` ou remova pelo
   WSL: `rm ./NUL`). Confirme que sumiu do `git status`.
3. Revise `.gitignore`: garanta que `/database/*.sqlite`, `/database/*.sqlite-journal`
   e `.env` continuam ignorados. NÃO commite `database/database.sqlite` nem `.env`.
4. Crie/ajuste `.gitattributes` para normalizar fim de linha: `* text=auto eol=lf`
   e marque binários conhecidos (`*.png binary`, `*.ico binary`, `*.svg text`).
   Rode `git add --renormalize .` num commit separado só de normalização.
5. Organize o trabalho pendente em commits pequenos e temáticos, na ordem de
   dependência (migrations e models antes das telas). Sugestão de fatiamento:
   - "Adiciona papeis de usuario, middleware de role e policies"
   - "Implementa painel do dono (dashboard, quadras, reservas, financeiro)"
   - "Implementa area administrativa (listagens de quadras, usuarios, reservas)"
   - "Adiciona busca de quadras proximas via Overpass API"
   - "Adiciona pagamento simulado na entrada de salas"
   - "Adiciona fotos de quadra"
   - "Ajustes de navegacao, layout e testes"
   Use os prefixos e o estilo de mensagem dos commits já existentes (`git log --oneline`).
   Cada commit deve deixar a suíte verde — rode `php artisan test` entre eles se tiver dúvida.
6. Depois de tudo commitado em `main`, crie e passe a trabalhar numa branch
   `melhorias-pos-tcc` (ou nome equivalente) para as próximas sessões.
7. Não altere lógica de negócio nesta sessão. Se encontrar algo quebrado, anote num
   comentário no fim deste arquivo em vez de corrigir agora.

No fim: `git status` limpo, `php artisan test` verde, `vendor/bin/pint --test` verde.
```

---

## [ ] Sessão 1 — Localização pt-BR

```
Continuando o AlugaQuadra. O app é todo em português do Brasil, mas
config/app.php está com locale=en, faker_locale=en_US e timezone=UTC. Componentes
usam translatedFormat() do Carbon e os meses saem em inglês.

Faça:
1. Em .env.example e .env: APP_LOCALE=pt_BR, APP_FALLBACK_LOCALE=pt_BR,
   APP_FAKER_LOCALE=pt_BR. Defina o timezone da aplicação para "America/Recife"
   (os dados de demonstração são de Pernambuco) via config/app.php 'timezone'.
2. Garanta que o Carbon usa o locale pt_BR: em AppServiceProvider::boot(), adicione
   `\Carbon\CarbonImmutable::setLocale(config('app.locale'));` (o projeto já usa
   Date::use(CarbonImmutable::class)). Confirme que translatedFormat('d \d\e F \d\e Y')
   passa a render "26 de agosto de 2026".
3. Publique os arquivos de tradução pt-BR do Laravel se necessário (mensagens de
   validação): `php artisan lang:publish` e traduza validation.php, ou use um pacote
   de traduções pt-BR se já houver um instalado — verifique composer.json antes.
   Foque só em validation.php, auth.php e passwords.php.
4. Rode a suíte inteira. Alguns testes de data/hora podem quebrar por causa do
   timezone (ex.: cálculos com now()). Ajuste os testes para o novo timezone em vez
   de reverter a config. Testes que dependiam de mês em inglês devem passar a
   esperar português.
5. `vendor/bin/pint` no final.

Teste no navegador: abra a tela de criar sala e confira a data por extenso no resumo
da partida; abra /perfil > Minhas Reservas e confira as datas.
```

---

## [ ] Sessão 2 — Integridade de reservas (concorrência)

```
Continuando o AlugaQuadra. Hoje app/Livewire/Quadras/Listagem.php::reservar() e
app/Livewire/Salas/Criar.php::criar() fazem: consulta de conflito -> if -> create,
sem transação, sem lock e sem constraint no banco. Duas requisições simultâneas
conseguem reservar o mesmo horário da mesma quadra.

A janela de reserva é [hora_inicio, hora_fim) e reservas podem ter durações
diferentes (1h a 4h), então uma UNIQUE simples em (quadra_id, data, hora_inicio)
não cobre sobreposições parciais — serve só como rede de segurança para início
idêntico. A proteção real precisa ser transação + lock.

Faça:
1. Migration nova adicionando índice único em reservas (quadra_id, data, hora_inicio)
   — apenas como salvaguarda contra duplicata exata. Antes de aplicar, garanta no
   DemoSeeder e nas factories que não há linha que viole isso.
2. Extraia a lógica de "verificar conflito e criar reserva" para um único lugar
   reutilizável — um método em app/Models/Reserva.php (ex.: estático
   `Reserva::agendar(Quadra $quadra, int $userId, string $data, string $horaInicio, string $horaFim, ReservaStatus $status): self`)
   ou um pequeno service app/Services/Reservas/AgendadorDeReserva.php. Dentro dele:
   - `DB::transaction(...)`;
   - `Quadra::whereKey($quadra->id)->lockForUpdate()->first()` para serializar por quadra;
   - a mesma query de conflito que já existe hoje (hora_inicio < horaFim AND
     hora_fim > horaInicio AND status != cancelada);
   - se houver conflito, lançar uma exceção de domínio própria
     (App\Exceptions\HorarioIndisponivelException) em vez de retornar bool;
   - senão, criar e retornar a Reserva.
3. Reescreva reservar() e Criar::criar() para chamar esse ponto único e converter a
   exceção de domínio em $this->addError('horaInicio', '...') / mensagem amigável —
   mantendo exatamente as mensagens atuais para não quebrar os testes de asserção
   de texto. Em Criar::criar(), a criação da Sala continua na mesma transação da
   reserva.
4. Testes novos em tests/Feature/QuadraReservaTest.php e tests/Feature/SalaTest.php:
   - reserva com sobreposição parcial (ex.: existe 19:00–21:00, tenta 20:00–21:00) é
     rejeitada;
   - o índice único barra duplicata exata mesmo se a checagem de aplicação for
     ignorada (crie a primeira via factory sem passar pelo agendador, tente a
     segunda e espere QueryException OU a exceção de domínio).
   Não dá pra testar corrida real de forma determinística no SQLite; documente isso
   num comentário e cubra o caminho lógico (transação chamada, lock aplicado) —
   pode usar DB::transactionLevel() ou um spy simples.
5. Rode a suíte inteira + pint. Todos os 178 testes anteriores continuam passando.
```

---

## [ ] Sessão 3 — Integridade de salas e loja

```
Continuando o AlugaQuadra. Dois pontos com o mesmo padrão frágil da Sessão 2:

(a) app/Livewire/Salas/Pagamento.php::confirmarPagamento() faz
    count() >= max_participantes e depois participantes()->attach(), sem transação
    nem lock -> a sala pode passar da capacidade se dois usuários entrarem juntos.
    app/Livewire/Salas/Criar.php também dá attach() no criador sem checar.

(b) app/Livewire/Loja/Carrinho.php::finalizarPedido() cria Pedido + ItemPedido e só
    depois tenta `Produto::where('id',..)->where('estoque','>=',qty)->decrement(...)`.
    Se o decrement afetar 0 linhas (estoque acabou entre montar o carrinho e
    finalizar), o pedido é criado assim mesmo, sem rollback e sem avisar o usuário.

Faça:
1. Salas: em confirmarPagamento(), envolva a checagem de vaga + o attach() num
   `DB::transaction`, com `Sala::whereKey($this->sala->id)->lockForUpdate()->first()`
   e recontagem de participantes DENTRO da transação. Se cheia, retornar o mesmo
   $this->erro = 'Essa sala já está cheia.' de hoje (sem quebrar os testes de texto).
   Recarregue $this->sala depois.
2. Loja: em finalizarPedido(), dentro da transação, capture o retorno de decrement().
   Se algum produto retornar 0 (ou se `affected < 1`), aborte a transação
   (throw uma exceção de domínio App\Exceptions\EstoqueInsuficienteException),
   capture no componente e mostre uma mensagem inline
   ("O produto X ficou sem estoque. Ajuste o carrinho e tente de novo.") sem redirect
   e sem limpar o carrinho. Só chama Carrinho::limpar() e redireciona no sucesso.
3. Reforce a validação de estoque no momento do checkout (não só no
   Carrinho::adicionar): antes de abrir a transação, compare cada item do carrinho
   com o estoque atual e, se algum passou, corrija o carrinho para o máximo
   disponível e mostre um aviso pedindo pra revisar, sem finalizar.
4. Testes:
   - tests/Feature/SalaTest.php: sala com 1 vaga, dois usuários; simule o segundo
     entrando com a sala já cheia (crie o estado "cheia" direto e chame
     confirmarPagamento) e espere o erro, sem participante extra no banco.
   - tests/Feature/CarrinhoTest.php: carrinho com 2 unidades, estoque baixado para 1
     por fora, finalizarPedido não cria Pedido e mostra aviso; carrinho intacto.
5. Suíte inteira + pint verdes.
```

---

## [ ] Sessão 4 — README e decisões de arquitetura

```
Continuando o AlugaQuadra. O repositório não tem README. Existem
ROTEIRO-APRESENTACAO.md (roteiro da defesa) e PLANO-LOJA.md (histórico de execução).

Crie README.md na raiz, em português, com:
1. Uma frase do que é o AlugaQuadra e para quem.
2. Stack: Laravel 12, Livewire 4, Laravel Fortify (auth + 2FA), Pest, Vite,
   Tailwind 4 + Flux (área autenticada de settings) e Bootstrap 5 + CSS próprio
   (site público). SQLite por padrão.
3. Setup local passo a passo (pode reaproveitar `composer setup` do composer.json e o
   "Antes de começar" do ROTEIRO-APRESENTACAO.md). Inclua
   `php artisan db:seed --class=DemoSeeder` e a tabela de contas de demonstração.
4. Mapa de funcionalidades por papel (Jogador / Dono de quadra / Admin), com as
   rotas principais de cada uma.
5. Estrutura de pastas relevante (app/Livewire por contexto, app/Services/Overpass,
   app/Policies, app/Enums, app/Support/Carrinho).
6. Como rodar os testes e o lint (`php artisan test`, `vendor/bin/pint`).
7. Seção "Decisões de arquitetura (ADR resumido)" cobrindo, cada uma em 3–5 linhas:
   - Por que duas stacks de CSS convivem (site público herdado em Bootstrap; área de
     settings usa o starter kit Livewire/Flux) e que unificar é trabalho futuro.
   - Pagamento é simulado: reserva e pedido são confirmados na hora, sem gateway. A
     string PIX exibida é ilustrativa e não é um payload EMV válido.
   - "Quadras próximas" consulta o OpenStreetMap via Overpass API e mostra quadras do
     mundo real ao redor do usuário — é uma prova de integração com serviço externo,
     independente do cadastro de quadras da plataforma.
   - Papéis via coluna `role` + enum + middleware, não um pacote de ACL.
   - SQLite escolhido para facilitar a avaliação; migrations são compatíveis com MySQL.
8. Seção "Limitações conhecidas / trabalho futuro" consolidando a lista do fim do
   PLANO-LOJA.md + o que sobrar deste plano.

Não invente recursos que não existem — confirme cada afirmação lendo o código.
```

---

## [ ] Sessão 5 — Autorização e políticas

```
Continuando o AlugaQuadra. Só existem QuadraPolicy e ReservaPolicy (só `update`).
Não há policy para Sala, e a confirmação de pedido (routes/web.php, rota
loja.pedido.confirmacao) valida o dono do pedido com um abort_unless inline.

Faça:
1. Crie app/Policies/SalaPolicy.php no mesmo estilo das outras:
   - `update`/`cancelar`: criador da sala OU admin;
   - `entrar`: qualquer usuário autenticado que ainda não participa e que a sala não
     esteja cheia nem no passado (mova essa regra pra cá se hoje ela está espalhada
     no componente).
   Registre em AppServiceProvider::boot() junto das outras (Gate::policy(...)).
2. Aplique a policy onde hoje há checagem manual:
   - app/Livewire/Salas/Pagamento.php e qualquer lugar que feche/edite sala usam
     `$this->authorize(...)`.
   - Se ainda não existe tela de cancelar/fechar sala pelo criador, não crie agora
     (é a Sessão 6 que mexe em cancelamento); só deixe a policy pronta.
3. Crie app/Policies/PedidoPolicy.php com `view` (dono do pedido OU admin) e use no
   lugar do abort_unless inline. Se a rota é uma closure, considere trocá-la por um
   componente Livewire full-page `App\Livewire\Loja\Confirmacao` que faça
   `$this->authorize('view', $pedido)` no mount — decida pelo caminho mais curto e
   siga o padrão das outras telas da loja.
4. Revise as telas de admin (app/Livewire/Admin/*): confirme que são somente leitura
   e que qualquer ação de escrita (se houver) exige role admin via policy/authorize,
   não só o middleware de rota.
5. Testes em tests/Feature/ (novos arquivos SalaPolicyTest.php e PedidoPolicyTest.php
   no mesmo formato de QuadraPolicyTest.php): criador cancela/edita a própria sala,
   terceiro não; dono do pedido vê a confirmação, terceiro toma 403; admin vê tudo.
6. Suíte + pint verdes.
```

---

## [ ] Sessão 6 — Cancelamento de reserva pelo jogador

```
Continuando o AlugaQuadra. O enum ReservaStatus tem Cancelada, mas só o dono/admin
cancelam (app/Livewire/Painel/Reservas/Listagem.php). O jogador não tem como
desmarcar a própria reserva.

Faça:
1. Regra de negócio: o jogador pode cancelar a própria reserva se ela ainda está no
   futuro e faltam pelo menos X horas para o início (defina X = 3 e deixe como
   constante nomeada, fácil de achar). Reserva já cancelada ou passada não pode.
   Reserva vinculada a uma sala (reserva_id preenchido na Sala) NÃO pode ser
   cancelada direto por aqui — cancelar a sala é outro fluxo; apenas bloqueie com
   mensagem clara.
2. Adicione o método em ReservaPolicy (`cancelarComoJogador` ou reaproveite a
   estrutura) e a ação em app/Livewire/Perfil/MinhasReservas.php: botão "Cancelar"
   nas reservas futuras elegíveis, com confirmação (wire:confirm), atualizando o
   status para Cancelada e recomputando a lista.
3. resources/views/livewire/perfil/minhas-reservas.blade.php: botão só aparece
   quando a policy permite; reservas canceladas aparecem com um badge "Cancelada" e
   sem botão.
4. O painel do dono já filtra status != cancelada nos lugares certos? Confirme
   em Painel/Reservas/Listagem.php e Painel/Financeiro.php que uma reserva cancelada
   pelo jogador some do faturamento e da aba correta. Ajuste se necessário.
5. Testes em tests/Feature/Perfil/MinhasReservasTest.php: cancela reserva futura
   elegível; não consegue cancelar reserva a menos de 3h; não consegue cancelar
   reserva de sala; reserva cancelada não entra no faturamento do dono.
6. Suíte + pint verdes. Atualize ROTEIRO-APRESENTACAO.md (passo 6, Meu Perfil)
   mencionando o cancelamento.
```

---

## [ ] Sessão 7 — Consolidação visual / apresentação offline

```
Continuando o AlugaQuadra. O site público (layouts/bootstrap.blade.php) carrega
Bootstrap 5 CSS/JS e bootstrap-icons por CDN (jsdelivr). Sem internet no dia da
defesa, o site fica sem estilo. Além disso há divergência visual entre o site
(Bootstrap + public/css/style.css) e as telas de settings (Tailwind/Flux).

Objetivo: apresentação 100% offline e visual coeso, sem reescrever o site.

Faça:
1. Traga o Bootstrap para o build local em vez do CDN:
   - `npm i bootstrap@5.3 bootstrap-icons` (as versões que já estão no HTML).
   - importe no pipeline do Vite (resources/css/app.css ou um novo
     resources/css/site.css) e no resources/js/app.js o bundle JS do Bootstrap.
   - troque os <link>/<script> de CDN em layouts/bootstrap.blade.php por
     @vite([...]). Confirme que os ícones bi-* continuam aparecendo (fonte
     bootstrap-icons copiada pelo Vite — pode precisar de config de assets).
   - `npm run build` e teste o site inteiro offline (desligue o wi-fi e navegue por
     todas as telas do ROTEIRO-APRESENTACAO.md).
2. Se algo do Flux/Tailwind também puxa recurso externo, verifique fontes em
   resources/views/partials/head.blade.php e resolva do mesmo jeito.
3. Alinhe os tokens de cor: extraia a cor laranja da marca e os raios de borda /
   sombras já repetidos em public/css/style.css para variáveis CSS no :root e
   troque os valores hardcoded por var(--...). Não redesenhe nada — só remova
   duplicação e deixe um ponto único de ajuste. Documente as variáveis num
   comentário no topo do style.css.
4. Rode a suíte (as mudanças são de asset, não devem quebrar nada) + pint.

Teste: wi-fi desligado, `php artisan serve`, percorra cadastro -> quadras ->
reserva -> sala -> loja -> perfil. Nenhuma tela pode ficar sem CSS ou sem ícones.
```

---

## [ ] Sessão 8 — Limpeza final e CI

```
Última sessão. Faxina e fechamento.

Faça:
1. Remova tests/Feature/ExampleTest.php e tests/Unit/ExampleTest.php (scaffolding do
   Laravel, não testam nada do domínio). Rode a suíte pra confirmar a contagem nova.
2. Decida a verificação de e-mail: hoje /dashboard exige `verified` mas o cadastro
   loga sem verificar e MustVerifyEmail está comentado no User. Escolha UMA:
   (a) tirar o middleware `verified` de /dashboard e assumir que não há verificação
       (mais simples, coerente com "cadastro loga direto"); ou
   (b) ativar de verdade: descomentar MustVerifyEmail, disparar o e-mail no registro,
       e ajustar o ROTEIRO pra usar `php artisan tinker` / Mailpit na demo.
   Faça a opção (a) salvo se você quiser mostrar verificação na banca. Ajuste os
   testes de acordo.
3. Alinhe a versão do PHP: composer.json pede ^8.2, o CI roda 8.4 e 8.5. Rode a
   suíte localmente num PHP 8.4 (o Herd tem) e confirme que passa. Se quiser, fixe
   `"php": "^8.3"` no composer.json pra refletir o mínimo real testado.
4. Revise .github/workflows/tests.yml e lint.yml: confirme que rodam em push/PR pra
   `main`, que o cache do Composer está ligado, e que `npm run build` roda antes dos
   testes (algum teste de view pode depender do manifest do Vite).
5. Passe o `pint` no projeto inteiro (`vendor/bin/pint`) e commite o que ele ajustar.
6. Faça a passada manual completa do ROTEIRO-APRESENTACAO.md do começo ao fim, com o
   banco recém-semeado, anotando qualquer atrito. Corrija só o que for rápido e
   visual; o resto vira item em "trabalho futuro" no README.
7. Atualize este arquivo (PLANO-MELHORIAS.md) marcando as sessões concluídas e
   movendo o que não foi feito para uma seção "Pendências" no fim.

No fim: `git status` limpo na branch de melhorias, suíte verde, pint verde, e um PR
(ou merge) da branch `melhorias-pos-tcc` para `main`.
```

---

# Trilha 2 — TCC / monografia

Estas sessões produzem artefatos acadêmicos e conformidade. Dependem só da Sessão 0.

---

## [ ] Sessão A — Consolidar cadastro + validações brasileiras

```
Continuando o AlugaQuadra (Laravel 12 + Livewire 4 + Fortify). Hoje há DOIS fluxos
de cadastro vivos:
- Fortify: FortifyServiceProvider registra Fortify::registerView() e
  Fortify::createUsersUsing(App\Actions\Fortify\CreateNewUser::class). CreateNewUser
  valida e grava só name/email/password (sem role, sem perfil). A rota POST /register
  do Fortify está no ar.
- Aplicação: App\Livewire\Auth\Registrar (rota /registro) coleta o perfil completo
  (CPF, nascimento, sexo, endereço, CEP, cidade, estado, telefone) e
  App\Livewire\Auth\RegistrarDono (rota /cadastro-dono) coleta CNPJ + estabelecimento.

Além disso a validação de documentos é só de tamanho: Registrar confere "11 dígitos"
no CPF, RegistrarDono "14 dígitos" no CNPJ, nenhum valida dígito verificador; `estado`
aceita quaisquer 2 letras; não há idade mínima.

Faça:
1. Decida UM caminho de cadastro de jogador e remova o outro de circulação. Recomendado:
   manter os componentes Livewire (Registrar / RegistrarDono) e desligar o registro
   do Fortify — em config/fortify.php remova Features::registration() (ou o equivalente
   na sua versão) e apague App\Actions\Fortify\CreateNewUser + a linha
   createUsersUsing() + registerView() no FortifyServiceProvider. Confirme que
   `php artisan route:list` não lista mais POST /register. Ajuste ou remova os testes
   que batiam na rota do Fortify (tests/Feature/Auth/RegistrationTest.php) e garanta
   que RegistroCompletoTest e RegistroDonoTest cobrem o fluxo que sobrou.
2. Crie uma regra de validação reutilizável para CPF com dígito verificador:
   app/Rules/Cpf.php (implements ValidationRule). Idem app/Rules/Cnpj.php. Rejeite
   sequências repetidas (00000000000 etc.) e valide os dois dígitos por mód 11.
3. Crie app/Rules/Uf.php (ou use Rule::in) validando contra a lista das 27 UFs.
4. Em Registrar: troque a checagem manual de CPF pela regra Cpf, valide `estado` com
   Uf, e adicione idade mínima — defina IDADE_MINIMA = 16 como constante nomeada e
   rejeite data_nascimento que não a atinja, com mensagem clara. Em RegistrarDono:
   regra Cnpj + Uf.
5. Centralize as regras de perfil em app/Concerns/ProfileValidationRules.php (hoje só
   tem name/email) para os dois componentes não duplicarem — mova cpf/uf/telefone/etc.
   para lá como métodos, no mesmo estilo dos que já existem.
6. Testes novos em tests/Unit/Rules/ (CpfTest, CnpjTest, UfTest com casos válidos e
   inválidos conhecidos) e ajustes nos testes de cadastro para um CPF/CNPJ válido de
   teste. Rode a suíte inteira + pint.

Documente numa frase no futuro README qual fluxo de cadastro é o oficial.
```

---

## [ ] Sessão B — Adequação à LGPD

```
Continuando o AlugaQuadra. O sistema coleta dados pessoais (CPF, data de nascimento,
endereço, CEP, telefone; CNPJ e endereço do estabelecimento para donos) e hoje não
tem política de privacidade, consentimento nem base legal registrada. É um ponto que
uma banca de TCC quase sempre levanta. Objetivo: tratamento mínimo viável e defensável,
+ material para o capítulo de LGPD da monografia. NÃO precisa virar um DPO — precisa
ser coerente e documentado.

Faça:
1. Minimização de dados: revise campo a campo do cadastro de jogador o que é REALMENTE
   necessário para "reservar quadra e entrar em salas". Proposta a implementar salvo
   objeção: manter obrigatórios name, email, telefone, cidade/estado; tornar CPF,
   endereço completo, CEP, data de nascimento e sexo OPCIONAIS (nullable já no banco),
   com um texto curto explicando por que cada um é pedido. Ajuste Registrar, as regras
   e os testes. Se algum desses dados for exigido por uma regra de negócio real
   (ex.: CPF no recibo de pagamento), escreva essa justificativa num comentário.
2. Consentimento: adicione ao cadastro (jogador e dono) um checkbox obrigatório
   "Li e aceito a Política de Privacidade e os Termos de Uso" com link, e grave
   `consentimento_em` (timestamp) e `consentimento_versao` (string) no users via
   migration. Sem o aceite, não cria a conta.
3. Páginas estáticas /politica-de-privacidade e /termos-de-uso (Blade simples no
   layout do site). Conteúdo objetivo: quais dados são coletados, para quê, base
   legal (execução de contrato + consentimento), retenção, com quem é compartilhado
   (ninguém, além da Overpass API que NÃO recebe dado pessoal — deixar isso explícito),
   e como exercer os direitos (acesso, correção, eliminação).
4. Direito de eliminação: já existe exclusão de conta (Fortify delete-user). Documente
   e teste o que acontece em cascata — reservas, pedidos, participações em sala,
   quadras do dono (hoje é cascadeOnDelete). Decida se reserva/pedido devem ser
   anonimizados em vez de apagados (para o dono não perder histórico financeiro) e,
   se sim, implemente: ao excluir o usuário, setar user_id nulo + copiar nome para
   um campo `cliente_nome` que já existe em reservas. Teste esse caminho.
5. Direito de acesso: um botão "Baixar meus dados" no perfil que gera um JSON com os
   dados do usuário e suas reservas/pedidos/salas. Simples, sem fila.
6. Escreva docs/lgpd.md consolidando: inventário de dados pessoais (tabela: campo,
   finalidade, base legal, retenção), fluxo de consentimento, e os direitos atendidos.
   Esse arquivo vira base do capítulo da monografia.
7. Suíte + pint verdes. Atualize ROTEIRO-APRESENTACAO.md com um passo mostrando o
   aceite no cadastro e o "Baixar meus dados".
```

---

## [ ] Sessão C — Documentação de engenharia (`docs/`)

```
Continuando o AlugaQuadra. Não existe pasta docs/ nem diagramas versionados. A
monografia precisa de artefatos de engenharia de software e a maior parte pode ser
derivada do código atual. Use Markdown + Mermaid (renderiza no GitHub) para tudo
ficar versionável e fácil de exportar como imagem depois.

Leia antes de escrever: database/migrations/*, app/Models/*, app/Enums/*,
routes/*.php, app/Livewire/** e app/Policies/*. Não invente entidade nem regra que
não esteja no código.

Faça (um arquivo por item, em docs/):
1. docs/modelo-de-dados.md: diagrama ER em Mermaid (erDiagram) com todas as tabelas
   de domínio (users, quadras, quadra_fotos, reservas, salas, participacao_salas,
   produtos, pedidos, itens_pedido) e seus relacionamentos e cardinalidades. Abaixo
   do diagrama, uma tabela por entidade com os campos e o significado de cada um.
2. docs/casos-de-uso.md: um diagrama de casos de uso (Mermaid, pode ser um flowchart
   agrupado por ator) para os três atores (Jogador, Dono de Quadra, Administrador) +
   Visitante. Liste em texto cada caso de uso com pré-condição, fluxo principal e
   fluxo alternativo relevante (ex.: "Reservar quadra" com o alternativo "horário em
   conflito").
3. docs/requisitos.md: requisitos funcionais numerados (RF01…) rastreáveis a um
   componente/rota, e requisitos não-funcionais (RNF01…) — segurança (papéis,
   rate limit de login, hash de senha), usabilidade (pt-BR, formatação de máscara),
   confiabilidade (transações nas reservas depois da Sessão 2), portabilidade
   (SQLite/MySQL), LGPD (aponta para docs/lgpd.md).
4. docs/arquitetura.md: diagrama de camadas (Mermaid) mostrando Navegador →
   Rotas → Componentes Livewire → (Policies / Services / Models) → Banco, com a
   Overpass API como sistema externo. Um ou dois parágrafos sobre o padrão adotado
   (Livewire full-page como controlador, Services para integração externa, Enums
   para valores de domínio, Policies para autorização) e por que SQLite/Fortify.
5. docs/README.md: índice curto linkando os quatro arquivos, com uma nota de que os
   diagramas Mermaid podem ser exportados como PNG/SVG para a monografia.
6. Linke docs/ a partir do README.md principal (que a Sessão 4 cria) — se o README
   ainda não existir, deixe um TODO nele.

Não mexa em código de aplicação nesta sessão. pint não se aplica a .md, mas rode
`php artisan test` no fim só para garantir que nada foi tocado por engano.
```

---

## [ ] Sessão D — Metodologia e evidência de testes

```
Continuando o AlugaQuadra. A suíte tem ~178 testes Pest passando, mas não há
medição de cobertura nem um texto que explique a estratégia — a monografia precisa
dos dois.

Faça:
1. Habilite cobertura: confirme que a extensão pcov ou xdebug está disponível
   (o CI já usa xdebug). Adicione um script "test:coverage" no composer.json rodando
   `pest --coverage --min=70` (ajuste o mínimo para um pouco abaixo do valor real
   atual — rode uma vez para medir). Adicione `--coverage-clover` para gerar
   coverage.xml e ignore-o no .gitignore.
2. No .github/workflows/tests.yml, adicione um passo que roda a cobertura e publica
   o resumo no job summary (pest já imprime a tabela). Não precisa de serviço externo
   (Codecov etc.).
3. docs/testes.md: explique a pirâmide adotada (muitos testes de feature/HTTP+Livewire,
   poucos unitários nos pontos de lógica pura — Overpass, regras de CPF, Carrinho),
   por que Pest, o uso de RefreshDatabase, factories e seeders de teste. Inclua a
   tabela de cobertura por diretório e comente os pontos descobertos.
4. docs/rastreabilidade.md: tabela ligando cada RF de docs/requisitos.md ao(s)
   arquivo(s) de teste que o exercita(m). Onde não houver teste, marque como lacuna
   e decida se vale cobrir agora.
5. Capturas de tela: rode o app com o DemoSeeder e salve em docs/img/ prints dos
   fluxos principais (home, listagem de quadras com filtro, reserva confirmada,
   criar sala, loja, carrinho, confirmação de pedido, painel do dono, perfil).
   Referencie-as em docs/casos-de-uso.md. (Se preferir, deixe a lista do que
   capturar e faça os prints à mão.)
6. Suíte + pint verdes.
```

---

## [ ] Sessão E — Ambiente reproduzível para avaliação

```
Última sessão da trilha de TCC. Hoje rodar o projeto depende de ter PHP+Composer+Node
configurados na mão (o ROTEIRO assume isso). Para a banca/avaliador, quanto mais
próximo de "um comando" melhor.

Faça:
1. Escolha e implemente UMA das opções, documentando no README:
   (a) Laravel Sail: `composer require laravel/sail --dev` já está instalado —
       gere o docker-compose (só app + sqlite, sem MySQL para ficar leve),
       documente `./vendor/bin/sail up`, `sail artisan migrate --seed`. OU
   (b) Passo a passo à prova de falha sem Docker: um script `bin/setup.sh` /
       `bin/setup.ps1` que roda composer install, cópia do .env, key:generate,
       migrate:fresh, db:seed --class=DemoSeeder, npm ci, npm run build, e imprime
       as contas de demo no fim.
   Recomendo (b) se a defesa é na sua máquina; (a) se o avaliador vai rodar na dele.
2. Revise o .env.example: garanta que roda de cara com SQLite sem editar nada
   (DB_CONNECTION=sqlite, DB_DATABASE com caminho absoluto ou o padrão do Laravel),
   e que as chaves da Overpass têm default utilizável.
3. Seed determinístico: no DemoSeeder, fixe uma seed do Faker
   (`fake()->seed(12345)` ou `$this->faker->seed(...)`) para os dados aleatórios
   (os 4 jogadores extras) saírem iguais toda vez — a demo fica previsível.
4. docs/como-avaliar.md: "Avalie em 5 minutos" — os comandos exatos, as três contas,
   e um roteiro mínimo (login como jogador → reservar → criar sala → comprar na loja).
   É o ROTEIRO-APRESENTACAO.md condensado para quem não assistiu à defesa.
5. Checklist de contingência da defesa (pode ser uma seção no ROTEIRO): o que fazer
   se cair a internet (Sessão 7 já resolve o CSS), se o banco corromper
   (migrate:fresh + seed), se a porta 8000 estiver ocupada.
6. Faça a passada final: apague o projeto num diretório limpo (clone do zero),
   siga só o que está no README, e confirme que sobe. Corrija o que travar.
7. Suíte + pint verdes. Merge das duas trilhas para `main`.
```

---

## 3. Trabalho futuro (não planejado aqui — decidir caso a caso)

- Gateway de pagamento real (Stripe / Mercado Pago / PIX de verdade).
- CRUD de produtos para dono/admin (padrão: `app/Livewire/Painel/Quadras/Formulario.php`).
- Avaliações e notas de quadras.
- Notificações por e-mail (reserva confirmada, sala cheia, lembrete de jogo).
- Visão de calendário/agenda da quadra no lugar do dropdown de horários.
- Aba "Quadras Favoritas" no perfil (CSS já existe, funcionalidade não).
- Unificar a stack de CSS (migrar o site público para Tailwind, ou o inverso).
- Reescrever `routes/web.php` (closures → Livewire full-page / controllers de ação única).
