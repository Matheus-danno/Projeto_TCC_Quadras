@extends('layouts.admin')

@section('admin-title', 'Dashboard')

@section('content')
    <div class="admin-page">
        <div class="admin-page__heading">
            <div>
                <h1>Meu Painel</h1>
                <p>Acompanhe rapidamente o desempenho das suas quadras.</p>
            </div>

            <a href="{{ route('admin.quadras.create') }}" class="admin-primary-button">
                <i class="bi bi-plus-lg" aria-hidden="true"></i>
                Cadastrar Quadra
            </a>
        </div>

        <section class="admin-kpis" aria-label="Resumo do painel">
            <article class="admin-kpi admin-kpi--orange">
                <span>Quadras Cadastradas</span>
                <strong>4</strong>
            </article>

            <article class="admin-kpi admin-kpi--green">
                <span>Reservas do Mês</span>
                <strong>128</strong>
            </article>

            <article class="admin-kpi admin-kpi--orange">
                <span>Faturamento do Mês</span>
                <strong>R$ 6.240,00</strong>
            </article>

            <article class="admin-kpi admin-kpi--green">
                <span>Avaliação Média</span>
                <strong>4.8 <i class="bi bi-star-fill" aria-hidden="true"></i></strong>
            </article>
        </section>

        <section id="minhas-quadras" class="admin-courts" aria-labelledby="admin-courts-title">
            <div class="admin-section-heading">
                <h2 id="admin-courts-title">Minhas Quadras</h2>
            </div>

            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Quadra</th>
                            <th>Esporte</th>
                            <th>Valor Hora</th>
                            <th>Status</th>
                            <th>Reservas</th>
                            <th class="admin-table__actions-heading">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div class="admin-court-name">
                                    <span class="admin-court-thumb" aria-hidden="true"></span>
                                    <strong>Arena Sports - Quadra 1</strong>
                                </div>
                            </td>
                            <td>Vôlei de Areia</td>
                            <td>R$ 50,00</td>
                            <td><span class="admin-status admin-status--active">Ativa</span></td>
                            <td>52</td>
                            <td>
                                <div class="admin-row-actions">
                                    <button type="button">Editar</button>
                                    <button type="button">Excluir</button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="admin-court-name">
                                    <span class="admin-court-thumb" aria-hidden="true"></span>
                                    <strong>Arena Sports - Quadra 2</strong>
                                </div>
                            </td>
                            <td>Beach Tennis</td>
                            <td>R$ 50,00</td>
                            <td><span class="admin-status admin-status--active">Ativa</span></td>
                            <td>43</td>
                            <td>
                                <div class="admin-row-actions">
                                    <button type="button">Editar</button>
                                    <button type="button">Excluir</button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="admin-court-name">
                                    <span class="admin-court-thumb" aria-hidden="true"></span>
                                    <strong>SuperQuadra Club</strong>
                                </div>
                            </td>
                            <td>Futsal</td>
                            <td>R$ 60,00</td>
                            <td><span class="admin-status admin-status--active">Ativa</span></td>
                            <td>21</td>
                            <td>
                                <div class="admin-row-actions">
                                    <button type="button">Editar</button>
                                    <button type="button">Excluir</button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="admin-court-name">
                                    <span class="admin-court-thumb" aria-hidden="true"></span>
                                    <strong>Quadra do Bairro</strong>
                                </div>
                            </td>
                            <td>Futebol</td>
                            <td>R$ 40,00</td>
                            <td><span class="admin-status admin-status--inactive">Inativa</span></td>
                            <td>12</td>
                            <td>
                                <div class="admin-row-actions">
                                    <button type="button">Editar</button>
                                    <button type="button">Excluir</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
