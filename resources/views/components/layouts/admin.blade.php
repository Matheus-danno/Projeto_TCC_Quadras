@props(['title' => null])

@php
    $statusFilaAprovacoes = [\App\Enums\StatusCadastro::Pendente, \App\Enums\StatusCadastro::EmAnalise];
    $totalPendenteAprovacao = \App\Models\User::where('role', \App\Enums\UserRole::DonoQuadra)->whereIn('status_cadastro', $statusFilaAprovacoes)->count()
        + \App\Models\Quadra::whereIn('status_cadastro', $statusFilaAprovacoes)->count();
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-zinc-50">
        <div class="flex min-h-screen [grid-template-areas:'sidebar_content']" x-data>
            <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-white">
                <flux:sidebar.toggle class="lg:hidden" />

                <flux:sidebar.header>
                    <a href="{{ route('home') }}" class="flex items-center" wire:navigate>
                        <img src="{{ asset('imagens/tela_inicial/logo_login.png') }}" alt="{{ config('app.name') }}" class="h-8 w-auto">
                    </a>
                </flux:sidebar.header>

                <flux:sidebar.nav>
                    <flux:sidebar.item :href="route('admin.dashboard')" :current="request()->routeIs('admin.dashboard')" icon="squares-2x2" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item
                        :href="route('admin.aprovacoes')"
                        :current="request()->routeIs('admin.aprovacoes*')"
                        icon="inbox"
                        :badge="$totalPendenteAprovacao > 0 ? $totalPendenteAprovacao : null"
                        badge:color="orange"
                        wire:navigate
                    >
                        {{ __('Aprovações') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item :href="route('admin.financeiro')" :current="request()->routeIs('admin.financeiro*')" icon="banknotes" wire:navigate>
                        {{ __('Financeiro') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item :href="route('admin.quadras')" :current="request()->routeIs('admin.quadras')" icon="building-office-2" wire:navigate>
                        {{ __('Quadras') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item :href="route('admin.usuarios')" :current="request()->routeIs('admin.usuarios*')" icon="users" wire:navigate>
                        {{ __('Usuários') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item :href="route('admin.reservas')" :current="request()->routeIs('admin.reservas')" icon="calendar-days" wire:navigate>
                        {{ __('Reservas') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item :href="route('admin.configuracoes')" :current="request()->routeIs('admin.configuracoes')" icon="cog-6-tooth" wire:navigate>
                        {{ __('Configurações') }}
                    </flux:sidebar.item>
                </flux:sidebar.nav>

                <flux:sidebar.spacer />

                <flux:sidebar.nav>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <flux:sidebar.item type="submit" icon="arrow-right-start-on-rectangle">
                            {{ __('Sair') }}
                        </flux:sidebar.item>
                    </form>
                </flux:sidebar.nav>
            </flux:sidebar>

            <div class="flex min-w-0 flex-1 flex-col">
                <div class="flex items-center justify-between gap-4 border-b border-zinc-200 bg-white px-4 py-3 sm:px-6 lg:px-8">
                    <div class="flex items-center gap-3">
                        <flux:sidebar.toggle class="lg:hidden" />
                        <flux:heading size="lg">{{ $title ?? __('Administração') }}</flux:heading>
                    </div>

                    <flux:dropdown position="bottom" align="end">
                        <button type="button" class="flex items-center gap-2 rounded-full py-1 ps-1 pe-3 hover:bg-zinc-100">
                            <flux:avatar size="xs" :name="auth()->user()->name" :initials="auth()->user()->initials()" color="zinc" />
                            <span class="hidden text-sm font-semibold text-zinc-700 sm:inline">
                                {{ auth()->user()->name }}
                            </span>
                        </button>

                        <flux:menu>
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar :name="auth()->user()->name" :initials="auth()->user()->initials()" color="zinc" />
                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                            <flux:menu.separator />
                            <form method="POST" action="{{ route('logout') }}" class="w-full">
                                @csrf
                                <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full cursor-pointer">
                                    {{ __('Sair') }}
                                </flux:menu.item>
                            </form>
                        </flux:menu>
                    </flux:dropdown>
                </div>

                <main class="flex-1 px-4 py-8 sm:px-6 lg:px-8">
                    {{ $slot }}
                </main>
            </div>
        </div>

        @fluxScripts
    </body>
</html>
