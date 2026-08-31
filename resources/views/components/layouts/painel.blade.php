@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-zinc-50">
        <div class="border-b border-zinc-200 bg-white">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
                <a href="{{ route('home') }}" class="flex items-center gap-2" wire:navigate>
                    <span class="flex size-8 items-center justify-center rounded-md bg-orange-500 text-white">
                        <x-app-logo-icon class="size-5 fill-current" />
                    </span>
                    <span class="text-lg font-bold text-zinc-900">{{ config('app.name') }}</span>
                </a>

                <flux:dropdown position="bottom" align="end">
                    <button type="button" class="flex items-center gap-2 rounded-full py-1 ps-1 pe-3 hover:bg-zinc-100">
                        <flux:avatar size="xs" :name="auth()->user()->name" :initials="auth()->user()->initials()" color="orange" />
                        <span class="hidden text-sm font-semibold text-orange-600 sm:inline">
                            {{ __('Painel do Dono') }}
                        </span>
                    </button>

                    <flux:menu>
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <flux:avatar :name="auth()->user()->name" :initials="auth()->user()->initials()" color="orange" />
                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                            </div>
                        </div>
                        <flux:menu.separator />
                        <flux:menu.radio.group>
                            <flux:menu.item
                                :href="route('painel.configuracoes')"
                                icon="cog"
                                wire:navigate
                            >
                                {{ __('Configurações da conta') }}
                            </flux:menu.item>
                            <form method="POST" action="{{ route('logout') }}" class="w-full">
                                @csrf
                                <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full cursor-pointer">
                                    {{ __('Sair') }}
                                </flux:menu.item>
                            </form>
                        </flux:menu.radio.group>
                    </flux:menu>
                </flux:dropdown>
            </div>

            <nav class="bg-orange-500">
                <div class="mx-auto flex max-w-7xl items-center justify-center gap-1 overflow-x-auto px-4 py-2 [&>*]:shrink-0 sm:px-6 lg:px-8">
                    <x-painel-nav-item :href="route('painel.dashboard')" :current="request()->routeIs('painel.dashboard')">
                        {{ __('Visão Geral') }}
                    </x-painel-nav-item>
                    <x-painel-nav-item :href="route('painel.quadras')" :current="request()->routeIs('painel.quadras')">
                        {{ __('Minhas Quadras') }}
                    </x-painel-nav-item>
                    <x-painel-nav-item :href="route('painel.reservas')" :current="request()->routeIs('painel.reservas')">
                        {{ __('Reservas') }}
                    </x-painel-nav-item>
                    <x-painel-nav-item :href="route('painel.financeiro')" :current="request()->routeIs('painel.financeiro')">
                        {{ __('Financeiro') }}
                    </x-painel-nav-item>
                    <x-painel-nav-item :href="route('painel.mensagens')" :current="request()->routeIs('painel.mensagens')">
                        {{ __('Mensagens') }}
                    </x-painel-nav-item>
                    <x-painel-nav-item :href="route('painel.agendamento-manual')" :current="request()->routeIs('painel.agendamento-manual')">
                        {{ __('Agendamento Manual') }}
                    </x-painel-nav-item>
                    <x-painel-nav-item :href="route('painel.configuracoes')" :current="request()->routeIs('painel.configuracoes')">
                        {{ __('Configurações') }}
                    </x-painel-nav-item>
                </div>
            </nav>
        </div>

        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            {{ $slot }}
        </main>

        @fluxScripts
    </body>
</html>
