# Plano de Execução — AlugaQuadra (TCC)

Revisão completa do projeto em **28/08/2026**. Este documento substitui o antigo
`PLANO-MELHORIAS.md` e o `PLANO-LOJA.md` (ambos arquivados em `docs/historico/`).

**Como usar:** cada sessão abaixo é **um prompt independente**. Copie o bloco entre
as marcas ` ``` ` e cole numa conversa nova do Claude Code. Cada sessão assume que a
anterior já foi commitada. Marque o checkbox `[x]` conforme terminar.

**Sobre os commits:** os commits devem ficar registrados **somente no seu nome**
(`Matheus-danno`). **Nunca** adicione `Co-Authored-By`, `Generated with`,
`Assisted-by` nem qualquer outro crédito de IA/assistente — em nenhuma sessão,
mesmo que a ferramenta sugira. Use o estilo de mensagem dos commits que já existem
(`git log --oneline`): imperativo, sem ponto final, sem prefixo `feat:/fix:`.

---

## 1. Diagnóstico — estado atual (28/08/2026)

### O que já está bom

- **Laravel 12.53 + Livewire 4 + Fortify (2FA) + Pest.** 178 testes passando (528
  asserções), `pint` limpo.
- Domínio modelado: `User`, `Quadra`, `QuadraFoto`, `Reserva`, `Sala`,
  `ParticipacaoSala`, `Produto`, `Pedido`, `ItemPedido`. Enums para todos os valores
  de domínio (`Esporte`, `ReservaStatus`, `UserRole`, `FormaPagamento`,
  `NivelHabilidade`, `PedidoStatus`, `Sexo`, `AceitacaoNivel`).
- Fluxos completos: cadastro (jogador e dono), reserva de quadra com detecção de
  conflito de horário, criação de sala/partida (gera reserva vinculada), entrada em
  sala com pagamento simulado (PIX/cartão), loja com catálogo/carrinho/checkout
  simulado/histórico, painel do dono (dashboard, quadras, reservas, financeiro,
  agendamento manual), área de admin (listagens), perfil (minhas reservas / meus
  pedidos).
- `App\Services\Overpass\*` — integração com a Overpass API (OpenStreetMap) bem
  feita: retry, timeout escalonado, cache, exceção própria, teste unitário.
- Papéis (`Jogador`, `DonoQuadra`, `Admin`) com middleware `EnsureUserHasRole` e
  `Policy` para `Quadra` e `Reserva`.
- Código limpo de SQL cru: nenhum `DB::raw`, `whereRaw`, `strftime` etc. — só o
  query builder do Eloquent. Isso tornou a troca de banco (item abaixo) indolor.

### O que foi feito nesta sessão (28/08/2026)

- **Conexão com PostgreSQL implementada.** Ver Sessão 1 (marcada como concluída) e
  `docs/banco-de-dados.md`. As 21 migrations rodam limpas no PostgreSQL 16, o
  `DemoSeeder` popula sem erro, e 178/178 testes passam contra o banco
  `aluga_quadra_test`. `docker-compose.yml` sobe o banco com um comando.

### Problemas encontrados (ordenados por gravidade)

| # | Gravidade | Problema | Trata na sessão |
|---|-----------|----------|-----------------|
| A | **Crítico** | **Trabalho não versionado.** Dezenas de arquivos novos sem `git add` (`app/Livewire/Admin`, `app/Livewire/Painel`, `app/Livewire/Loja`, `app/Services/Overpass`, `app/Policies`, `app/Http/Middleware`, ~15 migrations, muitas views) e ~45 arquivos "modificados" que na verdade são só ruído de fim de linha (CRLF↔LF). O último commit não reflete o projeto. Há um arquivo lixo `NUL` na raiz. Tudo direto na `main`. Um `checkout` errado apaga meses de trabalho. | 0 |
| B | **Alto** | **Concorrência em reservas.** `Quadras\Listagem::reservar()` e `Salas\Criar::criar()` fazem *verifica-depois-insere* sem transação, sem lock e sem constraint no banco. Duas requisições simultâneas reservam o mesmo horário. Com PostgreSQL dá para resolver de verdade com uma *exclusion constraint* (`EXCLUDE USING gist`). | 3 |
| C | **Alto** | **Concorrência em salas e loja.** `Salas\Pagamento::confirmarPagamento()` checa `count() >= max_participantes` e depois `attach()` fora de transação → sala estoura a capacidade. No checkout (`Loja\Carrinho::finalizarPedido()`), se o `decrement` guardado falhar, o `Pedido` e os `ItemPedido` são criados mesmo assim, sem rollback e sem aviso. | 4 |
| D | **Alto** | **Localização errada.** `config/app.php`: `locale=en`, `faker_locale=en_US`, `timezone=UTC`. O app é 100% pt-BR e usa `translatedFormat('d \d\e F \d\e Y')` — meses saem em inglês. | 2 |
| E | **Médio** | **Sem README.** Só `ROTEIRO-APRESENTACAO.md`. Um repositório de TCC precisa de README com stack, setup, mapa de funcionalidades e decisões de arquitetura. | 5 |
| F | **Médio** | **Duas stacks de CSS.** Site público em Bootstrap 5 via **CDN** + `public/css/style.css` à mão (718 linhas); telas de settings/sidebar em Tailwind 4 + Flux. Sem internet no dia da defesa, o site público fica sem estilo. | 8 |
| G | **Médio** | **Autorização incompleta.** Só há `Policy` para `Quadra` e `Reserva`. Não há `SalaPolicy` nem `PedidoPolicy` — a confirmação de pedido valida o dono com `abort_unless` inline na rota. | 6 |
| H | **Médio** | **Verificação de e-mail pela metade.** `/dashboard` exige `verified`, mas o cadastro loga automaticamente sem verificar e `MustVerifyEmail` está comentado no `User`. Decidir: no escopo ou não? | 9 |
| I | **Baixo** | **`routes/web.php` é uma pilha de closures** retornando views. Difícil de testar/manter; candidatos a Livewire full-page ou single-action controllers. | 9 (parcial) / futuro |
| J | **Baixo** | **Jogador não cancela a própria reserva.** O enum `ReservaStatus` tem `Cancelada`, mas só dono/admin cancelam. | 7 |
| K | **Baixo** | **Restos de scaffolding.** `tests/Feature/ExampleTest.php` e `tests/Unit/ExampleTest.php`. `database/database.sqlite` órfão (banco antigo). PHP local (WSL 8.3) diferente da matriz de CI (8.4/8.5). | 0 / 9 |
| L | **Baixo** | **String PIX ilustrativa** (`Salas\Pagamento::codigoPix()` — CRC fixo). Inofensivo; documentar como simulado. | 5 |
| M | **Contexto** | **"Quadras próximas" x quadras cadastradas.** A busca por geolocalização traz quadras do OpenStreetMap, que **não** são as da plataforma. O propósito precisa ficar explícito. | 5 |
| N | **Alto (TCC)** | **Dois caminhos de cadastro vivos.** `FortifyServiceProvider` registra `registerView` + `CreateNewUser` (cria usuário só com nome/e-mail/senha). O cadastro real é `App\Livewire\Auth\Registrar` (`/registro`), que coleta o perfil completo. Dois fluxos, dados incompatíveis. | A |
| O | **Médio (TCC)** | **Validação de CPF/CNPJ/UF só de tamanho.** Ninguém valida dígito verificador (mód 11); `estado` aceita quaisquer 2 letras; sem idade mínima em `data_nascimento`. | A |
| P | **Alto (TCC)** | **LGPD.** Coleta CPF, nascimento, endereço, telefone (e CNPJ) sem política de privacidade, sem consentimento, sem minimização, sem base legal registrada. CPF/CNPJ em texto plano. | B |
| Q | **Contexto (TCC)** | **Sem documentação de engenharia.** Não há `docs/` com diagrama ER, casos de uso por ator, requisitos funcionais/não-funcionais nem diagrama de arquitetura versionados. | C |

### Fora de escopo (decisões conscientes — só reabrir se a banca pedir)

Gateway de pagamento real, CRUD de produto para dono/admin, favoritar
quadra/produto, avaliações/notas de quadra, notificações por e-mail. Ver seção 4.

---

## 2. Cronograma

Estimativa total: **~18–22h**. Sugestão de **~3 semanas**, uma sessão por dia útil,
agrupando as curtas. A Sessão 0 vem **sempre primeiro**. Depois dela, a Trilha 2
(TCC) pode correr em paralelo com a Trilha 1 — útil se você escreve a monografia
enquanto programa.

### Trilha 1 — Código

| # | Sessão | Entrega | Tempo | Semana |
|---|--------|---------|-------|--------|
| 0 | Salvaguarda do repositório | commits organizados do backlog, normalização CRLF→LF, remover `NUL` e `database.sqlite` órfão, branch de trabalho | ~1h | 1 · seg |
| 1 | ✅ **Conexão com PostgreSQL** | **FEITO** — `docker-compose.yml`, `.env`/`.env.example`, `phpunit.xml`, CI, `docs/banco-de-dados.md`. Falta só: você validar na sua máquina + entrar no README (Sessão 5) | — | 1 · ter |
| 1.1 | Validação local + DBeaver (sem Docker) | PostgreSQL 16 nativo na WSL instalado, `.env` em pgsql, migrations+seed rodados, conexão DBeaver testada, persistência validada ponta a ponta | ~1h35 (2 blocos, 1 dia) | 1 · ter/qua |
| 2 | Localização pt-BR | locale/timezone/faker + Carbon em português, traduções de validação, ajustar testes | ~45min | 1 · qua |
| 3 | Integridade de reservas | transação + `lockForUpdate` + **exclusion constraint do PostgreSQL** contra sobreposição, testes | ~2h | 1 · qui |
| 4 | Integridade de salas e loja | lock na entrada em sala, rollback + aviso no checkout, testes | ~1h30 | 1 · sex |
| 5 | README + decisões de arquitetura | `README.md` na raiz + seção de ADRs, incorpora banco de dados, PIX simulado, quadras próximas | ~1h30 | 2 · seg |
| 6 | Autorização e políticas | `SalaPolicy`, `PedidoPolicy`, `authorize()` consistente | ~1h30 | 2 · ter |
| 7 | Cancelamento de reserva pelo jogador | self-service com regra de antecedência, refletir nos painéis | ~1h | 2 · qua |
| 8 | Consolidação visual / apresentação offline | Bootstrap via npm (sem CDN), alinhar tokens de cor | ~1h30 | 2 · qui |
| 9 | Limpeza final e CI | remover ExampleTest, decidir verificação de e-mail, alinhar PHP, revisar workflows, passada manual ponta a ponta | ~1h | 3 · seg |

### Trilha 2 — TCC / monografia (depende só da Sessão 0)

| # | Sessão | Entrega | Tempo | Semana |
|---|--------|---------|-------|--------|
| A | Consolidar cadastro + validações brasileiras | um só fluxo de registro, regra de CPF/CNPJ (mód 11), UF, idade mínima, testes | ~2h | 2 · sex |
| B | Adequação à LGPD | política de privacidade, consentimento, minimização, base legal, "baixar meus dados", `docs/lgpd.md` | ~2h30 | 3 · ter |
| C | Documentação de engenharia (`docs/`) | diagrama ER, casos de uso por ator, requisitos funcionais/não-funcionais, arquitetura — Markdown + Mermaid | ~2h30 | 3 · qua |
| D | Metodologia e evidência de testes | cobertura (`pest --coverage`), capítulo "Testes", rastro requisito→teste, capturas de tela | ~1h30 | 3 · qui |
| E | Ambiente reproduzível para avaliação | `docker compose up` documentado + script de setup à prova de falha, seed determinístico, "avalie em 5 minutos" | ~1h30 | 3 · sex |

Depois de cada sessão: rodar os testes e o `pint`, testar no navegador, e
**commitar** antes de ir para a próxima.

---

## Trilha 1 — Código

## [ ] Sessão 0 — Salvaguarda do repositório

```
Contexto: projeto de TCC "AlugaQuadra" (Laravel 12 + Livewire 4 + Fortify + Pest),
banco PostgreSQL via docker-compose.yml. Há uma quantidade grande de trabalho no
working tree que nunca foi commitado: dezenas de arquivos novos (app/Livewire/Admin,
app/Livewire/Painel, app/Livewire/Loja, app/Services/Overpass, app/Policies,
app/Http/Middleware, ~15 migrations em database/migrations/2026_08_*, muitas views,
routes/admin.php, routes/painel.php) e ~45 arquivos que aparecem como "modificados"
mas cujo diff real é só fim de linha (CRLF vs LF) — .gitattributes já declara
`* text=auto eol=lf`, então é renormalização pendente. Existe um arquivo lixo `NUL`
na raiz (artefato de redirecionamento no Windows) e um database/database.sqlite
órfão (o projeto agora usa PostgreSQL). Tudo está sendo feito direto na branch main.

