<div class="flex w-full flex-col gap-6">
    <div>
        <flux:heading size="xl" class="text-3xl! sm:text-4xl!">{{ __('Status do cadastro') }}</flux:heading>
        <flux:text class="mt-1 text-base text-zinc-400">{{ __('Acompanhe a análise do seu cadastro e das suas quadras.') }}</flux:text>
    </div>

    <flux:card class="flex flex-col gap-4 rounded-2xl">
        <div class="flex items-center justify-between gap-2">
            <flux:heading size="lg">{{ __('Seu cadastro de proprietário') }}</flux:heading>
            <flux:badge size="lg" color="{{ $this->usuario->status_cadastro->cor() }}">{{ $this->usuario->status_cadastro->label() }}</flux:badge>
        </div>

        @if ($this->usuario->status_cadastro === App\Enums\StatusCadastro::Pendente)
            <flux:text class="text-zinc-500">{{ __('Seu cadastro está na fila, aguardando um administrador analisar.') }}</flux:text>
        @elseif ($this->usuario->status_cadastro === App\Enums\StatusCadastro::EmAnalise)
            <flux:text class="text-zinc-500">{{ __('Um administrador está analisando o seu cadastro agora.') }}</flux:text>
        @elseif ($this->usuario->status_cadastro === App\Enums\StatusCadastro::Aprovado)
            <flux:text class="text-green-600">{{ __('Seu cadastro está aprovado. Você já pode cadastrar e operar quadras.') }}</flux:text>
        @endif

        @if ($this->motivoAtual && in_array($this->usuario->status_cadastro, [App\Enums\StatusCadastro::AjustesSolicitados, App\Enums\StatusCadastro::Rejeitado], true))
            <flux:callout variant="{{ $this->usuario->status_cadastro === App\Enums\StatusCadastro::Rejeitado ? 'danger' : 'warning' }}" icon="exclamation-triangle">
                <flux:callout.heading>{{ $this->usuario->status_cadastro === App\Enums\StatusCadastro::Rejeitado ? __('Cadastro rejeitado') : __('Ajustes solicitados') }}</flux:callout.heading>
                <flux:callout.text>{{ $this->motivoAtual->motivo }}</flux:callout.text>
            </flux:callout>

            <form wire:submit="reenviar" class="flex flex-col gap-3">
                <flux:text class="text-sm text-zinc-500">
                    {{ __('Atualize seus dados em Configurações se precisar, e envie um novo documento aqui se foi isso que pediram. Depois, clique em reenviar.') }}
                </flux:text>

                <flux:input type="file" wire:model="novoDocumentoIdentidade" :label="__('Novo documento de identidade (opcional)')" accept=".jpg,.jpeg,.png,.pdf" class="rounded-xl" />
                @error('novoDocumentoIdentidade') <flux:text class="text-red-600">{{ $message }}</flux:text> @enderror

                <flux:button type="submit" variant="primary" color="orange" class="w-fit rounded-full">{{ __('Reenviar cadastro') }}</flux:button>
            </form>
        @endif
    </flux:card>

    <flux:card class="flex flex-col gap-4 rounded-2xl p-0">
        <div class="flex items-center justify-between gap-2 p-6 pb-0">
            <flux:heading size="lg">{{ __('Suas quadras') }}</flux:heading>
            <flux:button size="sm" variant="outline" class="rounded-full" :href="route('painel.quadras.criar')" wire:navigate>
                {{ __('Cadastrar nova quadra') }}
            </flux:button>
        </div>

        @if ($this->quadras->isEmpty())
            <div class="flex flex-col items-center gap-2 px-6 py-16 text-center">
                <flux:icon.building-office-2 class="size-8 text-zinc-300" />
                <flux:heading size="lg">{{ __('Nenhuma quadra cadastrada ainda') }}</flux:heading>
            </div>
        @else
            <div class="flex flex-col gap-3 p-6 pt-0">
                @foreach ($this->quadras as $quadra)
                    @php $motivoQuadra = $this->motivoDaQuadra($quadra); @endphp
                    <div class="flex flex-col gap-2 rounded-xl bg-zinc-50 p-4" wire:key="status-quadra-{{ $quadra->id }}">
                        <div class="flex items-center justify-between gap-2">
                            <flux:text class="font-medium text-zinc-900">{{ $quadra->nome }}</flux:text>
                            <flux:badge size="sm" color="{{ $quadra->status_cadastro->cor() }}">{{ $quadra->status_cadastro->label() }}</flux:badge>
                        </div>

                        @if ($motivoQuadra && in_array($quadra->status_cadastro, [App\Enums\StatusCadastro::AjustesSolicitados, App\Enums\StatusCadastro::Rejeitado], true))
                            <flux:text class="text-sm text-zinc-600">{{ $motivoQuadra->motivo }}</flux:text>

                            <flux:button size="sm" variant="outline" class="w-fit rounded-full !border-orange-300 !text-orange-600 hover:!bg-orange-50" :href="route('painel.quadras.editar', $quadra)" wire:navigate>
                                {{ __('Editar e reenviar') }}
                            </flux:button>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </flux:card>
</div>
