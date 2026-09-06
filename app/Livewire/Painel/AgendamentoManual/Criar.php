<?php

namespace App\Livewire\Painel\AgendamentoManual;

use App\Enums\ReservaStatus;
use App\Enums\UserRole;
use App\Models\Quadra;
use App\Models\Reserva;
use App\Models\User;
use Carbon\Carbon;
use Flux\Concerns\InteractsWithComponents;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

class Criar extends Component
{
    use InteractsWithComponents;

    #[Url]
    public string $quadraId = '';

    #[Url]
    public string $data = '';

    #[Url]
    public string $horaInicio = '';

    public string $horaFim = '';

    public string $tipoCliente = 'existente';

    public string $buscaCliente = '';

    public ?int $clienteId = null;

    public string $clienteNome = '';

    public string $clienteTelefone = '';

    public string $clienteEmail = '';

    public string $observacoes = '';

    public string $valor = '';

    public string $statusPagamento = 'pendente';

    public string $formaPagamento = '';

    /**
     * Evita sobrescrever um valor que o dono já ajustou manualmente quando
     * a quadra ou o horário mudam depois.
     */
    private bool $valorEditadoManualmente = false;

    /**
     * Vindo da Agenda com um horário pré-selecionado (link "Realizar
     * agendamento"): completa a hora de término automaticamente.
     */
    public function mount(): void
    {
        if ($this->horaInicio !== '' && $this->horaFim === '') {
            $this->horaFim = Carbon::createFromFormat('H:i', $this->horaInicio)->addHour()->format('H:i');
        }

        $this->sugerirValor();
    }

    protected function rules(): array
    {
        return [
            'quadraId' => ['required', 'integer'],
            'data' => ['required', 'date', 'after_or_equal:today'],
            'horaInicio' => ['required', 'date_format:H:i'],
            'horaFim' => ['required', 'date_format:H:i', 'after:horaInicio'],
            'tipoCliente' => ['required', 'in:existente,sem_conta'],
            'clienteId' => ['nullable', 'required_if:tipoCliente,existente', 'integer', 'exists:users,id'],
            'clienteNome' => ['nullable', 'required_if:tipoCliente,sem_conta', 'string', 'max:255'],
            'clienteTelefone' => ['nullable', 'required_if:tipoCliente,sem_conta', 'string', 'max:20'],
            'clienteEmail' => ['nullable', 'email', 'max:255'],
            'observacoes' => ['nullable', 'string'],
            'valor' => ['required', 'numeric', 'min:0'],
            'statusPagamento' => ['required', 'in:pago,pendente,isento'],
            'formaPagamento' => ['required_unless:statusPagamento,isento', 'in:pix,cartao,dinheiro'],
        ];
    }

    #[Computed]
    public function quadras(): EloquentCollection
    {
        return Quadra::query()
            ->where('dono_id', auth()->id())
            ->where('ativa', true)
            ->orderBy('nome')
            ->get();
    }

    #[Computed]
    public function clientesEncontrados(): Collection
    {
        if (mb_strlen(trim($this->buscaCliente)) < 2) {
            return collect();
        }

        return User::query()
            ->where('role', UserRole::Jogador)
            ->where(function ($query) {
                $query->where('name', 'like', '%'.$this->buscaCliente.'%')
                    ->orWhere('email', 'like', '%'.$this->buscaCliente.'%');
            })
            ->orderBy('name')
            ->limit(5)
            ->get();
    }

    /**
     * Duração da reserva formatada (ex.: "1h", "1h30min"), a partir dos
     * campos ainda não salvos do formulário.
     */
    #[Computed]
    public function duracaoFormatada(): ?string
    {
        if ($this->horaInicio === '' || $this->horaFim === '') {
            return null;
        }

        $minutos = (strtotime($this->horaFim) - strtotime($this->horaInicio)) / 60;

        if ($minutos <= 0) {
            return null;
        }

        $horas = intdiv((int) $minutos, 60);
        $resto = (int) $minutos % 60;

        return match (true) {
            $horas > 0 && $resto > 0 => "{$horas}h{$resto}min",
            $horas > 0 => "{$horas}h",
            default => "{$resto}min",
        };
    }