Objetivo: pôr o repositório em segurança, sem mudar comportamento.

Faça:
1. Suba o banco (`docker compose up -d`) e rode a suíte para registrar o ponto de
   partida: todos os 178 testes devem passar. Rode `vendor/bin/pint --test`.
2. Remova o arquivo `NUL` da raiz (pelo WSL: `rm ./NUL`). Remova também
   `database/database.sqlite` (o projeto usa PostgreSQL agora; o arquivo está
   ignorado pelo git, é só limpeza local). Confirme com `git status`.
3. Confirme no .gitignore / database/.gitignore que `*.sqlite*` e `.env` continuam
   ignorados. NÃO commite `.env`.
4. Faça um commit isolado só de normalização de fim de linha:
   `git add --renormalize .` e commite como "Normaliza fim de linha para LF".
   Depois desse commit, `git status` deve mostrar só os arquivos com mudança real.
5. Organize o trabalho pendente em commits pequenos e temáticos, na ordem de
   dependência (migrations e models antes das telas). Sugestão de fatiamento:
   - "Adiciona papeis de usuario, middleware de role e policies"
   - "Modela loja: pedidos, itens de pedido e estoque de produtos"
   - "Implementa painel do dono (dashboard, quadras, reservas, financeiro, agendamento)"
   - "Implementa area administrativa (listagens de quadras, usuarios, reservas)"
   - "Adiciona busca de quadras proximas via Overpass API"
   - "Adiciona pagamento simulado na entrada de salas"
   - "Adiciona fotos de quadra"
   - "Adiciona cadastro de dono de quadra e campos de estabelecimento"
   - "Ajustes de navegacao, layout e testes"
   Cada commit deve deixar a suíte verde — rode os testes entre eles se tiver dúvida.
