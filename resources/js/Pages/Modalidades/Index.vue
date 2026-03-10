<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    modalidades: Array,
});

const formatPrecio = (value) => {
    if (value == null) return '—';
    const n = Number(value);
    return isNaN(n) ? '—' : n.toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const destroy = (id) => {
    if (confirm('¿Eliminar esta modalidad de liquidación?')) {
        router.delete(route('modalidades.destroy', id));
    }
};
</script>

<template>
    <Head title="Modalidad de liquidación" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Modalidad de liquidación
                </h2>
                <Link :href="route('modalidades.create')">
                    <PrimaryButton>Nueva modalidad</PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div v-if="$page.props.flash?.success" class="mb-4 rounded-md bg-green-100 p-4 text-green-700">
                    {{ $page.props.flash.success }}
                </div>
                <div v-if="$page.props.flash?.error" class="mb-4 rounded-md bg-red-100 p-4 text-red-700">
                    {{ $page.props.flash.error }}
                </div>
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div v-if="modalidades.length === 0" class="text-center text-gray-500">
                            No hay modalidades. Creá una (ej. Por día, Por Bin, Por Maletas) con su precio por unidad.
                        </div>
                        <table v-else class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Nombre</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Precio por unidad</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Empleados</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="mod in modalidades" :key="mod.id">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{{ mod.nombre }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-gray-900">$ {{ formatPrecio(mod.precio_por_unidad) }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">{{ mod.employees_count ?? 0 }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                        <Link :href="route('modalidades.edit', mod.id)" class="text-indigo-600 hover:text-indigo-900">Editar</Link>
                                        <button type="button" @click="destroy(mod.id)" class="ml-4 text-red-600 hover:text-red-900">Eliminar</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
