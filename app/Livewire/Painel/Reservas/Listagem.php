<?php

namespace App\Livewire\Painel\Reservas;

use App\Enums\ReservaStatus;
use App\Models\Conversa;
use App\Models\Quadra;
use App\Models\Reserva;
use Flux\Concerns\InteractsWithComponents;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Listagem extends Component
{
    use InteractsWithComponents;

    public string $quadraId = '';

    public string $data = '';

    public string $status = '';

    public string $aba = 'todas';

    public ?int $reservaSelecionadaId = null;

    public string $motivoCancelamento = '';

    public bool $notificarCliente = true;

    public string $novaMensagem = '';

    #[Computed]
    public function quadras(): Collection
    {
        return Quadra::query()
            ->where('dono_id', auth()->id())
            ->orderBy('nome')
            ->get();
    }

    #[Computed]
    public function reservas(): Collection
    {
        return Reserva::query()
            ->whereHas('quadra', fn ($query) => $query->where('dono_id', auth()->id()))
            ->with(['quadra', 'user'])
            ->when($this->quadraId, fn ($query) => $query->where('quadra_id', $this->quadraId))
            ->when($this->data, fn ($query) => $query->whereDate('data', $this->data))
            ->when($this->status, fn ($query) => $query->where('status', $this->status))
            ->when($this->aba === 'hoje', fn ($query) => $query->whereDate('data', now()->toDateString()))
            ->when($this->aba === 'semana', fn ($query) => $query->whereBetween('data', [
                now()->startOfWeek()->toDateString(),
                now()->endOfWeek()->toDateString(),
            ]))
            ->when($this->aba === 'pendentes', fn ($query) => $query->where('status', ReservaStatus::Pendente))
            // O enum ReservaStatus não tem status "concluída": tratamos como confirmada com data já passada.
            ->when($this->aba === 'concluidas', fn ($query) => $query->where('status', ReservaStatus::Confirmada)
                ->whereDate('data', '<', now()->toDateString()))
            ->orderBy('data')
            ->orderBy('hora_inicio')
            ->get();
    }

    #[Computed]
    public function resumo(): array
    {
        $baseQuery = fn () => Reserva::query()
            ->whereHas('quadra', fn ($query) => $query->where('dono_id', auth()->id()));

        $reservasSemana = $baseQuery()
            ->whereBetween('data', [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()])
            ->with('quadra')
            ->get();

        $faturamentoSemana = $reservasSemana
            ->where('status', ReservaStatus::Confirmada)
            ->sum(function (Reserva $reserva) {
                $horas = (strtotime($reserva->hora_fim) - strtotime($reserva->hora_inicio)) / 3600;

                return $reserva->quadra->valor_hora * $horas;
            });

        return [
            'hoje' => $baseQuery()->whereDate('data', now()->toDateString())->count(),
            'semana' => $reservasSemana->count(),
            'pendentes' => $baseQuery()->where('status', ReservaStatus::Pendente)->count(),
            'faturamentoSemana' => $faturamentoSemana,
        ];
    }

    #[Computed]
    public function reservaSelecionada(): ?Reserva
    {
        if (! $this->reservaSelecionadaId) {
            return null;
        }

        return Reserva::query()->with(['quadra', 'user'])->find($this->reservaSelecionadaId);
    }

    public function verDetalhes(int $reservaId): void
    {
        $reserva = Reserva::findOrFail($reservaId);

        $this->authorize('update', $reserva);

        $this->reservaSelecionadaId = $reservaId;

        $this->modal('detalhes-reserva')->show();
    }

    public function pedirCancelamento(int $reservaId): void
    {
        $reserva = Reserva::findOrFail($reservaId);

        $this->authorize('update', $reserva);

        $this->reservaSelecionadaId = $reservaId;
        $this->motivoCancelamento = '';
        $this->notificarCliente = true;

        $this->modal('cancelar-reserva')->show();
    }

    public function confirmar(int $reservaId): void
    {
        $reserva = Reserva::findOrFail($reservaId);

        $this->authorize('update', $reserva);

        if ($reserva->status !== ReservaStatus::Pendente) {
            return;
        }

        $reserva->update(['status' => ReservaStatus::Confirmada]);

        $this->toast('Reserva confirmada.', variant: 'success');

        unset($this->reservas);
    }

    public function cancelar(): void
    {
        $reserva = Reserva::with(['quadra', 'user'])->findOrFail($this->reservaSelecionadaId);

        $this->authorize('update', $reserva);

        if ($reserva->status !== ReservaStatus::Cancelada) {
            // Reembolso automático em crédito, mesmo mecanismo usado quando o
            // próprio jogador cancela uma reserva confirmada (MinhasReservas::cancelar()).
            if ($reserva->status === ReservaStatus::Confirmada && $reserva->user) {
                $reserva->user->increment('saldo_creditos', (float) ($reserva->quadra?->valor_hora ?? 0));
            }

            $reserva->update([
                'status' => ReservaStatus::Cancelada,
                'cancelamento_tipo' => $reserva->status === ReservaStatus::Confirmada && $reserva->user ? 'credito' : null,
                'motivo_cancelamento' => trim($this->motivoCancelamento) ?: null,
            ]);

            if ($this->notificarCliente && $reserva->user) {
                $conversa = Conversa::firstOrCreate([
                    'quadra_id' => $reserva->quadra_id,
                    'jogador_id' => $reserva->user_id,
                ]);

                $texto = __('Sua reserva de :data às :hora foi cancelada.', [
                    'data' => $reserva->data->format('d/m/Y'),
                    'hora' => substr($reserva->hora_inicio, 0, 5),
                ]);

                if (trim($this->motivoCancelamento)) {
                    $texto .= ' '.__('Motivo: :motivo', ['motivo' => trim($this->motivoCancelamento)]);
                }

                $conversa->mensagens()->create([
                    'user_id' => auth()->id(),
                    'texto' => $texto,
                ]);
            }
        }

        $this->modal('cancelar-reserva')->close();
        $this->toast('Reserva cancelada.', variant: 'success');

        $this->reservaSelecionadaId = null;
        unset($this->reservas);
    }

    public function enviarMensagem(): void
    {
        $reserva = Reserva::findOrFail($this->reservaSelecionadaId);

        $this->authorize('update', $reserva);

        abort_unless($reserva->user_id, 404);

        $validated = $this->validate([
            'novaMensagem' => ['required', 'string', 'max:500'],
        ], [
            'novaMensagem.required' => __('Escreva uma mensagem antes de enviar.'),
            'novaMensagem.max' => __('A mensagem pode ter no máximo 500 caracteres.'),
        ]);

        $conversa = Conversa::firstOrCreate([
            'quadra_id' => $reserva->quadra_id,
            'jogador_id' => $reserva->user_id,
        ]);

        $conversa->mensagens()->create([
            'user_id' => auth()->id(),
            'texto' => trim($validated['novaMensagem']),
        ]);

        $this->novaMensagem = '';
        $this->toast(__('Mensagem enviada.'), variant: 'success');
    }

    public function render()
    {
        return view('livewire.painel.reservas.listagem', [
            'statusDisponiveis' => ReservaStatus::cases(),
        ]);
    }
}
