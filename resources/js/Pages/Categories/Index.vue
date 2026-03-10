<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    categories: Array,
});

const destroy = (id) => {
    if (confirm('¿Eliminar esta categoría?')) {
        router.delete(route('categories.destroy', id));
    }
};
</script>

<template>
    <Head title="Categorías" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Categorías
                </h2>
                <Link :href="route('categories.create')">
                    <PrimaryButton>Nueva categoría</PrimaryButton>
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
                        <div v-if="categories.length === 0" class="text-center text-gray-500">
                            No hay categorías. Creá una para asignar a los empleados.
                        </div>
                        <table v-else class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Nombre</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Empleados</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="cat in categories" :key="cat.id">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{{ cat.nombre }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">{{ cat.employees_count ?? 0 }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                        <Link :href="route('categories.edit', cat.id)" class="text-indigo-600 hover:text-indigo-900">Editar</Link>
                                        <button type="button" @click="destroy(cat.id)" class="ml-4 text-red-600 hover:text-red-900">Eliminar</button>
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
