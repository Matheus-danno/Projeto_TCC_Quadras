@extends('layouts.bootstrap')

@section('titulo', 'Termos de Uso')

@section('conteudo')
    <div class="container py-5" style="max-width: 860px;">
        <h1 class="fw-bold mb-1" style="color: #FF7D14;">Termos de Uso</h1>
        <p class="text-muted mb-5">Versão de {{ \Carbon\Carbon::parse(config('privacidade.versao'))->translatedFormat('d \d\e F \d\e Y') }}</p>

        <p>
            Estes Termos regulam o uso do <strong>AlugaQuadra</strong>, plataforma que conecta jogadores a
            quadras esportivas e a outros jogadores. Ao criar uma conta, você declara que leu e concorda com estes
            Termos e com a <a href="{{ route('politica.privacidade') }}" class="text-orange fw-bold">Política de Privacidade</a>.
        </p>

        <h2 class="fw-bold h4 mt-5 mb-3">1. Contas</h2>
        <ul>
            <li>Jogadores precisam ter pelo menos 16 anos.</li>
            <li>Você é responsável pelos dados informados e por manter sua senha em segredo.</li>
            <li>Cada CPF, CNPJ e e-mail pode ser usado em apenas uma conta.</li>
            <li>Contas de dono de quadra passam por análise antes de as quadras aparecerem para os jogadores.</li>
        </ul>

        <h2 class="fw-bold h4 mt-5 mb-3">2. Reservas e salas</h2>
        <ul>
            <li>A reserva garante o horário na quadra; o mesmo horário não pode ser reservado duas vezes.</li>
            <li>O cancelamento segue o prazo definido pelo dono da quadra; dentro do prazo, o valor vira crédito na plataforma.</li>
            <li>Em salas, o organizador define as regras de entrada, o número de vagas e pode aprovar ou recusar pedidos.</li>
        </ul>

        <h2 class="fw-bold h4 mt-5 mb-3">3. Pagamentos</h2>
        <p>
            Nesta versão, os pagamentos (Pix e cartão) são <strong>simulados</strong>: nenhuma cobrança real é feita
            e nenhum dado completo de cartão é processado. Sobre as reservas e pedidos pagos, a plataforma retém a
            comissão vigente, informada ao dono da quadra no cadastro.
        </p>

        <h2 class="fw-bold h4 mt-5 mb-3">4. Donos de quadra</h2>
        <ul>
            <li>O dono é responsável pelas informações da quadra, pelos horários de funcionamento e pelo atendimento.</li>
            <li>Os produtos vendidos na loja são de responsabilidade do estabelecimento que os anuncia.</li>
            <li>A plataforma pode suspender quadras ou contas que descumpram estes Termos.</li>
        </ul>

        <h2 class="fw-bold h4 mt-5 mb-3">5. Conduta</h2>
        <p>
            Não é permitido usar o chat, as avaliações ou os nomes de sala para ofender, assediar ou divulgar
            dados de terceiros. Conteúdos assim podem ser removidos e a conta, bloqueada.
        </p>

        <h2 class="fw-bold h4 mt-5 mb-3">6. Encerramento</h2>
        <p>
            Você pode excluir sua conta a qualquer momento nas configurações. A exclusão apaga seu cadastro e os
            dados ligados a ele, como descrito na Política de Privacidade.
        </p>
    </div>
@endsection
