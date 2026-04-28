@extends('layouts.app')

@section('titulo', 'Crie sua conta')

@section('conteudo')
<div class="container d-flex justify-content-center my-5">
    <div class="card shadow-lg p-4" style="border-radius: 40px; width: 100%; max-width: 700px; border: 4px solid #FF7F27;">
        
        <h2 class="text-center mb-4" style="color: #FF7F27; font-weight: bold; text-decoration: underline; text-underline-offset: 10px;">Crie sua conta</h2>

        <form action="#" method="POST">
            @csrf
            
            <div class="mb-3">
                <label class="form-label text-secondary fw-bold">Nome completo</label>
                <input type="text" name="nome" class="form-control rounded-pill border-secondary-subtle" placeholder="Digite seu nome">
            </div>

            <div class="row mb-3">
                <label class="text-secondary fw-bold mb-2">Data de Nascimento</label>
                <div class="col-4">
                    <select name="dia" class="form-select rounded-pill border-secondary-subtle">
                        <option value="">Dia</option>
                        @for ($i = 1; $i <= 31; $i++)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-4">
                    <select name="mes" class="form-select rounded-pill border-secondary-subtle">
                        <option value="">Mês</option>
                        @for ($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-4">
                    <select name="ano" class="form-select rounded-pill border-secondary-subtle">
                        <option value="">Ano</option>
                        @for ($i = date('Y'); $i >= 1900; $i--)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                </div>
            </div>

            <div class="row mb-3 align-items-end">
                <div class="col-md-5">
                    <label class="text-secondary fw-bold">CPF</label>
                    <input type="text" name="cpf" class="form-control rounded-pill border-secondary-subtle" placeholder="000.000.000-00">
                </div>
                <div class="col-md-7">
                    <label class="text-secondary fw-bold">Sexo</label>
                    <div class="d-flex gap-2">
                        <div class="border rounded-pill px-3 py-1 flex-grow-1 d-flex align-items-center justify-content-between bg-white border-secondary-subtle">
                            <label class="form-check-label text-secondary mb-0" for="masc">Masculino</label>
                            <input class="form-check-input" type="radio" name="sexo" id="masc" value="masculino">
                        </div>
                        <div class="border rounded-pill px-3 py-1 flex-grow-1 d-flex align-items-center justify-content-between bg-white border-secondary-subtle">
                            <label class="form-check-label text-secondary mb-0" for="fem">Feminino</label>
                            <input class="form-check-input" type="radio" name="sexo" id="fem" value="feminino">
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-9">
                    <label class="text-secondary fw-bold">Rua</label>
                    <input type="text" name="rua" class="form-control rounded-pill border-secondary-subtle" placeholder="Nome da rua, bairro">
                </div>
                <div class="col-md-3">
                    <label class="text-secondary fw-bold">Nº</label>
                    <input type="text" name="numero" class="form-control rounded-pill border-secondary-subtle" placeholder="00">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="text-secondary fw-bold">CEP</label>
                    <input type="text" name="cep" class="form-control rounded-pill border-secondary-subtle" placeholder="00000-000">
                </div>
                <div class="col-md-5">
                    <label class="text-secondary fw-bold">Cidade</label>
                    <input type="text" name="cidade" class="form-control rounded-pill border-secondary-subtle" placeholder="Sua cidade">
                </div>
                <div class="col-md-3">
                    <label class="text-secondary fw-bold">Estado</label>
                    <select name="estado" class="form-select rounded-pill border-secondary-subtle">
                        <option value="">UF</option>
                        <option value="AC">AC</option>
                        <option value="AL">AL</option>
                        <option value="AP">AP</option>
                        <option value="AM">AM</option>
                        <option value="BA">BA</option>
                        <option value="CE">CE</option>
                        <option value="DF">DF</option>
                        <option value="ES">ES</option>
                        <option value="GO">GO</option>
                        <option value="MA">MA</option>
                        <option value="MT">MT</option>
                        <option value="MS">MS</option>
                        <option value="MG">MG</option>
                        <option value="PA">PA</option>
                        <option value="PB">PB</option>
                        <option value="PR">PR</option>
                        <option value="PE">PE</option>
                        <option value="PI">PI</option>
                        <option value="RJ">RJ</option>
                        <option value="RN">RN</option>
                        <option value="RS">RS</option>
                        <option value="RO">RO</option>
                        <option value="RR">RR</option>
                        <option value="SC">SC</option>
                        <option value="SP">SP</option>
                        <option value="SE">SE</option>
                        <option value="TO">TO</option>
                        </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-7">
                    <label class="text-secondary fw-bold">E-mail</label>
                    <input type="email" name="email" class="form-control rounded-pill border-secondary-subtle" placeholder="seu@email.com">
                </div>
                <div class="col-md-5">
                    <label class="text-secondary fw-bold">Telefone</label>
                    <input type="text" name="telefone" class="form-control rounded-pill border-secondary-subtle" placeholder="(00) 00000-0000">
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="text-secondary fw-bold">Senha</label>
                    <input type="password" name="senha" class="form-control rounded-pill border-secondary-subtle" placeholder="********">
                </div>
                <div class="col-md-6">
                    <label class="text-secondary fw-bold">Confirmar Senha</label>
                    <input type="password" name="confirmar_senha" class="form-control rounded-pill border-secondary-subtle" placeholder="********">
                </div>
            </div>

            <div class="d-flex gap-3">
                <button type="submit" class="btn text-white w-50 py-2 shadow-sm" style="background-color: #FF7F27; border-radius: 25px; font-weight: bold;">
                    Criar conta
                </button>

                <a href="{{ route('home') }}" class="btn w-50 py-2" style="border-radius: 25px; color: #FF7F27; border: 2px solid #FF7F27; font-weight: bold;">
                    Cancelar
                </a>
            </div>

        </form>
    </div>
</div>
@endsection