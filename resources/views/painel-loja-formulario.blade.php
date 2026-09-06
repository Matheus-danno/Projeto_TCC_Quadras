<x-layouts.painel :title="isset($produto) ? __('Editar Produto') : __('Cadastrar Produto')">
    <livewire:painel.loja.formulario :produto="$produto ?? null" />
</x-layouts.painel>
