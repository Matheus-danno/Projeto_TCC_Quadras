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

    /**
     * Vindo da Agenda com um horário pré-selecionado (link "Realizar
     * agendamento"): completa a hora de término automaticamente.
     */
    public function mount(): void
    {
        if ($this->horaInicio !== '' && $this->horaFim === '') {
            $this->horaFim = Carbon::createFromFormat('H:i', $this->horaInicio)->addHour()->format('H:i');
        }
    }

    public string $tipoCliente = 'existente';

    public string $buscaCliente = '';

    public ?int $clienteId = null;

    public string $clienteNome = '';

    public string $clienteTelefone = '';

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

    public function updatedTipoCliente(): void
    {
        $this->reset(['buscaCliente', 'clienteId', 'clienteNome', 'clienteTelefone']);
        $this->resetErrorBag(['clienteId', 'clienteNome', 'clienteTelefone']);
    }

    public function updatedBuscaCliente(): void
    {
        $this->clienteId = null;
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
            'data' => $validated['data'],
            'hora_inicio' => $horaInicioSql,
            'hora_fim' => $horaFimSql,
            'status' => ReservaStatus::Confirmada,
        ]);

        $this->toast('Reserva agendada com sucesso.', variant: 'success');

        $this->reset([
            'quadraId', 'data', 'horaInicio', 'horaFim',
            'tipoCliente', 'buscaCliente', 'clienteId', 'clienteNome', 'clienteTelefone',
        ]);
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.painel.agendamento-manual.criar');
    }
}
