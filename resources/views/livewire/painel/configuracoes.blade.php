<flux:accent color="orange" class="flex w-full flex-col gap-6">
    <div>
        <flux:heading size="xl" class="text-3xl! sm:text-4xl!">{{ __('Configurações') }}</flux:heading>
        <flux:text class="mt-1 text-base text-zinc-400">{{ __('Gerencie os dados do seu estabelecimento e preferências.') }}</flux:text>
    </div>

    <flux:card class="rounded-2xl">
        <flux:heading size="lg" class="mb-2">{{ __('Dados do Estabelecimento') }}</flux:heading>

        @if (! $editandoEstabelecimento)
            <div class="flex flex-col gap-6">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <flux:text class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Nome do Estabelecimento') }}</flux:text>
                        <flux:text class="text-zinc-500 dark:text-zinc-400">{{ $nomeEstabelecimento ?: '—' }}</flux:text>
                    </div>
                    <div>
                        <flux:text class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('CNPJ') }}</flux:text>
                        <flux:text class="text-zinc-500 dark:text-zinc-400">{{ $this->cnpjFormatado() ?: '—' }}</flux:text>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <flux:text class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Responsável') }}</flux:text>
                        <flux:text class="text-zinc-500 dark:text-zinc-400">{{ $name ?: '—' }}</flux:text>
                    </div>
                    <div>
                        <flux:text class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Telefone') }}</flux:text>
                        <flux:text class="text-zinc-500 dark:text-zinc-400">{{ $telefone ?: '—' }}</flux:text>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <flux:text class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('E-mail') }}</flux:text>
                        <flux:text class="text-zinc-500 dark:text-zinc-400">{{ $email ?: '—' }}</flux:text>
                    </div>
                    <div>
                        <flux:text class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Endereço') }}</flux:text>
                        <flux:text class="text-zinc-500 dark:text-zinc-400">{{ $endereco ?: '—' }}</flux:text>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <flux:text class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Cidade') }}</flux:text>
                        <flux:text class="text-zinc-500 dark:text-zinc-400">{{ $cidade ?: '—' }}</flux:text>
                    </div>
                    <div>
                        <flux:text class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Estado (UF)') }}</flux:text>
                        <flux:text class="text-zinc-500 dark:text-zinc-400">{{ $estado ?: '—' }}</flux:text>
                    </div>
                </div>

                <div>
                    <flux:button type="button" variant="primary" color="orange" class="rounded-full" wire:click="editarEstabelecimento">
                        {{ __('Editar') }}
                    </flux:button>
                </div>
            </div>
        @else
            <form wire:submit="salvar" class="flex flex-col gap-6">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <flux:input wire:model="nomeEstabelecimento" :label="__('Nome do Estabelecimento')" class="rounded-full" />
                    <flux:input :value="$this->cnpjFormatado()" :label="__('CNPJ')" class="rounded-full" disabled />
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <flux:input wire:model="name" :label="__('Responsável')" class="rounded-full" />
                    <flux:input wire:model="telefone" :label="__('Telefone')" class="rounded-full" placeholder="(11) 91234-5678" />
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <flux:input wire:model="email" type="email" :label="__('E-mail')" class="rounded-full" />
                    <flux:input wire:model="endereco" :label="__('Endereço')" class="rounded-full" />
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <flux:input wire:model="cidade" :label="__('Cidade')" class="rounded-full" />
                    <flux:input wire:model="estado" :label="__('Estado (UF)')" class="rounded-full" maxlength="2" />
                </div>

                <div class="flex gap-3">
                    <flux:button type="submit" variant="primary" color="orange" class="rounded-full">
                        {{ __('Salvar') }}
                    </flux:button>
                    <flux:button type="button" variant="outline" class="rounded-full !border-orange-300 !text-orange-600 hover:!bg-orange-50 dark:!border-orange-400/30 dark:!text-orange-400 dark:hover:!bg-orange-400/10" wire:click="cancelarEdicaoEstabelecimento">
                        {{ __('Cancelar') }}
                    </flux:button>
                </div>
            </form>
        @endif
    </flux:card>

    <flux:card class="rounded-2xl">
        <flux:heading size="lg" class="mb-4">{{ __('Segurança') }}</flux:heading>

        <div class="flex flex-col gap-6">
            <div>
                <flux:heading size="sm" class="mb-3">{{ __('Alterar senha') }}</flux:heading>

                <form wire:submit="updatePassword" class="flex flex-col gap-4">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <flux:input wire:model="currentPassword" type="password" :label="__('Senha atual')" class="rounded-full" autocomplete="current-password" />
                        <flux:input wire:model="newPassword" type="password" :label="__('Nova senha')" class="rounded-full" autocomplete="new-password" />
                        <flux:input wire:model="newPassword_confirmation" type="password" :label="__('Confirmar nova senha')" class="rounded-full" autocomplete="new-password" />
                    </div>

                    <div>
                        <flux:button type="submit" size="sm" variant="primary" color="orange" class="rounded-full">
                            {{ __('Salvar senha') }}
                        </flux:button>
                    </div>
                </form>
            </div>

            @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::twoFactorAuthentication()))
                <div class="border-t border-zinc-100 pt-6 dark:border-zinc-700">
                    <flux:heading size="sm" class="mb-1">{{ __('Autenticação de dois fatores') }}</flux:heading>
                    <flux:text class="mb-3 text-zinc-400">{{ __('Peça um código extra do seu celular ao entrar na conta.') }}</flux:text>

                    @if ($twoFactorEnabled)
                        <div class="flex flex-col items-start gap-3">
                            <flux:badge color="green" size="sm">{{ __('Ativado') }}</flux:badge>

                            <livewire:pages::settings.two-factor.recovery-codes />

                            <flux:button
                                size="sm"
                                variant="outline"
                                class="rounded-full !border-red-300 !text-red-600 hover:!bg-red-50 dark:!border-red-400/30 dark:!text-red-400 dark:hover:!bg-red-400/10"
                                wire:click="disableTwoFactor"
                            >
                                {{ __('Desativar 2FA') }}
                            </flux:button>
                        </div>
                    @else
                        <div class="flex flex-col items-start gap-3">
                            <flux:badge color="red" size="sm">{{ __('Desativado') }}</flux:badge>

                            <flux:modal.trigger name="two-factor-setup-modal">
                                <flux:button size="sm" variant="primary" color="orange" class="rounded-full" wire:click="$dispatch('start-two-factor-setup')">
                                    {{ __('Ativar 2FA') }}
                                </flux:button>
                            </flux:modal.trigger>

                            <livewire:pages::settings.two-factor-setup-modal :requires-confirmation="$requiresConfirmation" />
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </flux:card>

    <flux:card class="rounded-2xl">
        <flux:heading size="lg" class="mb-4">{{ __('Horário de Funcionamento') }}</flux:heading>

        <div class="flex flex-col gap-4">
            <div class="flex items-center gap-4">
                <div class="w-40 shrink-0"><flux:switch wire:model.live="diasUteisAberto" :label="__('Segunda a sexta')" /></div>

                @if ($diasUteisAberto)
                    <div class="flex-1"><flux:input wire:model.live="diasUteisInicio" type="time" class="rounded-full" /></div>
                    <div class="flex-1"><flux:input wire:model.live="diasUteisFim" type="time" class="rounded-full" /></div>
                @else
                    <flux:text class="text-zinc-400">{{ __('Fechado') }}</flux:text>
                @endif
            </div>

            <div class="flex items-center gap-4">
                <div class="w-40 shrink-0"><flux:switch wire:model.live="sabadoAberto" :label="__('Sábado')" /></div>

                @if ($sabadoAberto)
                    <div class="flex-1"><flux:input wire:model.live="sabadoInicio" type="time" class="rounded-full" /></div>
                    <div class="flex-1"><flux:input wire:model.live="sabadoFim" type="time" class="rounded-full" /></div>
                @else
                    <flux:text class="text-zinc-400">{{ __('Fechado') }}</flux:text>
                @endif
            </div>

            <div class="flex items-center gap-4">
                <div class="w-40 shrink-0"><flux:switch wire:model.live="domingoAberto" :label="__('Domingo')" /></div>

                @if ($domingoAberto)
                    <div class="flex-1"><flux:input wire:model.live="domingoInicio" type="time" class="rounded-full" /></div>
                    <div class="flex-1"><flux:input wire:model.live="domingoFim" type="time" class="rounded-full" /></div>
                @else
                    <flux:text class="text-zinc-400">{{ __('Fechado') }}</flux:text>
                @endif
            </div>
        </div>
    </flux:card>

    <flux:card class="rounded-2xl">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <flux:heading size="lg">{{ __('Exceções de data') }}</flux:heading>
                <flux:text class="text-zinc-400">{{ __('Fechamento ou horários especiais para dias específicos, como feriados ou eventos.') }}</flux:text>
            </div>

            <flux:button size="sm" variant="outline" icon="plus" class="rounded-full !border-orange-300 !text-orange-600 hover:!bg-orange-50 dark:!border-orange-400/30 dark:!text-orange-400 dark:hover:!bg-orange-400/10" wire:click="abrirModalExcecao">
                {{ __('Adicionar exceção') }}
            </flux:button>
        </div>

        @if ($this->excecoes->isEmpty())
            <flux:text class="mt-4 text-zinc-400">{{ __('Nenhuma exceção cadastrada.') }}</flux:text>
        @else
            <div class="mt-4 flex flex-col divide-y divide-zinc-100 overflow-hidden rounded-xl border border-zinc-100 dark:divide-zinc-700 dark:border-zinc-700">
                @foreach ($this->excecoes as $excecao)
                    <div class="flex items-center justify-between gap-3 px-4 py-2.5" wire:key="excecao-{{ $excecao->id }}">
                        <div class="flex items-center gap-2 text-sm">
                            <flux:icon.calendar-days variant="mini" class="text-red-500 dark:text-red-400" />
                            <span class="text-zinc-700 dark:text-zinc-200">
                                {{ $excecao->data->format('d/m/Y') }} - {{ $excecao->descricao }}
                                <span class="text-zinc-400">
                                    ({{ $excecao->fechado_dia_todo
                                        ? __('Fechado o dia todo')
                                        : __('Horário Especial: :inicio - :fim', ['inicio' => substr($excecao->hora_abertura, 0, 5), 'fim' => substr($excecao->hora_fechamento, 0, 5)]) }})
                                </span>
                            </span>
                        </div>

                        <button type="button" wire:click="removerExcecao({{ $excecao->id }})" class="text-zinc-400 hover:text-red-600 dark:hover:text-red-400">
                            <flux:icon.x-mark variant="mini" />
                        </button>
                    </div>
                @endforeach
            </div>
        @endif
    </flux:card>

    <flux:card class="rounded-2xl">
        <flux:heading size="lg">{{ __('Fechar estabelecimento temporariamente') }}</flux:heading>
        <flux:text class="mb-4 text-zinc-400">{{ __('Feche o estabelecimento por um período sem alterar sua agenda.') }}</flux:text>

        @if (auth()->user()->pausa_ativa)
            <div class="flex flex-col gap-3 rounded-xl bg-amber-50 p-4 text-sm text-amber-700 dark:bg-amber-400/10 dark:text-amber-300">
                <p>
                    {{ __('Suas quadras estão pausadas.') }}
                    @if (auth()->user()->pausa_motivo)
                        {{ __('Motivo: :motivo.', ['motivo' => auth()->user()->pausa_motivo]) }}
                    @endif
                    @if (auth()->user()->pausa_indeterminada)
                        {{ __('Sem previsão de retorno.') }}
                    @elseif (auth()->user()->pausa_ate)
                        {{ __('Até :data.', ['data' => auth()->user()->pausa_ate->format('d/m/Y')]) }}
                    @endif
                </p>

                <flux:button size="sm" variant="primary" color="orange" class="w-fit rounded-full" wire:click="retomarQuadra">
                    {{ __('Retomar Quadra') }}
                </flux:button>
            </div>
        @else
            <form wire:submit="pausarQuadra" class="flex flex-col gap-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <flux:input wire:model="pausaMotivo" :label="__('Motivo')" class="rounded-full" placeholder="{{ __('Digite aqui o seu motivo') }}" />

                    <div>
                        <flux:input wire:model="pausaAte" type="date" :label="__('Até quando')" class="rounded-full" :disabled="$pausaIndeterminada" />
                        <div class="mt-4">
                            <flux:checkbox wire:model="pausaIndeterminada" :label="__('Por tempo indeterminado')" />
                        </div>
                    </div>
                </div>

                <div>
                    <flux:button type="submit" variant="outline" class="rounded-full !border-red-300 !text-red-600 hover:!bg-red-50 dark:!border-red-400/30 dark:!text-red-400 dark:hover:!bg-red-400/10">
                        {{ __('Fechar Estabelecimento') }}
                    </flux:button>
                </div>
            </form>
        @endif
    </flux:card>

    <flux:card class="rounded-2xl">
        <flux:heading size="lg" class="mb-1">{{ __('Política de Cancelamento') }}</flux:heading>
        <flux:text class="mb-4 text-zinc-400">{{ __('Prazo mínimo, em horas antes do horário reservado, para o cliente cancelar uma reserva confirmada sem custo.') }}</flux:text>

        <form wire:submit="salvarPoliticaCancelamento" class="flex flex-wrap items-end gap-4">
            <div class="w-40">
                <flux:input wire:model="prazoCancelamentoHoras" type="number" min="0" max="168" :label="__('Horas de antecedência')" class="rounded-full" />
            </div>

            <flux:button type="submit" size="sm" variant="primary" color="orange" class="rounded-full">
                {{ __('Salvar') }}
            </flux:button>
        </form>
    </flux:card>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <flux:card class="rounded-2xl">
            <flux:heading size="lg" class="mb-4">{{ __('Métodos de pagamento aceitos') }}</flux:heading>

            <div class="flex flex-col gap-3">
                <flux:checkbox wire:model.live="aceitaCartao" :label="__('Cartão de Crédito')" />
                <flux:checkbox wire:model.live="aceitaPix" :label="__('PIX')" />
            </div>
        </flux:card>

        <flux:card class="rounded-2xl">
            <flux:heading size="lg" class="mb-4">{{ __('Notificações') }}</flux:heading>

            <div class="flex flex-col gap-3">
                <flux:switch wire:model.live="notifDiasUteis" :label="__('Segunda a sexta')" />
                <flux:switch wire:model.live="notifCancelamento" :label="__('Cancelamento')" />
                <flux:switch wire:model.live="notifMensagensClientes" :label="__('Mensagens Clientes')" />
            </div>
        </flux:card>
    </div>

    <flux:card class="rounded-2xl">
        <flux:heading size="lg" class="mb-1">{{ __('Aparência') }}</flux:heading>
        <flux:text class="mb-4 text-zinc-400">{{ __('Escolha entre o tema claro e escuro para o painel.') }}</flux:text>

        <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
            <flux:radio value="light" icon="sun">{{ __('Claro') }}</flux:radio>
            <flux:radio value="dark" icon="moon">{{ __('Escuro') }}</flux:radio>
            <flux:radio value="system" icon="computer-desktop">{{ __('Sistema') }}</flux:radio>
        </flux:radio.group>
    </flux:card>

    <flux:card class="rounded-2xl border-red-100 dark:border-red-900/40">
        <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('Excluir sua conta remove todas as quadras e reservas associadas permanentemente.') }}</flux:text>

        <div class="mt-3">
            <flux:modal.trigger name="confirm-user-deletion">
                <flux:button variant="outline" class="rounded-full !border-red-300 !text-red-600 hover:!bg-red-50 dark:!border-red-400/30 dark:!text-red-400 dark:hover:!bg-red-400/10">
                    {{ __('Excluir conta') }}
                </flux:button>
            </flux:modal.trigger>
        </div>
    </flux:card>

    <livewire:pages::settings.delete-user-modal />

    <flux:modal name="adicionar-excecao" class="w-full md:w-96">
        <flux:accent color="orange">
            <form wire:submit="salvarExcecao" class="flex flex-col gap-4">
                <flux:heading size="lg">{{ __('Adicionar exceção de data') }}</flux:heading>

                <flux:input wire:model="excecaoData" type="date" :label="__('Data')" class="rounded-full" min="{{ now()->toDateString() }}" />

                <flux:input wire:model="excecaoDescricao" :label="__('Descrição')" class="rounded-full" placeholder="{{ __('Ex: Natal') }}" />

                <flux:checkbox wire:model.live="excecaoFechadoDiaTodo" :label="__('Fechado o dia todo')" />

                @unless ($excecaoFechadoDiaTodo)
                    <div class="grid grid-cols-2 gap-4">
                        <flux:input wire:model="excecaoHoraAbertura" type="time" :label="__('Abertura')" class="rounded-full" />
                        <flux:input wire:model="excecaoHoraFechamento" type="time" :label="__('Fechamento')" class="rounded-full" />
                    </div>
                @endunless

                <div class="flex justify-end gap-3">
                    <flux:modal.close>
                        <flux:button variant="outline" class="rounded-full !border-orange-300 !text-orange-600 hover:!bg-orange-50 dark:!border-orange-400/30 dark:!text-orange-400 dark:hover:!bg-orange-400/10">
                            {{ __('Cancelar') }}
                        </flux:button>
                    </flux:modal.close>

                    <flux:button type="submit" variant="primary" color="orange" class="rounded-full">
                        {{ __('Adicionar') }}
                    </flux:button>
                </div>
            </form>
        </flux:accent>
    </flux:modal>
</flux:accent>