6. Depois de tudo commitado em main, crie e passe a trabalhar numa branch
   `melhorias-tcc` para as próximas sessões.
7. Não altere lógica de negócio nesta sessão. Se achar algo quebrado, anote num
   comentário no fim deste arquivo (PLANO.md) em vez de corrigir agora.

No fim: `git status` limpo, 178 testes verdes, `vendor/bin/pint --test` verde.
Os commits devem ficar só no seu nome, sem Co-Authored-By.
```

---

## [x] Sessão 1 — Conexão com PostgreSQL (CONCLUÍDA em 28/08/2026)

O que foi implementado nesta sessão:

- **`docker-compose.yml`** — PostgreSQL 16 (`postgres:16-alpine`), volume `pgdata`,
  cria os bancos `aluga_quadra` e `aluga_quadra_test` na primeira subida (via
  `docker/postgres/init/01-create-test-database.sh`). Healthcheck configurado.
- **`.env` e `.env.example`** — bloco `DB_*` trocado de `sqlite` para `pgsql`
  (`DB_HOST=127.0.0.1`, `DB_PORT=5432`, `DB_DATABASE=aluga_quadra`,
  `DB_USERNAME=aluga`, `DB_PASSWORD=secret`), batendo com o docker-compose.
- **`phpunit.xml`** — testes apontam para a conexão `pgsql`, banco
  `aluga_quadra_test`. (Antes era `sqlite` `:memory:`.)
- **`.github/workflows/tests.yml`** — job `ci` ganhou um *service container*
  `postgres:16` e as extensões `pdo_pgsql, pgsql` no `setup-php`.
- **`docs/banco-de-dados.md`** — como subir, credenciais, comandos de migration,
  e a nota sobre a *exclusion constraint* planejada para a Sessão 3.

Validação feita:
- `php artisan migrate:fresh --seed --seeder=DemoSeeder` → 21 migrations OK, seed OK.
- Suíte completa → **178 testes passando** contra `aluga_quadra_test`.
- `php artisan db:show` → PostgreSQL 16.15, conexão `pgsql`.

**Pendências desta sessão:** movidas e detalhadas na Sessão 1.1 abaixo, que também
cobre a conexão via DBeaver.

---

## [ ] Sessão 1.1 — Validação local do PostgreSQL (sem Docker) + DBeaver

Diagnóstico feito em **05/10/2026**, revisado para **não usar Docker** (decisão do
autor, para economizar tempo de instalação/boot do Docker Desktop): o código da
Sessão 1 está todo pronto e commitado, mas nunca foi validado **nesta máquina**. O
`.env` local ainda está com `DB_CONNECTION=sqlite`. Em vez de Docker Desktop, o
PostgreSQL 16 vai ser instalado **nativamente dentro da distro WSL** (onde o PHP/
Composer/artisan já rodam — ver item K do diagnóstico). O `docker-compose.yml`
continua no repositório (é usado pelo CI e fica como alternativa documentada), mas
deixa de ser o caminho usado nesta máquina.

### Cronograma (cabe em 1 dia, dividido em 2 blocos)

| Quando | Tarefa | Quem faz | Duração |
|---|---|---|---|
| Hoje (05/10) | Instalar o PostgreSQL 16 nativo na WSL (sem Docker) e criar role/bancos | Você, manual (terminal WSL) | ~20 min |
| Hoje (05/10) | **Prompt 1** — confirmar o Postgres, corrigir `.env`, migrar, semear, testar | Claude Code | ~20 min |
| Hoje (05/10) | Instalar o DBeaver (Windows) e criar a conexão com o Postgres da WSL | Você, manual | ~15 min |
| Amanhã (06/10) | **Prompt 2** — validar persistência ponta a ponta pelos fluxos da aplicação | Claude Code | ~40 min |

### Passo manual 1 — instalar o PostgreSQL nativo na WSL (sem Docker)

Abra um terminal da **distro Ubuntu (WSL)** — não PowerShell, não Git Bash do
Windows — pois é lá que o `php artisan` do projeto roda.

1. Instalar o pacote:
   ```bash
   sudo apt update
   sudo apt install -y postgresql postgresql-contrib
   ```
2. Garantir que o serviço está de pé (a maioria das distros WSL não usa systemd por
   padrão):
   ```bash
   sudo service postgresql start
   sudo service postgresql status
   ```
   Se `systemctl` funcionar no seu WSL (`wsl.conf` com `systemd=true`), pode usar
   `sudo systemctl enable --now postgresql` para já subir sozinho a cada boot do WSL.
3. Conferir a versão (precisa ser a 16.x, igual ao CI e ao `docker-compose.yml`
   antigo):
   ```bash
   psql --version
   ```
   Se o `apt` padrão da distro trouxer outra versão major, adicione o repositório
   oficial `apt.postgresql.org` e instale `postgresql-16` explicitamente antes de
   seguir.
4. Criar o role e os dois bancos, com as mesmas credenciais que já estão em
   `.env.example` (para não precisar tocar em mais nada):
   ```bash
   sudo -u postgres psql -c "CREATE ROLE aluga WITH LOGIN PASSWORD 'secret' CREATEDB;"
   sudo -u postgres createdb -O aluga aluga_quadra
   sudo -u postgres createdb -O aluga aluga_quadra_test
   ```
5. Testar a conexão local:
   ```bash
   PGPASSWORD=secret psql -h 127.0.0.1 -U aluga -d aluga_quadra -c '\conninfo'
   ```
   O pacote padrão do Ubuntu já escuta em `127.0.0.1:5432` por padrão — não precisa
   editar `postgresql.conf` nem `pg_hba.conf` para acesso local.

### Prompt 1 — confirmar o banco nativo e validar (cole numa conversa nova do Claude Code)

```
Continuando o AlugaQuadra (Laravel 12 + Livewire 4, PostgreSQL). Decisão tomada: não
usar Docker nesta máquina para economizar tempo. O PostgreSQL 16 acabou de ser
instalado nativamente dentro da distro WSL (apt install postgresql), com o role
"aluga"/senha "secret" e os bancos aluga_quadra e aluga_quadra_test já criados
manualmente. O .env local ainda está com DB_CONNECTION=sqlite (nunca foi trocado) e o
banco nunca foi validado aqui. O docker-compose.yml continua no repo (uso do CI / 
alternativa), mas não deve ser necessário localmente a partir de agora.

Faça:
1. Confirme que o Postgres nativo responde: `pg_isready -h 127.0.0.1 -p 5432` e
   `psql -h 127.0.0.1 -U aluga -d aluga_quadra -c '\q'` (com PGPASSWORD=secret).
2. Ajuste o bloco DB_* do .env (não o .env.example, que já está certo) para:
   DB_CONNECTION=pgsql, DB_HOST=127.0.0.1, DB_PORT=5432, DB_DATABASE=aluga_quadra,
   DB_USERNAME=aluga, DB_PASSWORD=secret. Preserve as demais variáveis do .env atual
   (APP_KEY, etc).
