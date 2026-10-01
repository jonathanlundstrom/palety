@php use App\Enumerables\UserRole; @endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky collapsible class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <flux:sidebar.header>
                <flux:sidebar.brand href="{{ route('dashboard') }}" name="{{ config('app.name') }}" wire:navigate>
                    <x-slot name="logo" class="size-6 rounded-full bg-blue-700 text-yellow-300 text-xs font-bold">
                        <flux:icon name="square-3-stack-3d" variant="micro" />
                    </x-slot>
                </flux:sidebar.brand>

                <flux:sidebar.collapse class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.item icon="chart-bar-square" :href="route('dashboard')" wire:navigate>{{ __('pages.dashboard.title') }}</flux:sidebar.item>
                <flux:sidebar.item icon="cube" :href="route('parcels')" wire:navigate>{{ __('pages.parcels.title') }}</flux:sidebar.item>
                <flux:sidebar.item icon="square-3-stack-3d" :href="route('pallets')" wire:navigate>{{ __('pages.pallets.title') }}</flux:sidebar.item>
                <flux:sidebar.item icon="truck" :href="route('transports')" wire:navigate>{{ __('pages.transports.title') }}</flux:sidebar.item>
                @if (Auth::user()->role === UserRole::ADMIN)
                    <flux:sidebar.group icon="star" heading="Administration" expandable>
                        <flux:sidebar.item icon="list-bullet" :href="route('content')" wire:navigate>{{ __('pages.content.title') }}</flux:sidebar.item>
                        <flux:sidebar.item icon="map-pin" :href="route('recipients')" wire:navigate>{{ __('pages.recipients.title') }}</flux:sidebar.item>
                        <flux:sidebar.item icon="users" :href="route('users')" wire:navigate>{{ __('pages.users.title') }}</flux:sidebar.item>
                    </flux:sidebar.group>
                @endif
            </flux:sidebar.nav>

            <flux:spacer />

            <!-- Desktop User Menu -->
            <flux:dropdown position="bottom" align="start">
                <flux:sidebar.profile
                    :name="auth()->user()->name"
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-up-down"
                />

                <flux:menu class="w-[220px]">
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('settings.profile')" icon="cog-6-tooth" icon:variant="outline" wire:navigate>{{ __('pages.settings.title') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('app.logout') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden bg-white dark:bg-neutral-800 border-b-1 dark:border-b-white/10" sticky>
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                    class="-mr-4"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('settings.profile')" icon="cog-6-tooth" icon:variant="outline" wire:navigate>{{ __('pages.settings.title') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('app.logout') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @persist('toast')
            <flux:toast />
        @endpersist

        {{-- Shared components --}}
        <livewire:modals.delete-modal />
        <livewire:modals.merge-content-modal />
        <livewire:modals.delivered-modal />
        <livewire:modals.add-to-transport-modal />

        @fluxScripts
    </body>
</html>
