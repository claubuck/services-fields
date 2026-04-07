<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    employees: Object,
    establishments: Array,
    filters: Object,
});

const search = ref(props.filters?.search ?? '');
const establishmentId = ref(props.filters?.establishment_id ?? '');
const estado = ref(props.filters?.estado ?? '');

const applyFilters = () => {
    router.get(route('employees.index'), {
        search: search.value || undefined,
        establishment_id: establishmentId.value || undefined,
        estado: estado.value || undefined,
    }, { preserveScroll: false });
};

const clearFilters = () => {
    search.value = '';
    establishmentId.value = '';
    estado.value = '';
    router.get(route('employees.index'));
};

const destroy = (id) => {
    if (confirm('¿Eliminar este empleado?')) {
        router.delete(route('employees.destroy', id));
    }
};

const estadoLabel = (e) => {
    const labels = { activo: 'Activo', inactivo: 'Inactivo', suspendido: 'Suspendido', pendiente_de_baja: 'Pendiente de baja' };
    return labels[e] ?? e;
};

const estadoClass = (e) => {
    const classes = {
        activo: 'bg-green-100 text-green-800',
        inactivo: 'bg-gray-100 text-gray-800',
        suspendido: 'bg-amber-100 text-amber-800',
        pendiente_de_baja: 'bg-red-100 text-red-800',
    };
    return classes[e] ?? '';
};
</script>

<template>
    <Head title="Empleados" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <h2 class="min-w-0 truncate text-xl font-semibold leading-tight text-gray-800">
                    Empleados
                </h2>
                <Link :href="route('employees.create')" class="shrink-0">
                    <PrimaryButton>Nuevo empleado</PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="mx-auto max-w-7xl sm:px-0">
                <div v-if="$page.props.flash?.success" class="mb-4 rounded-md bg-green-100 p-4 text-green-700">
                    {{ $page.props.flash.success }}
                </div>

                <!-- Filtros -->
                <div class="mb-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <h3 class="mb-3 text-sm font-medium text-gray-700">Filtros</h3>
                    <div class="flex flex-wrap items-end gap-4">
                        <div class="min-w-0 flex-1">
                            <label for="filter-search" class="block text-xs font-medium text-gray-500">Buscar (nombre, DNI, CUIL)</label>
                            <input
                                id="filter-search"
                                v-model="search"
                                type="search"
                                placeholder="Buscar..."
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                @keyup.enter="applyFilters"
                            />
                        </div>
                        <div class="w-48">
                            <label for="filter-establishment" class="block text-xs font-medium text-gray-500">Establecimiento</label>
                            <select
                                id="filter-establishment"
                                v-model="establishmentId"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                @change="applyFilters"
                            >
                                <option value="">Todos</option>
                                <option v-for="est in establishments" :key="est.id" :value="String(est.id)">
                                    {{ est.nombre }}
                                </option>
                            </select>
                        </div>
                        <div class="w-44">
                            <label for="filter-estado" class="block text-xs font-medium text-gray-500">Estado</label>
                            <select
                                id="filter-estado"
                                v-model="estado"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                @change="applyFilters"
                            >
                                <option value="">Todos</option>
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                                <option value="suspendido">Suspendido</option>
                                <option value="pendiente_de_baja">Pendiente de baja</option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button
                                type="button"
                                @click="applyFilters"
                                class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                            >
                                Filtrar
                            </button>
                            <button
                                type="button"
                                @click="clearFilters"
                                class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50"
                            >
                                Limpiar
                            </button>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto p-6">
                        <div v-if="employees.data.length === 0" class="text-center text-gray-500">
                            No hay empleados que coincidan con los filtros. Creá uno o ajustá los filtros.
                        </div>
                        <table v-else class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Nombre y apellido</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Establecimiento</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Categoría / Modalidad</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Estado</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Fecha inicio</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="employee in employees.data" :key="employee.id">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ employee.nombre_apellido }}</div>
                                        <div class="text-xs text-gray-500">{{ employee.cuil }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">{{ employee.establishment?.nombre }}</td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-600">{{ employee.category?.nombre ?? '—' }}</div>
                                        <div class="text-xs text-gray-500">{{ employee.liquidation_modality?.nombre ?? '—' }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span :class="['inline-flex rounded-full px-2 py-1 text-xs font-medium', estadoClass(employee.estado)]">
                                            {{ estadoLabel(employee.estado) }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">{{ employee.fecha_inicio ? new Date(employee.fecha_inicio).toLocaleDateString('es-AR') : '—' }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                        <Link :href="route('employees.edit', employee.id)" class="text-indigo-600 hover:text-indigo-900">Editar</Link>
                                        <button type="button" @click="destroy(employee.id)" class="ml-4 text-red-600 hover:text-red-900">Eliminar</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div v-if="employees.last_page > 1" class="flex items-center justify-between border-t border-gray-200 px-6 py-4">
                        <p class="text-sm text-gray-600">
                            Mostrando {{ employees.from }}–{{ employees.to }} de {{ employees.total }} empleados
                        </p>
                        <div class="flex gap-1">
                            <template v-for="link in employees.links" :key="link.label">
                                <span
                                    v-if="link.url === null"
                                    class="inline-flex items-center rounded-md px-3 py-1.5 text-sm text-gray-400"
                                    v-html="link.label"
                                />
                                <Link
                                    v-else
                                    :href="link.url"
                                    class="inline-flex items-center rounded-md px-3 py-1.5 text-sm"
                                    :class="link.active
                                        ? 'bg-indigo-600 font-semibold text-white'
                                        : 'border border-gray-300 text-gray-700 hover:bg-gray-50'"
                                    v-html="link.label"
                                />
                            </template>
                        </div>
                    </div>
                </div>
            </div>
    </AuthenticatedLayout>
</template>
