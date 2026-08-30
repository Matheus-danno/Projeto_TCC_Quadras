@extends('layouts.admin')

@section('admin-title', 'Cadastrar Quadra')
@section('hide-admin-nav', '1')

@section('content')
    <div class="court-create-page">
        <header class="court-create-heading">
            <h1>Cadastrar Nova Quadra</h1>
            <p>Preencha as informações abaixo para cadastrar uma nova quadra.</p>
        </header>

        <section class="court-create-card court-create-photos" aria-labelledby="court-create-photos-title">
            <div class="court-create-card__title">
                <h2 id="court-create-photos-title">Fotos da Quadra</h2>
                <p>Adicione até 5 fotos da quadra.</p>
            </div>

            <div class="court-create-photo-grid">
                <button type="button" class="court-photo-upload">
                    <span class="court-photo-upload__plus">+</span>
                    <span>Adicionar foto</span>
                </button>

                @for ($i = 0; $i < 5; $i++)
                    <div class="court-photo-placeholder" aria-hidden="true"></div>
                @endfor
            </div>
        </section>

        <section class="court-create-card" aria-labelledby="court-create-info-title">
            <div class="court-create-card__title">
                <h2 id="court-create-info-title">Informações da Quadra</h2>
            </div>

            <form class="court-create-form">
                <label class="court-field court-field--full">
                    <span>Nome da Quadra</span>
                    <input type="text" placeholder="Ex: Arena Sports">
                </label>

                <label class="court-field">
                    <span>Tipo de Esporte</span>
                    <select>
                        <option>Selecione</option>
                        <option>Futebol</option>
                        <option>Futsal</option>
                        <option>Vôlei</option>
                        <option>Basquete</option>
                        <option>Tênis</option>
                        <option>Beach Tennis</option>
                    </select>
                </label>

                <label class="court-field">
                    <span>Cobertura</span>
                    <select>
                        <option>Selecione</option>
                        <option>Coberta</option>
                        <option>Descoberta</option>
                    </select>
                </label>

                <label class="court-field">
                    <span>Valor por Hora (R$)</span>
                    <input type="text" inputmode="decimal" placeholder="0,00">
                </label>

                <label class="court-field">
                    <span>Capacidade Máxima</span>
                    <input type="number" min="1" placeholder="Ex: 12">
                </label>

                <label class="court-field">
                    <span>Endereço da Quadra</span>
                    <input type="text" placeholder="Rua, avenida...">
                </label>

                <label class="court-field">
                    <span>Número</span>
                    <input type="text" placeholder="123">
                </label>

                <label class="court-field">
                    <span>Bairro</span>
                    <input type="text" placeholder="Bairro">
                </label>

                <label class="court-field">
                    <span>CEP</span>
                    <input type="text" inputmode="numeric" placeholder="00000-000">
                </label>

                <label class="court-field">
                    <span>Cidade</span>
                    <input type="text" placeholder="Cidade">
                </label>

                <label class="court-field">
                    <span>UF</span>
                    <select>
                        <option>Selecione</option>
                        <option>SP</option>
                        <option>RJ</option>
                        <option>MG</option>
                        <option>PR</option>
                    </select>
                </label>

                <label class="court-field court-field--full">
                    <span>Descrição</span>
                    <textarea rows="3" placeholder="Descreva os diferenciais e características da quadra..."></textarea>
                </label>
            </form>
        </section>

        <div class="court-create-actions">
            <a href="{{ route('dashboard') }}" class="court-create-button court-create-button--secondary">Cancelar</a>
            <button type="button" class="court-create-button court-create-button--primary">Cadastrar Quadra</button>
        </div>
    </div>
@endsection