3. Rode `php artisan migrate:fresh --seed --seeder=DemoSeeder` e confirme que todas as
   migrations aplicam sem erro.
4. Rode `php artisan db:show` e cole a saída confirmando a conexão pgsql.
5. Rode a suíte completa (`php artisan test`) e confirme que está tudo verde contra
   aluga_quadra_test.
6. Se a porta 5432 já estiver em uso por outro Postgres na máquina, documente a porta
   escolhida e ajuste DB_PORT no .env e no phpunit.xml.
7. Atualize docs/banco-de-dados.md: troque a seção "Subir / parar" para refletir que o
   caminho local agora é o PostgreSQL nativo da WSL (comandos `sudo service postgresql
   start/stop`, sem `docker compose`), mantendo uma nota curta de que o
   docker-compose.yml continua existindo e é usado pelo CI / como alternativa para
   quem preferir Docker.
8. Não altere lógica de negócio nem migrations existentes nesta sessão — só o que for
   preciso para o banco subir e os testes passarem.

No fim: pg_isready confirmando o banco nativo, php artisan db:show confirmando pgsql,
migrations aplicadas, suíte completa verde, docs/banco-de-dados.md atualizado.
```

### Passo manual 2 — conectar pelo DBeaver

1. Instale o DBeaver Community (dbeaver.io) no Windows.
2. Nova conexão > PostgreSQL. Host `127.0.0.1`, porta `5432` (ou a que você escolheu
   acima), banco `aluga_quadra`, usuário `aluga`, senha `secret` — credenciais de dev.
   O WSL2 encaminha automaticamente `127.0.0.1` do Windows para a distro, então o
   DBeaver (rodando no Windows) enxerga o Postgres nativo da WSL sem configuração
   extra. Se não conectar, confira no PowerShell `wsl hostname -I` para pegar o IP da
   distro e use-o como alternativa ao `127.0.0.1`.
3. "Test Connection" — se pedir, deixe o DBeaver baixar o driver PostgreSQL sozinho.
4. Abra o schema `public` e confirme que as tabelas do domínio aparecem (`users`,
   `quadras`, `quadra_fotos`, `reservas`, `salas`, `participacao_salas`, `produtos`,
   `pedidos`, `itens_pedido`, etc.) e que `users` já tem as contas do `DemoSeeder`.
5. Opcional: crie uma segunda conexão para `aluga_quadra_test` (mesmas credenciais,
   outro banco) para inspecionar o estado durante os testes.

### Prompt 2 — validar persistência ponta a ponta (cole numa conversa nova)

```
Continuando o AlugaQuadra. O PostgreSQL nativo (instalado na WSL, sem Docker) já está
rodando localmente e as migrations/seed já foram aplicadas. Quero uma validação ponta
a ponta de que TODA informação da aplicação está realmente sendo persistida no
PostgreSQL, não só as tabelas vazias do seed.

Faça:
1. Suba a aplicação (`php artisan serve` e `npm run dev` se precisar de assets) e,
   usando as contas de docs/banco-de-dados.md, percorra manualmente (ou me diga os
   passos para eu percorrer) os fluxos principais: cadastro de jogador, cadastro de
   dono, reserva de quadra, criação de sala, entrada em sala com pagamento simulado,
   compra na loja, cancelamento de reserva se já existir.
2. Depois de cada fluxo, rode uma consulta (via `php artisan tinker`) confirmando que a
   linha nova aparece na tabela correta do banco aluga_quadra — liste os comandos
   equivalentes que eu posso repetir no DBeaver (aba SQL Editor) para conferir o mesmo.
