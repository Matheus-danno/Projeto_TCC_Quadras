@extends('layouts.bootstrap')

@section('titulo', 'Política de Privacidade')

@section('conteudo')
    <div class="container py-5" style="max-width: 860px;">
        <h1 class="fw-bold mb-1" style="color: #FF7D14;">Política de Privacidade</h1>
        <p class="text-muted mb-5">Última atualização: {{ now()->translatedFormat('d \d\e F \d\e Y') }}</p>

        <p>
            Esta Política de Privacidade descreve como o <strong>AlugaQuadra</strong> coleta, usa, armazena e
            protege as informações dos usuários da plataforma — tanto jogadores que buscam e reservam quadras
            quanto donos de estabelecimentos que anunciam suas quadras. Ao criar uma conta ou utilizar o
            AlugaQuadra, você concorda com as práticas descritas abaixo.
        </p>

        <h2 class="fw-bold h4 mt-5 mb-3">1. Quais dados coletamos</h2>
        <p>Coletamos as informações necessárias para o funcionamento da plataforma, incluindo:</p>
        <ul>
            <li><strong>Dados de cadastro:</strong> nome, e-mail, telefone, data de nascimento e senha.</li>
            <li><strong>Dados de identificação:</strong> CPF (jogadores) ou CNPJ (donos de quadra), quando aplicável.</li>
            <li><strong>Dados de localização:</strong> endereço, cidade, estado e CEP, usados para exibir quadras próximas e cadastrar estabelecimentos.</li>
            <li><strong>Dados de uso:</strong> reservas realizadas, salas criadas, mensagens trocadas com donos de quadra e avaliações feitas.</li>
            <li><strong>Dados de pagamento:</strong> forma de pagamento escolhida (Pix, cartão ou crédito na plataforma) e histórico de transações. Não armazenamos o número completo do cartão de crédito.</li>
        </ul>

        <h2 class="fw-bold h4 mt-5 mb-3">2. Como usamos os dados</h2>
        <p>Utilizamos os dados coletados para:</p>
        <ul>
            <li>Viabilizar a busca, reserva e pagamento de quadras esportivas;</li>
            <li>Conectar jogadores aos donos das quadras alugadas, por meio das mensagens da plataforma;</li>
            <li>Enviar confirmações, lembretes e notificações sobre reservas, salas e cancelamentos;</li>
            <li>Melhorar a experiência de uso e a segurança da plataforma;</li>
            <li>Cumprir obrigações legais e regulatórias.</li>
        </ul>

        <h2 class="fw-bold h4 mt-5 mb-3">3. Com quem compartilhamos os dados</h2>
        <p>
            Não vendemos nem alugamos dados pessoais a terceiros. Compartilhamos apenas as informações
            estritamente necessárias para a operação do serviço:
        </p>
        <ul>
            <li>Com o <strong>dono da quadra</strong>, os dados da reserva (nome, telefone e horário) para que ele possa atendê-lo;</li>
            <li>Com o <strong>jogador</strong>, os dados de contato do estabelecimento vinculado à reserva feita;</li>
            <li>Com autoridades públicas, quando exigido por lei ou ordem judicial.</li>
        </ul>

        <h2 class="fw-bold h4 mt-5 mb-3">4. Armazenamento e segurança</h2>
        <p>
            Os dados são armazenados em ambiente controlado e protegidos por práticas de segurança adequadas,
            como senhas criptografadas e autenticação em duas etapas opcional. Ainda assim, nenhum sistema é
            completamente livre de riscos, e trabalhamos continuamente para reduzir vulnerabilidades.
        </p>

        <h2 class="fw-bold h4 mt-5 mb-3">5. Seus direitos</h2>
        <p>
            Em conformidade com a Lei Geral de Proteção de Dados (LGPD — Lei nº 13.709/2018), você tem direito a:
        </p>
        <ul>
            <li>Confirmar a existência de tratamento dos seus dados;</li>
            <li>Acessar, corrigir ou atualizar seus dados a qualquer momento, pela página "Editar Perfil";</li>
            <li>Solicitar a exclusão da sua conta e dos dados associados, pela página de Configurações;</li>
            <li>Solicitar a portabilidade dos seus dados a outro fornecedor de serviço;</li>
            <li>Revogar o consentimento dado, quando aplicável.</li>
        </ul>

        <h2 class="fw-bold h4 mt-5 mb-3">6. Cookies</h2>
        <p>
            Utilizamos cookies e tecnologias semelhantes para manter sua sessão ativa, lembrar preferências
            (como o tema claro/escuro) e entender como a plataforma é utilizada, sempre com o objetivo de
            melhorar sua experiência.
        </p>

        <h2 class="fw-bold h4 mt-5 mb-3">7. Alterações nesta política</h2>
        <p>
            Esta política pode ser atualizada periodicamente para refletir melhorias na plataforma ou mudanças
            na legislação. Recomendamos revisar esta página de tempos em tempos.
        </p>

        <h2 class="fw-bold h4 mt-5 mb-3">8. Contato</h2>
        <p>
            Em caso de dúvidas sobre esta Política de Privacidade ou sobre o tratamento dos seus dados, entre em
            contato pela página de Ajuda, disponível no seu perfil.
        </p>
    </div>
@endsection
