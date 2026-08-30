<div class="flex w-full flex-col gap-6">
    <div>
        <flux:heading size="xl" class="text-3xl! sm:text-4xl!">{{ __('Configurações') }}</flux:heading>
        <flux:text class="mt-1 text-base text-zinc-400">{{ __('Atualize os dados do seu estabelecimento.') }}</flux:text>
    </div>

    <flux:card class="rounded-2xl">
        <flux:heading size="lg" class="mb-2">{{ __('Dados do Estabelecimento') }}</flux:heading>

        <form wire:submit="salvar" class="flex flex-col gap-6">
            <div>
                <flux:input :value="$this->cnpjFormatado()" :label="__('CNPJ')" class="rounded-full" disabled />
                <flux:text class="mt-1 text-xs text-zinc-500">{{ __('O CNPJ não pode ser alterado após o cadastro.') }}</flux:text>
            </div>

            <flux:input wire:model="nomeEstabelecimento" :label="__('Nome do Estabelecimento')" class="rounded-full" />

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <flux:input wire:model="telefone" :label="__('Telefone')" class="rounded-full" placeholder="(11) 91234-5678" />
                <flux:input wire:model="endereco" :label="__('Endereço')" class="rounded-full" />
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <flux:input wire:model="cidade" :label="__('Cidade')" class="rounded-full" />
                <flux:input wire:model="estado" :label="__('Estado (UF)')" class="rounded-full" maxlength="2" />
            </div>

            <div class="flex justify-end">
                <flux:button type="submit" variant="primary" color="orange" class="rounded-full">
                    {{ __('Salvar Alterações') }}
                </flux:button>
            </div>
        </form>
    </flux:card>

    <flux:card class="rounded-2xl">
        <flux:heading size="lg" class="mb-1">{{ __('Aparência') }}</flux:heading>
        <flux:text class="mb-4 text-zinc-400">{{ __('Escolha entre o tema claro e escuro para o painel.') }}</flux:text>

        <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
            <flux:radio value="light" icon="sun">{{ __('Claro') }}</flux:radio>
            <flux:radio value="dark" icon="moon">{{ __('Escuro') }}</flux:radio>
            <flux:radio value="system" icon="computer-desktop">{{ __('Sistema') }}</flux:radio>
        </flux:radio.group>
    </flux:card>
</div>
