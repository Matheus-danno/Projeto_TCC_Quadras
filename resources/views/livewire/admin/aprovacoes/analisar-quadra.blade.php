<div class="flex w-full flex-col gap-6">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:button size="sm" variant="ghost" class="rounded-full" :href="route('admin.aprovacoes', ['aba' => 'quadras'])" wire:navigate>
                ← {{ __('Voltar para a fila') }}
            </flux:button>
            <flux:heading size="xl" class="mt-2 text-3xl! sm:text-4xl!">{{ $quadra->nome }}</flux:heading>
            <flux:text class="mt-1 text-base text-zinc-400">{{ __('Cadastro de quadra') }}</flux:text>
        </div>

        <flux:badge size="lg" color="{{ $quadra->status_cadastro->cor() }}">{{ $quadra->status_cadastro->label() }}</flux:badge>
    </div>

    @if (! $quadra->dono?->cadastroAprovado())
        <flux:callout variant="warning" icon="exclamation-triangle">
            <flux:callout.heading>{{ __('Proprietário ainda não aprovado') }}</flux:callout.heading>
            <flux:callout.text>
                {{ __('Esta quadra só pode ser aprovada depois que o cadastro do proprietário for aprovado.') }}
                <a href="{{ route('admin.aprovacoes.proprietario', $quadra->dono) }}" wire:navigate class="font-semibold underline">{{ __('Analisar proprietário') }}</a>
            </flux:callout.text>
        </flux:callout>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="flex flex-col gap-6 lg:col-span-2">
            <flux:card class="flex flex-col gap-4 rounded-2xl">
                <flux:heading size="lg">{{ __('Proprietário') }}</flux:heading>

                <a href="{{ route('admin.aprovacoes.proprietario', $quadra->dono) }}" wire:navigate class="flex items-center justify-between gap-2 rounded-xl bg-zinc-50 px-4 py-3 hover:bg-zinc-100">
                    <span class="text-sm font-medium text-zinc-900">{{ $quadra->dono?->nome_estabelecimento ?? $quadra->dono?->name ?? '—' }}</span>
                    <flux:badge size="sm" color="{{ $quadra->dono?->status_cadastro->cor() ?? 'zinc' }}">{{ $quadra->dono?->status_cadastro->label() ?? '—' }}</flux:badge>
                </a>
            </flux:card>

            <flux:card class="flex flex-col gap-4 rounded-2xl">
                <flux:heading size="lg">{{ __('Dados da quadra') }}</flux:heading>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <flux:text class="text-xs text-zinc-400">{{ __('Modalidade') }}</flux:text>
                        <flux:text class="font-medium text-zinc-900">{{ $quadra->esporte->label() }}</flux:text>
                    </div>
                    <div>
                        <flux:text class="text-xs text-zinc-400">{{ __('Tipo de piso') }}</flux:text>
                        <flux:text class="font-medium text-zinc-900">{{ $quadra->tipo_piso?->label() ?? '—' }}</flux:text>
                    </div>
                    <div>
                        <flux:text class="text-xs text-zinc-400">{{ __('Cobertura') }}</flux:text>
                        <flux:text class="font-medium text-zinc-900">{{ $quadra->cobertura ? __('Coberta') : __('Descoberta') }}</flux:text>
                    </div>
                    <div>
                        <flux:text class="text-xs text-zinc-400">{{ __('Valor por hora') }}</flux:text>
                        <flux:text class="font-medium text-zinc-900">R$ {{ number_format($quadra->valor_hora, 2, ',', '.') }}</flux:text>
                    </div>
                    <div class="sm:col-span-2">
                        <flux:text class="text-xs text-zinc-400">{{ __('Endereço') }}</flux:text>
                        <flux:text class="font-medium text-zinc-900">{{ $quadra->endereco }}, {{ $quadra->bairro }} — {{ $quadra->cidade }} · {{ $quadra->cep }}</flux:text>
                    </div>
                    <div class="sm:col-span-2">
                        <flux:text class="text-xs text-zinc-400">{{ __('Descrição') }}</flux:text>
                        <flux:text class="text-zinc-700">{{ $quadra->descricao }}</flux:text>
                    </div>
                </div>
            </flux:card>

            <flux:card class="flex flex-col gap-4 rounded-2xl">
                <flux:heading size="lg">{{ __('Fotos') }} ({{ $this->fotos->count() }})</flux:heading>

                @if ($this->fotos->isEmpty())
                    <flux:text class="text-zinc-500">{{ __('Nenhuma foto enviada.') }}</flux:text>
                @else
                    <div class="flex flex-wrap gap-3">
                        @foreach ($this->fotos as $foto)
                            <div class="relative h-28 w-28 shrink-0" wire:key="foto-analise-{{ $foto->id }}">
                                <a href="{{ $foto->url() }}" target="_blank">
                                    <img
                                        src="{{ $foto->url() }}"
                                        alt=""
                                        class="h-28 w-28 rounded-xl object-cover {{ match ($foto->status_moderacao) {
                                            App\Enums\StatusModeracaoFoto::Aceita => 'ring-2 ring-green-500',
                                            App\Enums\StatusModeracaoFoto::Recusada => 'opacity-40 ring-2 ring-red-500',
                                            default => '',
                                        } }}"
                                    >
                                </a>

                                <button
                                    type="button"
                                    wire:click="aceitarFoto({{ $foto->id }})"
                                    class="absolute top-1 left-1 flex size-6 items-center justify-center rounded-full {{ $foto->status_moderacao === App\Enums\StatusModeracaoFoto::Aceita ? 'bg-green-600' : 'bg-black/60 hover:bg-green-600' }} text-white"
                                    aria-label="{{ __('Aceitar foto') }}"
                                >
                                    <flux:icon.check class="size-3.5" />
                                </button>

                                <button
                                    type="button"
                                    wire:click="abrirRecusaFoto({{ $foto->id }})"
                                    class="absolute top-1 right-1 flex size-6 items-center justify-center rounded-full {{ $foto->status_moderacao === App\Enums\StatusModeracaoFoto::Recusada ? 'bg-red-600' : 'bg-black/60 hover:bg-red-600' }} text-white"
                                    aria-label="{{ __('Recusar foto') }}"
                                >
                                    <flux:icon.x-mark class="size-3.5" />
                                </button>

                                @if ($foto->status_moderacao === App\Enums\StatusModeracaoFoto::Recusada && $foto->motivo_recusa)
                                    <flux:tooltip :content="$foto->motivo_recusa">
                                        <flux:icon.information-circle class="absolute right-1 bottom-1 size-5 rounded-full bg-white text-red-600" />
                                    </flux:tooltip>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </flux:card>

            <flux:card class="flex flex-col gap-4 rounded-2xl">
                <flux:heading size="lg">{{ __('Documentos enviados') }}</flux:heading>

                @if ($this->documentos->isEmpty())
                    <flux:text class="text-zinc-500">{{ __('Nenhum documento enviado.') }}</flux:text>
                @else
                    <div class="flex flex-col gap-2">
                        @foreach ($this->documentos as $documento)
                            <a href="{{ $documento->url() }}" target="_blank" class="flex items-center justify-between gap-2 rounded-xl bg-zinc-50 px-4 py-3 hover:bg-zinc-100">
                                <span class="flex items-center gap-2 text-sm font-medium text-zinc-900">
                                    <flux:icon.document class="size-4 text-zinc-400" />
                                    {{ $documento->tipo->label() }}
                                </span>
                                <span class="text-xs text-zinc-400">{{ $documento->nome_original }} · {{ $documento->tamanhoFormatado() }}</span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </flux:card>

            <flux:card class="flex flex-col gap-4 rounded-2xl">
                <flux:heading size="lg">{{ __('Histórico do cadastro') }}</flux:heading>

                @if ($this->historico->isEmpty())
                    <flux:text class="text-zinc-500">{{ __('Nenhum evento registrado ainda.') }}</flux:text>
                @else
                    <div class="flex flex-col gap-3">
                        @foreach ($this->historico as $evento)
                            <div class="border-s-2 border-zinc-200 ps-3" wire:key="historico-quadra-{{ $evento->id }}">
                                <div class="flex items-center gap-2">
                                    <flux:text class="font-medium text-zinc-900">{{ $evento->rotuloEvento() }}</flux:text>
                                    <flux:text class="text-xs text-zinc-400">{{ $evento->created_at->format('d/m/Y H:i') }}</flux:text>
                                </div>
                                @if ($evento->admin)
                                    <flux:text class="text-xs text-zinc-400">{{ __('por') }} {{ $evento->admin->name }}</flux:text>
                                @endif
                                @if ($evento->motivo)
                                    <flux:text class="mt-1 block text-sm text-zinc-600">{{ $evento->motivo }}</flux:text>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </flux:card>
        </div>

        <div class="flex flex-col gap-6">
            <flux:card class="flex flex-col gap-3 rounded-2xl">
                <flux:heading size="lg">{{ __('Checklist de verificação') }}</flux:heading>

                @foreach (\App\Livewire\Admin\Aprovacoes\AnalisarQuadra::CHECKLIST as $chave => $rotulo)
                    <flux:checkbox
                        :checked="$checklist[$chave] ?? false"
                        wire:click="toggleChecklist('{{ $chave }}')"
                        :label="$rotulo"
                    />
                @endforeach
            </flux:card>

            @if (in_array($quadra->status_cadastro, [App\Enums\StatusCadastro::Pendente, App\Enums\StatusCadastro::EmAnalise], true))
                <flux:card class="flex flex-col gap-3 rounded-2xl">
                    <flux:heading size="lg">{{ __('Decisão') }}</flux:heading>

                    <flux:button
                        variant="primary"
                        color="green"
                        class="rounded-full"
                        wire:click="abrirConfirmacao('aprovar')"
                        :disabled="! $quadra->dono?->cadastroAprovado()"
                    >
                        {{ __('Aprovar') }}
                    </flux:button>
                    <flux:button variant="outline" class="rounded-full !border-amber-300 !text-amber-700 hover:!bg-amber-50" wire:click="abrirConfirmacao('ajustes')">
                        {{ __('Solicitar ajustes') }}
                    </flux:button>
                    <flux:button variant="outline" class="rounded-full !border-red-300 !text-red-600 hover:!bg-red-50" wire:click="abrirConfirmacao('rejeitar')">
                        {{ __('Rejeitar') }}
                    </flux:button>
                </flux:card>
            @endif
        </div>
    </div>

    <flux:modal name="confirmar-acao" class="w-full md:w-96">
        <form wire:submit="confirmar" class="flex flex-col gap-4">
            <flux:heading size="lg">
                {{ match($acao) {
                    'aprovar' => __('Aprovar esta quadra?'),
                    'ajustes' => __('Solicitar ajustes?'),
                    'rejeitar' => __('Rejeitar esta quadra?'),
                    default => '',
                } }}
            </flux:heading>

            @if ($acao !== 'aprovar')
                <flux:textarea wire:model="motivo" :label="$acao === 'rejeitar' ? __('Motivo da rejeição') : __('O que precisa ser ajustado')" rows="4" required />
            @else
                <flux:text>{{ __('A quadra ficará visível na busca e poderá receber reservas.') }}</flux:text>
            @endif

            <div class="flex gap-2">
                <flux:modal.close>
                    <flux:button type="button" variant="outline" class="w-full rounded-full">{{ __('Cancelar') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" color="orange" class="w-full rounded-full">{{ __('Confirmar') }}</flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="recusar-foto" class="w-full md:w-96">
        <form wire:submit="confirmarRecusaFoto" class="flex flex-col gap-4">
            <flux:heading size="lg">{{ __('Recusar esta foto?') }}</flux:heading>
            <flux:text class="text-zinc-500">{{ __('Explique o motivo para o dono saber o que corrigir nessa foto específica. Ele será avisado por notificação e e-mail.') }}</flux:text>

            <flux:textarea wire:model="motivoRecusaFoto" :label="__('Motivo da recusa')" rows="4" required />

            <div class="flex gap-2">
                <flux:modal.close>
                    <flux:button type="button" variant="outline" class="w-full rounded-full">{{ __('Cancelar') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" color="red" class="w-full rounded-full">{{ __('Recusar e avisar dono') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
