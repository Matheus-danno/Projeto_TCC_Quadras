# Requisitos

Cada requisito aponta para onde está implementado e para os testes que o
verificam (rastreabilidade). Caminhos de teste relativos a `tests/Feature/`. Os
casos de uso (UCxx) estão em [casos-de-uso.md](casos-de-uso.md).

## Requisitos funcionais

| ID | Requisito | Implementação | Testes |
|---|---|---|---|
| RF01 | Cadastrar jogador com CPF válido (mód. 11), UF oficial, idade mínima de 16 anos e aceite dos termos | `Livewire/Auth/Registrar` | `Auth/RegistroCompletoTest` |
| RF02 | Cadastrar dono (pessoa física ou jurídica) com documento de identidade, dados de recebimento e aceite da comissão e dos termos | `Livewire/Auth/RegistrarDono` | `Auth/RegistroDonoTest` |
| RF03 | Autenticar com e-mail e senha, recuperar senha e usar 2FA | Fortify, `FortifyServiceProvider` | `Auth/AuthenticationTest`, `Auth/PasswordResetTest`, `Auth/TwoFactorChallengeTest`, `Settings/TwoFactorAuthenticationTest` |
| RF04 | Restringir cada área ao seu papel (jogador, dono, admin) | `EnsureUserHasRole`, `routes/painel.php`, `routes/admin.php` | `RoleAccessTest`, `Admin/AcessoAdminTest` |
| RF05 | Buscar quadras aprovadas por cidade, nome, esporte e cobertura | `Livewire/Quadras/Listagem` | `QuadraReservaTest` |
| RF06 | Mostrar só horários livres, dentro do funcionamento do dono | `Quadra::horarioDisponivel`, `horariosDisponiveis` | `QuadraDisponibilidadeTest` |
| RF07 | Reservar quadra sem permitir sobreposição de horário, mesmo com requisições simultâneas | `Reserva::agendarSemConflito`, constraint `reservas_sem_sobreposicao` | `QuadraReservaTest`, `ReservaSemSobreposicaoTest` |
| RF08 | Pagar reserva (Pix, cartão ou créditos, simulados) | `Livewire/Quadras/Pagamento` | `ReservaCancelamentoTest`, `PedidoPolicyTest` |
| RF09 | Cancelar reserva respeitando o prazo do dono, com reembolso em crédito ou estorno | `Livewire/Perfil/MinhasReservas`, `Reserva::podeCancelar` | `ReservaCancelamentoTest`, `Perfil/MinhasReservasTest` |
| RF10 | Criar sala gravando reserva e sala na mesma transação | `Livewire/Salas/Criar` | `SalaTest` |
| RF11 | Entrar em sala sem ultrapassar o limite de vagas; aprovação manual pelo organizador | `Sala::entrarComo`, `Sala::aprovarPedido` | `SalaTest`, `Salas/VagasConcorrentesTest`, `SalaPolicyTest` |
| RF12 | Chat da sala e avaliação de jogadores e quadra após a partida | `Livewire/Salas/Grupo`, `Livewire/Salas/Avaliar` | `SalaChatTest`, `Salas/AvaliarTest` |
| RF13 | Ver quadras próximas pelo OpenStreetMap | `Services/Overpass`, `Livewire/Quadras/Proximas` | `QuadrasProximasTest`, `Unit/OverpassQuadraFinderTest` |
| RF14 | Comprar na loja sem vender além do estoque; um pedido por estabelecimento | `Livewire/Loja/Carrinho`, `Support/Carrinho` | `CarrinhoTest` |
| RF15 | Ver confirmação (comprador) e nota (comprador ou vendedor) do pedido | `PedidoPolicy`, `routes/web.php` | `PedidoPolicyTest`, `CarrinhoTest` |
| RF16 | Conversar com o dono sobre uma quadra ou um pedido | `Livewire/Perfil/Mensagens`, `Livewire/Painel/Mensagens` | `ConversaTest`, `Perfil/MensagensTest`, `Painel/MensagensTest` |
| RF17 | Editar perfil, preferências de notificação e cartões salvos | `Livewire/Perfil/*` | `Perfil/EditarTest`, `Perfil/PreferenciasNotificacaoTest`, `Perfil/PagamentosTest`, `NotificacoesTest` |
| RF18 | Baixar os próprios dados pessoais (LGPD) | `Services/Privacidade/ExportadorDeDadosPessoais` | `Privacidade/MeusDadosTest` |
| RF19 | Dono gerencia quadras, fotos, horários e exceções | `Livewire/Painel/Quadras/*`, `Painel/Configuracoes` | `Painel/QuadrasTest`, `Painel/QuadrasFormularioTest`, `Painel/QuadrasDetalheTest`, `Painel/ConfiguracoesTest` |
| RF20 | Dono vê agenda e reservas por situação, confirma e cancela | `Livewire/Painel/Reservas/*` | `Painel/ReservasTest`, `Painel/ReservasAgendaTest` |
| RF21 | Dono agenda manualmente para cliente com ou sem conta | `Livewire/Painel/AgendamentoManual/Criar` | `Painel/AgendamentoManualTest` |
| RF22 | Dono acompanha faturamento com a comissão descontada | `Livewire/Painel/Financeiro`, `Painel/Dashboard` | `Painel/FinanceiroTest`, `Painel/DashboardTest` |
| RF23 | Dono gerencia produtos, estoque e pedidos da loja | `Livewire/Painel/Loja/*` | `Painel/LojaTest`, `Painel/LojaFormularioTest`, `Painel/LojaDetalheTest`, `Painel/LojaPedidosTest` |
| RF24 | Admin aprova, rejeita ou pede ajustes em donos e quadras, com histórico | `Services/Aprovacao`, `Livewire/Admin/Aprovacoes/*` | `Aprovacao/CadastroAprovacaoTest`, `Admin/QuadraAprovacaoTest` |
| RF25 | Admin define a comissão (só afeta reservas futuras) e registra repasses | `ComissaoConfiguracao`, `Livewire/Admin/Financeiro/*` | `Admin/ComissaoReservaTest`, `Admin/RepasseTest` |
| RF26 | Admin bloqueia usuário e a sessão dele é encerrada | `EnsureUserIsNotBlocked`, `Livewire/Admin/Usuarios/*` | `Admin/UsuarioBloqueioTest` |

