@extends('layouts.bootstrap')

@section('titulo', 'Política de Privacidade')

@section('conteudo')
    <div class="container py-5" style="max-width: 860px;">
        <h1 class="fw-bold mb-1" style="color: #FF7D14;">Política de Privacidade</h1>
        <p class="text-muted mb-5">Versão de {{ \Carbon\Carbon::parse(config('privacidade.versao'))->translatedFormat('d \d\e F \d\e Y') }}</p>

        <p>
            Esta Política de Privacidade descreve como o <strong>AlugaQuadra</strong> coleta, usa, armazena e
            protege as informações dos usuários da plataforma — tanto jogadores que buscam e reservam quadras
            quanto donos de estabelecimentos que anunciam suas quadras. Ela segue a Lei Geral de Proteção de
            Dados (LGPD — Lei nº 13.709/2018). Para criar uma conta, é preciso ler e aceitar esta política e os
            <a href="{{ route('termos.uso') }}" class="text-orange fw-bold">Termos de Uso</a>; registramos a data
            e a versão do texto aceito.
        </p>

        <h2 class="fw-bold h4 mt-5 mb-3">1. Quais dados coletamos</h2>
        <ul>
            <li><strong>Cadastro (todos):</strong> nome, e-mail, telefone e senha (guardada apenas como hash, nunca em texto).</li>
            <li><strong>Jogadores:</strong> CPF, data de nascimento, sexo e endereço (logradouro, número, complemento, bairro, CEP, cidade e estado).</li>
            <li><strong>Donos de quadra:</strong> CPF ou CNPJ, nome do estabelecimento, endereço, foto ou PDF de um documento de identidade (para a análise do cadastro) e dados de recebimento (chave Pix e nome e CPF/CNPJ do titular).</li>
            <li><strong>Uso da plataforma:</strong> reservas, salas criadas e de que participa, pedidos da loja, mensagens trocadas no chat, avaliações e preferências de notificação.</li>
            <li><strong>Pagamento:</strong> forma de pagamento escolhida e histórico de transações. De cartões salvos guardamos só o nome do titular, a bandeira, os 4 últimos dígitos e a validade — nunca o número completo nem o código de segurança.</li>
            <li><strong>Localização aproximada:</strong> apenas quando você usa a busca de "quadras próximas" e autoriza o navegador a informar sua posição. Ela é usada na hora e não fica gravada na sua conta.</li>
        </ul>

        <h2 class="fw-bold h4 mt-5 mb-3">2. Para que usamos e com qual base legal</h2>
        <ul>
            <li><strong>Criar e manter sua conta, reservar quadras, entrar em salas e comprar na loja</strong> — execução do contrato com você (LGPD, art. 7º, V).</li>
            <li><strong>Identificar e aprovar donos de quadra</strong> (documento, CPF/CNPJ e dados de recebimento), para evitar anúncios falsos e repassar valores à pessoa certa — execução de contrato e legítimo interesse na segurança da plataforma (art. 7º, V e IX).</li>
            <li><strong>Enviar notificações</strong> sobre reservas, salas e pedidos — execução do contrato; você escolhe quais receber nas preferências de notificação.</li>
            <li><strong>Registro do seu aceite</strong> a esta política e aos Termos de Uso — consentimento (art. 7º, I), que pode ser revogado excluindo a conta.</li>
            <li><strong>Cumprir obrigações legais</strong>, como guardar registros de transações — art. 7º, II.</li>
        </ul>

        <h2 class="fw-bold h4 mt-5 mb-3">3. Com quem compartilhamos</h2>
        <p>Não vendemos nem alugamos dados pessoais. Compartilhamos apenas o necessário para o serviço funcionar:</p>
        <ul>
            <li>Com o <strong>dono da quadra</strong>: nome, telefone e horário da sua reserva, e seus pedidos na loja dele;</li>
            <li>Com o <strong>jogador</strong>: os dados de contato e o endereço do estabelecimento da reserva ou do pedido;</li>
            <li>Com os <strong>participantes de uma sala</strong>: seu nome, foto e nível de habilidade;</li>
            <li>Com <strong>serviços de mapa do OpenStreetMap</strong>: na busca de quadras próximas, enviamos à Overpass API apenas a latitude e a longitude aproximadas, sem nome, e-mail ou qualquer identificação; ao cadastrar uma quadra, enviamos ao Nominatim só o endereço da quadra para obter as coordenadas;</li>
            <li>Com autoridades públicas, quando exigido por lei ou ordem judicial.</li>
        </ul>

        <h2 class="fw-bold h4 mt-5 mb-3">4. Por quanto tempo guardamos</h2>
        <p>
            Os dados ficam guardados enquanto sua conta existir. Ao excluir a conta, apagamos seu cadastro e os
            dados ligados a ela (endereço, reservas, pedidos, salas criadas, cartões salvos, documentos e
            mensagens). Os documentos enviados por donos de quadra ficam em área privada do servidor, acessível
            apenas à equipe de aprovação.
        </p>

        <h2 class="fw-bold h4 mt-5 mb-3">5. Segurança</h2>
        <p>
            Senhas são guardadas com hash, o login tem limite de tentativas e você pode ativar a autenticação em
            duas etapas. Ainda assim, nenhum sistema é totalmente livre de riscos, e trabalhamos continuamente
            para reduzir vulnerabilidades.
        </p>

        <h2 class="fw-bold h4 mt-5 mb-3">6. Seus direitos</h2>
        <p>Pela LGPD (art. 18), você pode, a qualquer momento:</p>
        <ul>
            <li><strong>Acessar e levar seus dados</strong> (acesso e portabilidade): use o botão <em>Baixar meus dados</em> em <a href="{{ route('perfil.editar') }}" class="text-orange fw-bold">Editar Perfil</a> para receber uma cópia em JSON;</li>
            <li><strong>Corrigir</strong> dados incompletos ou desatualizados, na mesma página;</li>
            <li><strong>Excluir</strong> sua conta e os dados ligados a ela, nas configurações da conta — o que também revoga seu consentimento;</li>
            <li><strong>Escolher</strong> quais notificações deseja receber;</li>
            <li><strong>Pedir informações</strong> sobre o uso dos seus dados, pela página de Ajuda do seu perfil.</li>
        </ul>

        <h2 class="fw-bold h4 mt-5 mb-3">7. Crianças e adolescentes</h2>
        <p>
            O cadastro de jogador exige idade mínima de 16 anos. Não coletamos intencionalmente dados de
            pessoas abaixo dessa idade.
        </p>

        <h2 class="fw-bold h4 mt-5 mb-3">8. Cookies</h2>
        <p>
            Usamos apenas cookies necessários ao funcionamento: manter sua sessão ativa, proteger formulários e
            lembrar preferências (como o tema claro/escuro). Não usamos cookies de publicidade.
        </p>

        <h2 class="fw-bold h4 mt-5 mb-3">9. Alterações nesta política</h2>
        <p>
            Se esta política mudar, a versão acima é atualizada. Mudanças relevantes serão informadas na
            plataforma.
        </p>

        <h2 class="fw-bold h4 mt-5 mb-3">10. Contato</h2>
        <p>
            Dúvidas sobre esta política ou sobre o tratamento dos seus dados podem ser enviadas pela página de
            Ajuda, disponível no seu perfil.
        </p>
    </div>
@endsection