3. Confirme que nenhuma gravação está indo para storage/*.sqlite ou outra conexão —
   grep por `connection(` e por menções a sqlite no código da aplicação, fora de
   testes/documentação.
4. Escreva um resumo curto em docs/banco-de-dados.md (seção nova "Validação local") com
   a data de hoje, o que foi testado e o resultado, e deixe claro que a validação foi
   feita contra o PostgreSQL nativo (sem Docker).
5. Não precisa adicionar testes automatizados novos aqui — isso já é coberto pela
   suíte existente. O objetivo é confirmar visualmente/manualmente que os dados batem
   entre app e banco.

No fim: lista dos fluxos testados com confirmação de que cada um gravou no Postgres,
e a seção nova em docs/banco-de-dados.md.
```

Depois das duas sessões, marque esta Sessão 1.1 como concluída e siga para a Sessão 2.

---

## [ ] Sessão 2 — Localização pt-BR

```
Continuando o AlugaQuadra (Laravel 12, PostgreSQL). O app é todo em português do
Brasil, mas config/app.php está com locale=en, faker_locale=en_US e timezone=UTC.
Componentes usam translatedFormat() do Carbon e os meses saem em inglês.

Faça:
1. Em .env.example e .env: APP_LOCALE=pt_BR, APP_FALLBACK_LOCALE=pt_BR,
   APP_FAKER_LOCALE=pt_BR. Defina o timezone da aplicação para "America/Recife"
   (os dados de demonstração são de Pernambuco) em config/app.php 'timezone'.
2. Garanta que o Carbon usa pt_BR: em AppServiceProvider::boot(), adicione
   `\Carbon\CarbonImmutable::setLocale(config('app.locale'));` (o projeto já usa
   Date::use(CarbonImmutable::class)). Confirme que translatedFormat('d \d\e F \d\e Y')
   passa a render "28 de agosto de 2026".
3. `php artisan lang:publish` e traduza validation.php, auth.php e passwords.php para
   pt-BR (ou instale um pacote de traduções pt-BR — verifique composer.json antes).
4. Rode a suíte inteira. Testes de data/hora podem quebrar por causa do timezone;
   ajuste os TESTES para o novo timezone, não reverta a config. Testes que esperavam
   mês em inglês passam a esperar português.
5. vendor/bin/pint no final.

Teste no navegador: tela de criar sala (data por extenso no resumo) e
/perfil > Minhas Reservas.
```

---

## [ ] Sessão 3 — Integridade de reservas (concorrência)

```
Continuando o AlugaQuadra (PostgreSQL 16). Hoje
app/Livewire/Quadras/Listagem.php::reservar() e app/Livewire/Salas/Criar.php::criar()
fazem: consulta de conflito -> if -> create, sem transação, sem lock e sem constraint
no banco. Duas requisições simultâneas reservam o mesmo horário da mesma quadra.

Agora que o banco é PostgreSQL, dá para resolver isso NO BANCO com uma exclusion
constraint, além da proteção na aplicação.

Faça:
1. Migration nova: habilite a extensão btree_gist
   (`DB::statement('CREATE EXTENSION IF NOT EXISTS btree_gist')`) e adicione em
   `reservas` uma exclusion constraint que impede sobreposição de faixa horária na
   mesma quadra para reservas não canceladas. Algo como:
   ALTER TABLE reservas ADD CONSTRAINT reservas_sem_sobreposicao
   EXCLUDE USING gist (
     quadra_id WITH =,
     tsrange((data + hora_inicio), (data + hora_fim)) WITH &&
   ) WHERE (status <> 'cancelada');
   Ajuste os nomes/tipos de coluna ao schema real (confira a migration
   create_reservas_table e add_cliente_fields). O down() remove a constraint.
   Antes de aplicar, cheque no DemoSeeder e nas factories que nenhuma linha viola.
   IMPORTANTE: isso é específico do PostgreSQL — deixe um comentário na migration
   explicando, e confirme que a suíte (que roda em PostgreSQL) continua verde.
2. Extraia "verificar conflito e criar reserva" para um único ponto reutilizável:
   um método estático em app/Models/Reserva.php (ex.:
   `Reserva::agendar(Quadra $quadra, ?int $userId, string $data, string $horaInicio, string $horaFim, ReservaStatus $status): self`)
   ou um service app/Services/Reservas/AgendadorDeReserva.php. Dentro dele:
   - DB::transaction(...);
   - Quadra::whereKey($quadra->id)->lockForUpdate()->first() para serializar por quadra;
   - a mesma query de conflito de hoje (hora_inicio < horaFim AND hora_fim > horaInicio
     AND status != cancelada);
   - se houver conflito, lançar App\Exceptions\HorarioIndisponivelException;
   - senão, criar e retornar a Reserva. Se a exclusion constraint estourar
     (QueryException com SQLSTATE 23P01), converta na mesma exceção de domínio.
3. Reescreva reservar() e Criar::criar() para chamar esse ponto único e converter a
   exceção em $this->addError(...) com EXATAMENTE as mensagens atuais (não quebrar os
   testes de asserção de texto). Em Criar::criar(), a criação da Sala fica na mesma
   transação da reserva.
4. Testes novos em tests/Feature/QuadraReservaTest.php e tests/Feature/SalaTest.php:
   - sobreposição parcial (existe 19:00–21:00, tenta 20:00–21:00) é rejeitada;
   - a exclusion constraint barra a sobreposição mesmo criando a segunda reserva por
     fora do agendador (via DB direto) — espere QueryException;
   - reserva encostada (termina 20:00, outra começa 20:00) é PERMITIDA (tsrange é
     [) por padrão).
5. Suíte inteira + pint verdes. Os 178 testes anteriores continuam passando.
```

---

## [ ] Sessão 4 — Integridade de salas e loja

```
Continuando o AlugaQuadra. Dois pontos com o mesmo padrão frágil da Sessão 3:

(a) app/Livewire/Salas/Pagamento.php::confirmarPagamento() faz
    count() >= max_participantes e depois participantes()->attach(), sem transação
    nem lock -> a sala passa da capacidade se dois usuários entram juntos.
    app/Livewire/Salas/Criar.php também dá attach() no criador sem checar.

(b) app/Livewire/Loja/Carrinho.php::finalizarPedido() cria Pedido + ItemPedido e só
    depois tenta Produto::where('id',..)->where('estoque','>=',qty)->decrement(...).
    Se o decrement afetar 0 linhas, o pedido é criado assim mesmo, sem rollback e
    sem avisar o usuário.

Faça:
1. Salas: em confirmarPagamento(), envolva checagem de vaga + attach() num
   DB::transaction, com Sala::whereKey($this->sala->id)->lockForUpdate()->first() e
   recontagem de participantes DENTRO da transação. Se cheia, retornar o mesmo
   $this->erro = 'Essa sala já está cheia.' de hoje. Recarregue $this->sala depois.
2. Loja: em finalizarPedido(), dentro da transação, capture o retorno de decrement().
   Se algum produto retornar 0, aborte (throw App\Exceptions\EstoqueInsuficienteException),
   capture no componente e mostre mensagem inline ("O produto X ficou sem estoque.
   Ajuste o carrinho e tente de novo.") sem redirect e sem limpar o carrinho.
   Só chama Carrinho::limpar() e redireciona no sucesso.
3. Antes de abrir a transação, revalide cada item do carrinho contra o estoque atual;
   se algum passou, corrija o carrinho para o máximo disponível e mostre um aviso
   pedindo pra revisar, sem finalizar.
4. Testes:
   - tests/Feature/SalaTest.php: sala com 1 vaga, estado "cheia" montado direto,
     segundo usuário chama confirmarPagamento -> erro, sem participante extra.
   - tests/Feature/CarrinhoTest.php: carrinho com 2 unidades, estoque baixado para 1
     por fora -> finalizarPedido não cria Pedido, mostra aviso, carrinho intacto.
5. Suíte inteira + pint verdes.
```

---

## [ ] Sessão 5 — README + decisões de arquitetura

```
Continuando o AlugaQuadra. O repositório não tem README. Existem
ROTEIRO-APRESENTACAO.md e este PLANO.md. Os planos antigos estão em docs/historico/.

Crie README.md na raiz, em português, com:
1. Uma frase do que é o AlugaQuadra e para quem.
2. Stack: Laravel 12, Livewire 4, Laravel Fortify (auth + 2FA), Pest, Vite,
   Tailwind 4 + Flux (área de settings) e Bootstrap 5 + CSS próprio (site público).
   PostgreSQL 16 via docker-compose.
3. Setup local passo a passo: `docker compose up -d`, `composer install`,
   `cp .env.example .env`, `php artisan key:generate`,
   `php artisan migrate --seed --seeder=DemoSeeder`, `npm ci`, `npm run build`.
   Inclua a tabela de contas de demonstração (jogador@demo.com etc., senha "password").
   Aponte para docs/banco-de-dados.md.
4. Mapa de funcionalidades por papel (Jogador / Dono de quadra / Admin) com as rotas
   principais de cada uma.
5. Estrutura de pastas relevante (app/Livewire por contexto, app/Services/Overpass,
   app/Policies, app/Enums, app/Support/Carrinho).
6. Como rodar testes e lint (`php artisan test`, `vendor/bin/pint`). Cite que os
   testes rodam contra PostgreSQL (banco aluga_quadra_test do docker-compose).
7. Seção "Decisões de arquitetura (ADR resumido)", cada uma em 3–5 linhas:
   - Duas stacks de CSS convivem (site herdado em Bootstrap; settings usa o starter
     kit Livewire/Flux); unificar é trabalho futuro.
   - Pagamento é simulado: reserva e pedido confirmam na hora, sem gateway. A string
     PIX exibida é ilustrativa, não é um payload EMV válido.
   - "Quadras próximas" consulta o OpenStreetMap via Overpass API e mostra quadras do
     mundo real ao redor do usuário — prova de integração externa, independente do
     cadastro de quadras da plataforma.
   - Papéis via coluna `role` + enum + middleware, não um pacote de ACL.
   - PostgreSQL escolhido por ser um SGBD relacional completo (constraints de
     exclusão para regras de agenda, tipos ricos); migrations são portáveis.
8. Seção "Limitações conhecidas / trabalho futuro" (consolidar a seção 4 deste plano
   + o que sobrar).

Não invente recursos — confirme cada afirmação lendo o código.
```

---

## [ ] Sessão 6 — Autorização e políticas

```
Continuando o AlugaQuadra. Só existem QuadraPolicy e ReservaPolicy. Não há policy
para Sala, e a confirmação de pedido (routes/web.php, rota loja.pedido.confirmacao)
valida o dono com um abort_unless inline.

Faça:
1. app/Policies/SalaPolicy.php no estilo das outras:
   - update/cancelar: criador da sala OU admin;
   - entrar: usuário autenticado que ainda não participa, sala não cheia nem no
     passado (mova essa regra pra cá se hoje está espalhada no componente).
   Registre em AppServiceProvider::boot() (Gate::policy(...)).
2. Aplique a policy onde há checagem manual: Salas/Pagamento.php e qualquer lugar que
   feche/edite sala usam $this->authorize(...). Se ainda não há tela de cancelar sala
   pelo criador, só deixe a policy pronta (a Sessão 7 mexe em cancelamento).
3. app/Policies/PedidoPolicy.php com view (dono do pedido OU admin). Troque o
   abort_unless inline por ela. Se a rota é closure, considere um Livewire full-page
   App\Livewire\Loja\Confirmacao com $this->authorize('view', $pedido) no mount —
   siga o padrão das outras telas da loja.
4. Revise app/Livewire/Admin/*: confirme que são só leitura; qualquer escrita exige
   role admin via policy/authorize, não só o middleware de rota.
5. Testes novos tests/Feature/SalaPolicyTest.php e tests/Feature/PedidoPolicyTest.php
   no formato de QuadraPolicyTest.php: criador cancela/edita a própria sala, terceiro
   não; dono do pedido vê a confirmação, terceiro toma 403; admin vê tudo.
6. Suíte + pint verdes.
```

---

## [ ] Sessão 7 — Cancelamento de reserva pelo jogador

```
Continuando o AlugaQuadra. O enum ReservaStatus tem Cancelada, mas só dono/admin
cancelam (app/Livewire/Painel/Reservas/Listagem.php). O jogador não desmarca a
própria reserva.

Faça:
1. Regra: o jogador cancela a própria reserva se ela está no futuro e faltam pelo
   menos X horas para o início (X = 3, como constante nomeada). Reserva já cancelada
   ou passada não pode. Reserva vinculada a uma Sala (reserva_id preenchido) NÃO pode
   ser cancelada por aqui — bloqueie com mensagem clara.
2. Método em ReservaPolicy (cancelarComoJogador) + ação em
   app/Livewire/Perfil/MinhasReservas.php: botão "Cancelar" nas reservas futuras
   elegíveis, com wire:confirm, mudando o status para Cancelada e recomputando a lista.
3. resources/views/livewire/perfil/minhas-reservas.blade.php: botão só quando a
   policy permite; reservas canceladas com badge "Cancelada" e sem botão.
4. Confirme em Painel/Reservas/Listagem.php e Painel/Financeiro.php que uma reserva
   cancelada pelo jogador some do faturamento e da aba correta. Ajuste se preciso.
5. Testes em tests/Feature/Perfil/MinhasReservasTest.php: cancela reserva futura
   elegível; não cancela a menos de 3h; não cancela reserva de sala; reserva
   cancelada não entra no faturamento do dono.
6. Suíte + pint verdes. Atualize ROTEIRO-APRESENTACAO.md (passo Meu Perfil).
```

---

## [ ] Sessão 8 — Consolidação visual / apresentação offline

```
Continuando o AlugaQuadra. O site público (layouts/bootstrap.blade.php) carrega
Bootstrap 5 CSS/JS e bootstrap-icons por CDN (jsdelivr). Sem internet no dia da
defesa, o site fica sem estilo. Há divergência visual entre o site (Bootstrap +
public/css/style.css) e as telas de settings (Tailwind/Flux).

Objetivo: apresentação 100% offline e visual coeso, sem reescrever o site.

Faça:
1. Traga o Bootstrap para o build local:
   - npm i bootstrap@5.3 bootstrap-icons (as versões que já estão no HTML).
   - importe no pipeline do Vite (resources/css/app.css ou um novo site.css) e no
     resources/js/app.js o bundle JS do Bootstrap.
   - troque os <link>/<script> de CDN em layouts/bootstrap.blade.php por @vite([...]).
     Confirme que os ícones bi-* continuam aparecendo (fonte bootstrap-icons copiada
     pelo Vite — pode precisar de config de assets).
   - npm run build e teste o site inteiro com o wi-fi desligado.
2. Se algo do Flux/Tailwind puxa recurso externo, verifique
   resources/views/partials/head.blade.php e resolva igual.
3. Alinhe tokens de cor: extraia a cor laranja da marca e raios/sombras repetidos de
   public/css/style.css para variáveis CSS no :root; troque valores hardcoded por
   var(--...). Não redesenhe nada, só remova duplicação. Documente as variáveis num
   comentário no topo do style.css.
4. Suíte + pint (mudanças de asset não devem quebrar nada).

Teste: wi-fi desligado, php artisan serve, percorra cadastro -> quadras -> reserva
-> sala -> loja -> perfil. Nenhuma tela sem CSS ou sem ícones.
```

---

## [ ] Sessão 9 — Limpeza final e CI

```
Última sessão da Trilha 1. Faxina e fechamento.

Faça:
1. Remova tests/Feature/ExampleTest.php e tests/Unit/ExampleTest.php. Rode a suíte
   para confirmar a nova contagem.
2. Decida a verificação de e-mail: hoje /dashboard exige `verified` mas o cadastro
   loga sem verificar e MustVerifyEmail está comentado no User. Escolha UMA:
   (a) tirar o middleware `verified` de /dashboard (mais simples, coerente com o
       cadastro que loga direto); ou
   (b) ativar de verdade: descomentar MustVerifyEmail, disparar o e-mail no registro,
       ajustar o ROTEIRO para usar Mailpit/tinker na demo.
   Faça (a) salvo se quiser mostrar verificação na banca. Ajuste os testes.
3. Alinhe a versão do PHP: composer.json pede ^8.2, o CI roda 8.4/8.5. Rode a suíte
   num PHP 8.4 (o Herd tem) e confirme. Se quiser, fixe "php": "^8.3" no composer.json.
4. Revise .github/workflows/tests.yml e lint.yml: rodam em push/PR para main, cache
   do Composer ligado, service do PostgreSQL sobe antes dos testes (já configurado na
   Sessão 1), npm run build antes dos testes se algum teste de view depender do
   manifest do Vite.
5. vendor/bin/pint no projeto inteiro; commite o que ele ajustar.
6. Passada manual completa do ROTEIRO-APRESENTACAO.md do começo ao fim, com o banco
   recém-semeado. Corrija só o que for rápido e visual; o resto vira "trabalho
   futuro" no README.
7. Atualize este PLANO.md marcando as sessões concluídas e movendo o que não foi
   feito para uma seção "Pendências" no fim.

No fim: git status limpo na branch melhorias-tcc, suíte verde, pint verde, e um
merge (ou PR) de melhorias-tcc para main.
```

---

## Trilha 2 — TCC / monografia

Produz artefatos acadêmicos e conformidade. Depende só da Sessão 0.

## [ ] Sessão A — Consolidar cadastro + validações brasileiras

```
Continuando o AlugaQuadra (Laravel 12 + Livewire 4 + Fortify, PostgreSQL). Hoje há
DOIS fluxos de cadastro vivos:
- Fortify: FortifyServiceProvider registra Fortify::registerView() e
  createUsersUsing(App\Actions\Fortify\CreateNewUser::class). CreateNewUser grava só
  name/email/password (sem role, sem perfil). A rota POST /register está no ar.
- Aplicação: App\Livewire\Auth\Registrar (/registro) coleta o perfil completo (CPF,
  nascimento, sexo, endereço, CEP, cidade, estado, telefone) e
  App\Livewire\Auth\RegistrarDono (/cadastro-dono) coleta CNPJ + estabelecimento.

A validação de documentos é só de tamanho: 11 dígitos no CPF, 14 no CNPJ, nenhum
valida dígito verificador; `estado` aceita quaisquer 2 letras; sem idade mínima.

Faça:
1. Decida UM caminho de cadastro de jogador e tire o outro de circulação.
   Recomendado: manter os componentes Livewire e desligar o registro do Fortify —
   em config/fortify.php remova Features::registration(), apague
   App\Actions\Fortify\CreateNewUser e as linhas createUsersUsing()/registerView()
   no FortifyServiceProvider. Confirme que `php artisan route:list` não lista mais
   POST /register. Ajuste/remova tests/Feature/Auth/RegistrationTest.php e garanta
   que os testes do fluxo que sobrou cobrem tudo.
2. app/Rules/Cpf.php e app/Rules/Cnpj.php (implements ValidationRule): rejeitam
   sequências repetidas e validam os dois dígitos por mód 11.
3. app/Rules/Uf.php (ou Rule::in) contra a lista das 27 UFs.
4. Em Registrar: troque a checagem manual de CPF pela regra Cpf, valide `estado` com
   Uf, e adicione idade mínima (IDADE_MINIMA = 16 como constante nomeada), rejeitando
   data_nascimento que não atinja, com mensagem clara. Em RegistrarDono: Cnpj + Uf.
5. Centralize as regras de perfil em app/Concerns/ProfileValidationRules.php para os
   dois componentes não duplicarem, no estilo dos métodos que já existem lá.
6. Testes em tests/Unit/Rules/ (CpfTest, CnpjTest, UfTest, casos válidos e inválidos
   conhecidos) + ajustes nos testes de cadastro para um CPF/CNPJ válido de teste.
   Suíte inteira + pint.

Anote no futuro README qual fluxo de cadastro é o oficial.
```

---

## [ ] Sessão B — Adequação à LGPD

```
Continuando o AlugaQuadra. O sistema coleta dados pessoais (CPF, data de nascimento,
endereço, CEP, telefone; CNPJ e endereço do estabelecimento para donos) e não tem
política de privacidade, consentimento nem base legal registrada. Objetivo:
tratamento mínimo viável e defensável + material para o capítulo de LGPD da
monografia. Não precisa virar um DPO — precisa ser coerente e documentado.

Faça:
1. Minimização: revise campo a campo do cadastro de jogador o que é REALMENTE
   necessário para "reservar quadra e entrar em salas". Proposta salvo objeção:
   manter obrigatórios name, email, telefone, cidade/estado; tornar CPF, endereço,
   CEP, data de nascimento e sexo OPCIONAIS (nullable já no banco), com um texto
   curto explicando por que cada um é pedido. Ajuste Registrar, as regras e os
   testes. Se algum dado for exigido por regra de negócio real, escreva a
   justificativa num comentário.
2. Consentimento: checkbox obrigatório "Li e aceito a Política de Privacidade e os
   Termos de Uso" (com link) no cadastro (jogador e dono); grave consentimento_em
   (timestamp) e consentimento_versao (string) em users via migration. Sem aceite,
   não cria a conta.
3. Páginas estáticas /politica-de-privacidade e /termos-de-uso (Blade no layout do
   site): quais dados, para quê, base legal (execução de contrato + consentimento),
   retenção, com quem é compartilhado (ninguém; a Overpass API NÃO recebe dado
   pessoal — deixar explícito), e como exercer os direitos.
4. Direito de eliminação: já existe exclusão de conta (Fortify delete-user).
   Documente e teste o efeito em cascata (reservas, pedidos, participações, quadras
   do dono). Decida se reserva/pedido devem ser anonimizados em vez de apagados
   (para o dono não perder histórico financeiro); se sim, ao excluir o usuário setar
   user_id nulo + copiar o nome para cliente_nome (campo que já existe em reservas).
   Teste esse caminho.
5. Direito de acesso: botão "Baixar meus dados" no perfil gerando um JSON com os
   dados do usuário e suas reservas/pedidos/salas. Simples, sem fila.
6. docs/lgpd.md: inventário de dados pessoais (tabela: campo, finalidade, base legal,
   retenção), fluxo de consentimento, direitos atendidos. Base do capítulo da
   monografia.
7. Suíte + pint verdes. Atualize ROTEIRO-APRESENTACAO.md (aceite no cadastro +
   "Baixar meus dados").
```

---

## [ ] Sessão C — Documentação de engenharia (`docs/`)

```
Continuando o AlugaQuadra. A monografia precisa de artefatos de engenharia de
software e a maior parte deriva do código. Use Markdown + Mermaid (renderiza no
GitHub) para tudo ficar versionável e exportável como imagem depois.

Leia antes de escrever: database/migrations/*, app/Models/*, app/Enums/*,
routes/*.php, app/Livewire/** e app/Policies/*. Não invente entidade nem regra que
não esteja no código.

Faça (um arquivo por item, em docs/):
1. docs/modelo-de-dados.md: diagrama ER em Mermaid (erDiagram) com todas as tabelas
   de domínio (users, quadras, quadra_fotos, reservas, salas, participacao_salas,
   produtos, pedidos, itens_pedido), relacionamentos e cardinalidades. Abaixo, uma
   tabela por entidade com os campos e o significado de cada um. Mencione a exclusion
   constraint de reservas (Sessão 3) como regra de integridade no nível do banco.
2. docs/casos-de-uso.md: diagrama de casos de uso (Mermaid) para Jogador, Dono de
   Quadra, Administrador e Visitante. Cada caso de uso em texto com pré-condição,
   fluxo principal e fluxo alternativo relevante (ex.: "Reservar quadra" com o
   alternativo "horário em conflito").
3. docs/requisitos.md: requisitos funcionais numerados (RF01…) rastreáveis a um
   componente/rota, e não-funcionais (RNF01…) — segurança (papéis, rate limit de
   login, hash de senha), usabilidade (pt-BR, máscaras), confiabilidade (transações
   e exclusion constraint nas reservas), portabilidade, LGPD (aponta para
   docs/lgpd.md).
4. docs/arquitetura.md: diagrama de camadas (Mermaid) Navegador → Rotas →
   Componentes Livewire → (Policies / Services / Models) → PostgreSQL, com a Overpass
   API como sistema externo. Um ou dois parágrafos sobre o padrão (Livewire full-page
   como controlador, Services para integração externa, Enums para valores de
   domínio, Policies para autorização) e por que PostgreSQL/Fortify.
5. docs/README.md: índice curto linkando os arquivos, com a nota de que os diagramas
   Mermaid podem ser exportados como PNG/SVG para a monografia.
6. Linke docs/ a partir do README.md principal (Sessão 5). Se ainda não existir,
   deixe um TODO nele.

Não mexa em código de aplicação. Rode a suíte no fim só para garantir que nada foi
tocado por engano.
```

---

## [ ] Sessão D — Metodologia e evidência de testes

```
Continuando o AlugaQuadra. A suíte tem ~178 testes Pest passando (contra PostgreSQL),
mas não há medição de cobertura nem um texto que explique a estratégia.

Faça:
1. Cobertura: confirme que pcov ou xdebug está disponível (o CI usa xdebug).
   Adicione um script "test:coverage" no composer.json rodando
   `pest --coverage --min=70` (ajuste o mínimo para um pouco abaixo do valor real —
   meça uma vez). Adicione --coverage-clover para gerar coverage.xml e ignore-o no
   .gitignore.
2. No .github/workflows/tests.yml, adicione um passo que roda a cobertura e publica o
   resumo no job summary (o pest já imprime a tabela). Sem serviço externo.
3. docs/testes.md: a pirâmide adotada (muitos testes de feature HTTP+Livewire, poucos
   unitários nos pontos de lógica pura — Overpass, regras de CPF, Carrinho), por que
   Pest, o uso de RefreshDatabase, factories e seeders de teste, e o fato de os
   testes rodarem no mesmo SGBD da aplicação (PostgreSQL). Inclua a tabela de
   cobertura por diretório e comente os pontos descobertos.
4. docs/rastreabilidade.md: tabela ligando cada RF de docs/requisitos.md ao(s)
   arquivo(s) de teste que o exercita(m). Onde não houver teste, marque como lacuna.
5. Capturas de tela: rode o app com o DemoSeeder e salve em docs/img/ prints dos
   fluxos principais (home, listagem de quadras com filtro, reserva confirmada,
   criar sala, loja, carrinho, confirmação de pedido, painel do dono, perfil).
   Referencie em docs/casos-de-uso.md. (Ou deixe a lista do que capturar e faça à mão.)
6. Suíte + pint verdes.
```

---

## [ ] Sessão E — Ambiente reproduzível para avaliação

```
Última sessão da Trilha de TCC. O projeto já tem docker-compose.yml para o banco.
Falta empacotar o "sobe em um comando" para a banca/avaliador.

Faça:
1. Escolha e implemente UMA das opções, documentando no README:
   (a) Docker completo: acrescente ao docker-compose.yml um serviço `app` (PHP-FPM +
       Nginx, ou `php artisan serve`), de forma que `docker compose up` suba banco +
       app. Documente `docker compose exec app php artisan migrate --seed`. OU
   (b) Script à prova de falha sem app em Docker (só o banco): bin/setup.sh e
       bin/setup.ps1 rodando docker compose up -d, composer install, cópia do .env,
       key:generate, migrate:fresh, db:seed --class=DemoSeeder, npm ci, npm run
       build, e imprimindo as contas de demo no fim.
   Recomendo (b) se a defesa é na sua máquina; (a) se o avaliador roda na dele.
2. Revise o .env.example: roda de cara com o docker-compose sem editar nada.
3. Seed determinístico: no DemoSeeder, fixe uma seed do Faker (fake()->seed(12345))
   para os 4 jogadores extras saírem iguais toda vez.
4. docs/como-avaliar.md: "Avalie em 5 minutos" — os comandos exatos, as três contas,
   e um roteiro mínimo (login como jogador → reservar → criar sala → comprar na loja).
5. Checklist de contingência da defesa (seção no ROTEIRO): sem internet (Sessão 8 já
   resolve o CSS), banco corrompido (migrate:fresh + seed), porta 8000/5432 ocupada.
6. Passada final: clone do zero num diretório limpo, siga só o README, confirme que
   sobe. Corrija o que travar.
7. Suíte + pint verdes. Merge das duas trilhas para main.
```

---

## 3. Ordem recomendada de execução

```
Semana 1:  0 → 1(✅, só validar) → 2 → 3 → 4
Semana 2:  5 → 6 → 7 → 8            (em paralelo, se quiser: A)
Semana 3:  9 → B → C → D → E
```

Sessão 0 é bloqueante para todas. Sessões 2–4 mexem em migrations e concorrência —
não pule a ordem. A Trilha 2 (A–E) só precisa da Sessão 0; encaixe conforme o
cronograma da monografia.

---

## 4. Trabalho futuro (não planejado aqui — decidir caso a caso)

- Gateway de pagamento real (Stripe / Mercado Pago / PIX de verdade).
- CRUD de produtos para dono/admin (padrão: `app/Livewire/Painel/Quadras/Formulario.php`).
- Avaliações e notas de quadras.
- Notificações por e-mail (reserva confirmada, sala cheia, lembrete de jogo).
- Visão de calendário/agenda da quadra no lugar do dropdown de horários.
- Aba "Quadras Favoritas" no perfil (CSS já existe, funcionalidade não).
- Unificar a stack de CSS (migrar o site público para Tailwind, ou o inverso).
- Reescrever `routes/web.php` (closures → Livewire full-page / controllers de ação única).

---

## 5. Notas de execução

(Use esta seção para anotar, durante as sessões, o que ficou pendente ou quebrado.)

- **05/10/2026 — Sessão 1.1, Prompt 1 concluído.** PostgreSQL 16 nativo instalado na
  WSL (sem Docker), role/bancos criados, `.env` ajustado, 47 migrations + seed OK,
  `php artisan test` **359/359 verde**. No caminho, achamos e corrigimos o `.env`
  local que ainda tinha `APP_LOCALE=en`/`APP_FAKER_LOCALE=en_US` — isso fazia o
  Faker gerar CEPs em formato americano que não cabiam em `quadras.cep varchar(9)`
  (invisível no SQLite, que não aplica limite de tamanho). Corrigido sincronizando o
  `.env` com o `.env.example` (`pt_BR`). Isso adianta o `.env` da Sessão 2, mas a
  tradução de `validation.php`/`auth.php`/`passwords.php` e o
  `CarbonImmutable::setLocale()` no `AppServiceProvider` **continuam pendentes** —
  não pular a Sessão 2 por causa disso.
  **Pegadinha descoberta e corrigida:** um container Docker da Sessão 1 original
  (`aluga_quadra_pg`) estava rodando havia 5 semanas sem ninguém saber, ocupando a
  porta 5432 — o diagnóstico "Docker não está instalado" estava errado. Isso fez o
  pacote `postgresql` nativo subir sozinho na porta 5433, e as primeiras rodadas de
  migration/seed/teste/DBeaver foram todas (sem querer) para o container Docker, não
  para o nativo. Resolvido: `docker stop aluga_quadra_pg` (parado, não removido —
  dados antigos preservados no volume) + porta do nativo corrigida para 5432 em
  `postgresql.conf`. Revalidado depois: nativo de verdade (`x86_64-pc-linux-gnu`,
  sem Alpine/musl), 359/359 testes verdes. Ver detalhes em
  `docs/banco-de-dados.md`. DBeaver reconectado e confirmado contra o nativo
  (152ms, `x86_64-pc-linux-gnu`), 28 tabelas visíveis em `public`. Falta rodar o
  Prompt 2 (validação ponta a ponta pelos fluxos da aplicação).
-