    public function updatedTipoCliente(): void
    {
        $this->reset(['buscaCliente', 'clienteId', 'clienteNome', 'clienteTelefone', 'clienteEmail']);
        $this->resetErrorBag(['clienteId', 'clienteNome', 'clienteTelefone', 'clienteEmail']);
    }

    public function updatedBuscaCliente(): void
    {
        $this->clienteId = null;
    }

    public function updatedQuadraId(): void
    {
        $this->sugerirValor();
    }

    public function updatedHoraInicio(): void
    {
        $this->sugerirValor();
    }

    public function updatedHoraFim(): void
    {
        $this->sugerirValor();
    }

    public function updatedValor(): void
    {
        $this->valorEditadoManualmente = true;
    }

    /**
     * Sugere o valor da reserva (valor/hora da quadra × duração), sem
     * sobrescrever um valor já ajustado manualmente pelo dono.
     */
    private function sugerirValor(): void
    {
        if ($this->valorEditadoManualmente) {
            return;
        }

        $quadra = $this->quadraId !== '' ? Quadra::find($this->quadraId) : null;

        if (! $quadra || $this->horaInicio === '' || $this->horaFim === '') {
            return;
        }

        $minutos = (strtotime($this->horaFim) - strtotime($this->horaInicio)) / 60;

        if ($minutos <= 0) {
            return;
        }

        $this->valor = number_format(((float) $quadra->valor_hora) * ($minutos / 60), 2, '.', '');
    }

    public function selecionarCliente(int $userId): void
    {
        $cliente = User::findOrFail($userId);

        $this->clienteId = $cliente->id;
        $this->buscaCliente = $cliente->name;
    }

    public function salvar(): void
    {
        $validated = $this->validate();

        $quadra = Quadra::findOrFail($validated['quadraId']);

        $this->authorize('update', $quadra);

        $horaInicioSql = $validated['horaInicio'].':00';
        $horaFimSql = $validated['horaFim'].':00';

        $existeConflito = Reserva::query()
            ->where('quadra_id', $quadra->id)
            ->whereDate('data', $validated['data'])
            ->where('status', '!=', ReservaStatus::Cancelada)
            ->where('hora_inicio', '<', $horaFimSql)
            ->where('hora_fim', '>', $horaInicioSql)
            ->exists();

        if ($existeConflito) {
            $this->addError('horaInicio', 'Já existe uma reserva para esta quadra nesse horário.');

            return;
        }

        Reserva::create([
            'quadra_id' => $quadra->id,
            'user_id' => $this->tipoCliente === 'existente' ? $validated['clienteId'] : null,
            'cliente_nome' => $this->tipoCliente === 'sem_conta' ? $validated['clienteNome'] : null,
            'cliente_telefone' => $this->tipoCliente === 'sem_conta' ? $validated['clienteTelefone'] : null,
            'cliente_email' => $this->tipoCliente === 'sem_conta' ? ($validated['clienteEmail'] ?: null) : null,
            'data' => $validated['data'],
            'hora_inicio' => $horaInicioSql,
            'hora_fim' => $horaFimSql,
            'status' => ReservaStatus::Confirmada,
            'status_pagamento' => $validated['statusPagamento'],
            'metodo_pagamento' => $validated['statusPagamento'] === 'isento' ? null : $validated['formaPagamento'],
            'valor' => $validated['valor'],
            'observacoes' => $validated['observacoes'] ?: null,
        ]);

        $this->toast('Reserva agendada com sucesso.', variant: 'success');

        $this->reset([
            'quadraId', 'data', 'horaInicio', 'horaFim',
            'tipoCliente', 'buscaCliente', 'clienteId', 'clienteNome', 'clienteTelefone', 'clienteEmail',
            'observacoes', 'valor', 'statusPagamento', 'formaPagamento',
        ]);
        $this->valorEditadoManualmente = false;
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.painel.agendamento-manual.criar');
    }
}