## Requisitos não funcionais

| ID | Categoria | Requisito | Como é atendido |
|---|---|---|---|
| RNF01 | Segurança | Senhas nunca em texto | Hash bcrypt (Laravel) |
| RNF02 | Segurança | Limitar tentativas de login | `RateLimiter` de `login` e `two-factor` no `FortifyServiceProvider` |
| RNF03 | Segurança | Cada usuário só acessa os próprios registros | *Policies* (`app/Policies`) e middleware de papel; testes `*PolicyTest` |
| RNF04 | Segurança | Autenticação em dois fatores opcional | Fortify 2FA (TOTP) |
| RNF05 | Confiabilidade | Sem reservas duplicadas nem salas acima da capacidade sob concorrência | Transações com `lockForUpdate` + *exclusion constraint*; `ReservaSemSobreposicaoTest`, `VagasConcorrentesTest` |
| RNF06 | Confiabilidade | Checkout atômico: ou o pedido inteiro é gravado, ou nada | `DB::transaction` + decremento condicional de estoque |
| RNF07 | Usabilidade | Interface em português do Brasil, datas por extenso e fuso de Brasília | `APP_LOCALE=pt_BR`, `lang/pt_BR`, `timezone=America/Sao_Paulo` |
| RNF08 | Usabilidade | Máscaras e mensagens de erro em português nos formulários | Máscaras nas views; mensagens nos componentes |
| RNF09 | Disponibilidade | Funcionar sem internet (exceto mapa e quadras próximas) | Bootstrap, ícones e fontes empacotados pelo Vite |
| RNF10 | Resiliência | Falha da Overpass API não derruba a tela | *retry*, timeout e cache no `OverpassClient`; `OverpassIndisponivelException` tratada |
| RNF11 | Portabilidade | Ambiente reproduzível | `docker-compose.yml`, `scripts/configurar-banco.sh`, [como-avaliar.md](como-avaliar.md) |
| RNF12 | Manutenibilidade | Código padronizado e testado automaticamente | Pint no CI (`lint.yml`); Pest + cobertura no CI (`tests.yml`) |
| RNF13 | Privacidade | Adequação à LGPD | Consentimento versionado, exportação de dados, exclusão de conta; ver [lgpd.md](lgpd.md) |

## Lacunas conhecidas

- **RF08:** o pagamento é simulado; não há teste de integração com gateway, porque não há gateway.
- **RF13:** os testes usam respostas simuladas da Overpass (`Http::fake` e dublês do cliente), não a API real.
- **RNF09:** a verificação "sem internet" foi feita manualmente (sem referências a CDN no HTML); não há teste automatizado.
