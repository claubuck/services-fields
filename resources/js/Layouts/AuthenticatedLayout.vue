<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';

const sidebarOpen = ref(false);

const navItems = [
    { href: 'dashboard', name: 'Dashboard', current: () => route().current('dashboard') },
    { href: 'establishments.index', name: 'Establecimientos', current: () => route().current('establishments.*') },
    { href: 'employees.index', name: 'Empleados', current: () => route().current('employees.*') },
    { href: 'categories.index', name: 'Categorías', current: () => route().current('categories.*'), sub: true },
    { href: 'employer.edit', name: 'Empleador', current: () => route().current('employer.*') },
    { href: 'liquidations.index', name: 'Liquidaciones', current: () => route().current('liquidations.*') },
    { href: 'modalidades.index', name: 'Modalidad de liquidación', current: () => route().current('modalidades.*'), sub: true },
];

const linkRoute = (item) => route(item.href);
const isActive = (item) => item.current();
</script>

<template>
    <div class="flex min-h-screen bg-gray-100">
        <!-- Sidebar -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-40 flex w-64 flex-col border-r border-gray-200 bg-white transition-transform duration-200 lg:translate-x-0',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full',
            ]"
        >
            <div class="flex h-16 shrink-0 items-center border-b border-gray-200 px-4">
                <Link :href="route('dashboard')" class="flex items-center gap-2">
                    <ApplicationLogo class="block h-9 w-auto fill-current text-gray-800" />
                    <span class="text-sm font-semibold text-gray-800">Services Fields</span>
                </Link>
            </div>
            <nav class="flex-1 space-y-0.5 overflow-y-auto px-3 py-4">
                <Link
                    v-for="item in navItems"
                    :key="item.href"
                    :href="linkRoute(item)"
                    :class="[
                        'block rounded-lg px-3 py-2.5 text-sm font-medium transition-colors',
                        item.sub ? 'pl-6' : '',
                        isActive(item)
                            ? 'bg-indigo-50 text-indigo-700'
                            : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900',
                    ]"
                >
                    {{ item.name }}
                </Link>
            </nav>
            <div class="border-t border-gray-200 p-3">
                <div class="rounded-lg px-3 py-2 text-sm text-gray-700">
                    <div class="font-medium truncate">{{ $page.props.auth.user.name }}</div>
                    <div class="truncate text-gray-500 text-xs">{{ $page.props.auth.user.email }}</div>
                </div>
                <div class="mt-2 space-y-0.5">
                    <Link
                        :href="route('profile.edit')"
                        class="block rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-100"
                    >
                        Perfil
                    </Link>
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="block w-full rounded-lg px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-100"
                    >
                        Cerrar sesión
                    </Link>
                </div>
            </div>
        </aside>

        <!-- Overlay móvil -->
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 z-30 bg-gray-900/50 lg:hidden"
            @click="sidebarOpen = false"
        />

        <!-- Contenido principal: altura fija y scroll solo dentro del main -->
        <div class="flex min-h-screen flex-1 flex-col lg:pl-64">
            <header class="flex h-16 shrink-0 items-center gap-4 border-b border-gray-200 bg-white px-4 lg:px-8">
                <button
                    type="button"
                    class="shrink-0 rounded-md p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700 lg:hidden"
                    @click="sidebarOpen = !sidebarOpen"
                >
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <div v-if="$slots.header" class="min-w-0 flex-1">
                    <slot name="header" />
                </div>
            </header>

            <main class="min-h-0 flex-1 overflow-y-auto px-4 py-6 lg:px-8">
                <slot />
            </main>
        </div>
    </div>
</template>
