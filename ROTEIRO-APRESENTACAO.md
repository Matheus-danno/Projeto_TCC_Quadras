# Roteiro de Apresentação — AlugaQuadra

Roteiro curto para seguir durante a defesa. Cobre: cadastro → busca → reserva → sala → loja.

## Antes de começar (no dia, com antecedência)

1. Instalar dependências (só na primeira vez ou se algo mudou):
   ```
   composer install
   npm install && npm run build
   ```
2. Resetar o banco com dados de demonstração:
   ```
   php artisan migrate:fresh
   php artisan db:seed --class=DemoSeeder
   ```
3. Subir o servidor:
   ```
   php artisan serve
   ```
4. Abrir `http://127.0.0.1:8000` no navegador e deixar a aba pronta.

**Contas de demonstração** (senha para todas: `password`):

| Papel           | E-mail             |
|-----------------|---------------------|
| Jogador         | jogador@demo.com    |
| Dono de quadra  | dono@demo.com       |

## 1. Cadastro

- Na home, clique em **Cadastrar-se** (canto superior direito).
- Preencha nome, data de nascimento, CPF, sexo, endereço, CEP, cidade, estado, e-mail, telefone e senha (CPF, CEP e telefone se formatam sozinhos enquanto você digita).
- Ao enviar, o sistema cria a conta, loga automaticamente e leva direto para **Todas as Quadras**.
- *Alternativa mais rápida:* se preferir não preencher tudo ao vivo, entre direto com `jogador@demo.com` / `password` em **Entrar** e pule para o passo 2.

## 2. Busca de quadras

- Clique em **Todas as Quadras** no menu.
- Mostre os filtros (cidade, bairro, esporte) — filtre por um esporte específico (ex: Futebol) e mostre a lista atualizando.
- Aponte que os dados vêm do banco (preços e endereços variados, não são fixos).

## 3. Reserva de quadra

- Em uma quadra da lista, clique em **Agendar**.
- Escolha uma data e um horário disponível e clique em **Confirmar Reserva**.
- Mostre a mensagem de sucesso.
- *Ponto forte para mencionar:* tente reservar o **mesmo horário de novo** na mesma quadra — o sistema bloqueia o conflito com uma mensagem de erro, sem duplicar a reserva.

## 4. Sala / Time

- Clique em **Encontre um Time**.
- Mostre a lista de salas abertas (nome, esporte, vagas ocupadas/total).
- Clique em **Entrar** em uma sala com vagas livres — mostre o contador de vagas subindo.
- Depois, clique em **Criar uma Sala**, preencha nome, esporte, quadra (opcional) e número de vagas, e confirme.
- Volte para **Encontre um Time** e mostre a sala recém-criada na lista.

## 5. Loja

- Clique em **Loja**.
- Mostre a vitrine de produtos (nome, descrição, preço vindos do banco) e filtre por categoria ou busque um produto pelo nome.
- Clique num produto pra abrir a página de detalhe, ajuste a quantidade e clique em **Adicionar ao carrinho**.
- Clique em **Carrinho** no menu (repare no contador de itens badge) — ajuste a quantidade de um item e mostre o total recalculando.
- Clique em **Finalizar Pedido** e mostre a página de confirmação com os itens e o total.
- *Ponto forte para mencionar:* o pagamento é simulado — o pedido é confirmado na hora, sem cobrança real, do mesmo jeito que a reserva de quadra do passo 3. Também vale mostrar que um produto sem estoque aparece como **Esgotado** e não pode ser comprado.

## 6. Fechando: Meu Perfil

- Clique em **Meu perfil**.
- Mostre a aba **Minhas Reservas** com a reserva feita no passo 3 aparecendo em "Próximas".
- Mostre a aba **Meus Pedidos** com o pedido feito no passo 5 (itens comprados e total).
- Se quiser mostrar segurança da conta, a aba **Segurança** linka para troca de senha e ativação de 2FA (essas telas usam o layout padrão do Laravel/Flux, diferente do resto do site — é esperado).

## Se algo der errado ao vivo

- **Página em branco ou erro 500:** o servidor provavelmente não está rodando — confira o terminal onde rodou `php artisan serve`.
- **Login não funciona:** confirme que rodou o `db:seed --class=DemoSeeder` depois do `migrate:fresh` (ele recria as contas de demo).
- **Quer resetar tudo de novo no meio da apresentação:** repita os comandos do passo "Antes de começar" (migrate:fresh + db:seed) — leva poucos segundos.
