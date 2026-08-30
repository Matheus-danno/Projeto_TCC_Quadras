<x-layouts.painel :title="isset($quadra) ? __('Editar Quadra') : __('Cadastrar Quadra')">
    <livewire:painel.quadras.formulario :quadra="$quadra ?? null" />
</x-layouts.painel>
