# Adequação à LGPD

Como o AlugaQuadra trata dados pessoais, à luz da Lei Geral de Proteção de Dados
(Lei nº 13.709/2018). Este documento descreve o que o código faz hoje; os textos
apresentados ao usuário estão em `resources/views/politica-privacidade.blade.php`
(rota `/politica-de-privacidade`) e `resources/views/termos-uso.blade.php`
(rota `/termos-de-uso`).

## Inventário de dados pessoais

| Dado | Quem informa | Onde fica | Finalidade | Base legal (art. 7º) |
|---|---|---|---|---|
| Nome, e-mail, telefone | Todos | `users` | Identificar a conta, contato sobre reservas e pedidos | V — execução de contrato |
| Senha | Todos | `users.password` (hash bcrypt) | Autenticação | V — execução de contrato |
| CPF | Jogador; dono pessoa física | `users.cpf` | Identificação única da conta, evitar contas duplicadas | V — execução de contrato |
| Data de nascimento | Jogador | `users.data_nascimento` | Verificar a idade mínima (16 anos) | V — execução de contrato |
| Sexo | Jogador | `users.sexo` | Composição de salas/partidas | V — execução de contrato |
| Endereço (logradouro, número, complemento, bairro, CEP, cidade, UF) | Todos | `enderecos` (1:1 com `users`) | Sugerir quadras da região; endereço do estabelecimento | V — execução de contrato |
| CNPJ, nome do estabelecimento | Dono pessoa jurídica | `users` | Identificar o estabelecimento | V — execução de contrato |
| Documento de identidade (imagem/PDF) | Dono | `documentos` + disco `local` (privado) | Análise do cadastro antes de publicar quadras | V e IX — contrato e legítimo interesse (prevenção a fraude) |
| Chave Pix, nome e CPF/CNPJ do titular | Dono | `users` | Repasse dos valores recebidos | V — execução de contrato |
| Cartões salvos (titular, bandeira, 4 últimos dígitos, validade) | Jogador | `cartoes` | Agilizar pagamentos (simulados) | V — execução de contrato |
| Reservas, salas, pedidos, mensagens, avaliações | Gerados pelo uso | tabelas respectivas | Prestação do serviço | V — execução de contrato |
| Preferências de notificação | Todos | `noticacao_usuarios` | Respeitar o que o usuário quer receber | V — execução de contrato |
| Data e versão do aceite | Todos | `users.consentimento_em`, `users.consentimento_versao` | Provar o consentimento (art. 8º, §2º) | I — consentimento |
| Localização aproximada | Jogador, opcional | não é gravada | Busca de quadras próximas | I — consentimento (permissão do navegador) |

Não se armazena número completo de cartão nem código de segurança: o pagamento
é simulado (ver README, "Decisões de arquitetura").

## Consentimento

- Os dois cadastros (`App\Livewire\Auth\Registrar` e `RegistrarDono`) exigem o
  checkbox "Li e aceito a Política de Privacidade e os Termos de Uso"
  (componente `resources/views/components/aceite-privacidade.blade.php`, regra
  `accepted`). Sem o aceite, a conta não é criada.
- No momento do aceite são gravados `consentimento_em` (timestamp) e
  `consentimento_versao` (valor de `config('privacidade.versao')`). Ao alterar os
  textos legais, atualize `config/privacidade.php`: contas novas passam a
  registrar a nova versão, e é possível saber quem aceitou qual texto.
- Contas criadas antes desta funcionalidade ficam com os campos nulos.

## Idade mínima

O cadastro de jogador rejeita quem tem menos de 16 anos
(`Registrar::IDADE_MINIMA`). Abaixo dessa idade, o art. 14 exigiria
consentimento específico de um dos pais ou responsável, fluxo que a plataforma
não oferece.

## Compartilhamento com terceiros

| Destinatário | O que recebe | Quando |
|---|---|---|
| Overpass API (OpenStreetMap) | Latitude e longitude aproximadas, raio de busca | Busca de "quadras próximas" (`App\Services\Overpass`) |
| Nominatim (OpenStreetMap) | Endereço da quadra | Cadastro/edição de quadra pelo dono (`App\Services\Geocoding\NominatimClient`) |
| Dono da quadra | Nome, telefone e horário da reserva; pedidos da loja dele | Ao reservar ou comprar |
| Participantes de uma sala | Nome, foto e nível | Ao entrar na sala |

Nenhum identificador do usuário (nome, e-mail, CPF) é enviado aos serviços do
OpenStreetMap.

## Direitos do titular (art. 18)

| Direito | Como é atendido |
|---|---|
| Confirmação e acesso (II) | Botão **Baixar meus dados** em Editar Perfil (rota `perfil.meus-dados`) |
| Portabilidade (V) | O mesmo arquivo, em JSON estruturado |
| Correção (III) | Editar Perfil (`App\Livewire\Perfil\Editar`) e Configurações do painel do dono |
| Eliminação (VI) e revogação do consentimento (IX) | Exclusão de conta nas configurações; apaga em cascata endereço, reservas, pedidos, salas criadas, cartões, documentos e mensagens |
| Oposição a notificações | Preferências de notificação no perfil |

A exportação é montada por `App\Services\Privacidade\ExportadorDeDadosPessoais`
e deixa de fora segredos de autenticação (hash da senha, segredo e códigos de
recuperação do 2FA, token de sessão). Dos documentos enviados vão apenas os
metadados (tipo, nome do arquivo, data). A rota tem limite de 6 downloads por
minuto. Os testes estão em `tests/Feature/Privacidade/MeusDadosTest.php`.

## Segurança

- Senhas com hash (bcrypt) e autenticação em dois fatores opcional (Fortify).
- Limite de tentativas de login (`FortifyServiceProvider::configureRateLimiting`).
- Documentos de identidade no disco `local`, fora da pasta pública.
- Autorização por *policies* (`app/Policies`): cada usuário só vê as próprias
  reservas, pedidos e notas de compra.

## Limitações conhecidas

- **Exclusão apaga o histórico financeiro do dono.** As reservas e pedidos do
  jogador excluído somem também do painel do dono. Uma evolução seria
  anonimizar (manter o registro com o nome copiado para `cliente_nome` e
  `user_id` nulo) em vez de apagar.
- **CPF e CNPJ em texto plano no banco.** São usados em buscas de unicidade;
  criptografá-los exigiria guardar também um hash para essas buscas.
- **Não há prazo de retenção automático** para contas inativas.
- **Minimização:** CPF, data de nascimento, sexo e endereço completo são
  obrigatórios no cadastro de jogador. Para reservar uma quadra, nome, e-mail,
  telefone e cidade bastariam; tornar os demais opcionais é uma melhoria
  possível.
