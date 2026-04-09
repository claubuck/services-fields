<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    employees: Object,
    establishments: Array,
    filters: Object,
});

const search = ref(props.filters?.search ?? '');
const establishmentId = ref(props.filters?.establishment_id ?? '');
const estado = ref(props.filters?.estado ?? '');

// ---------- selección de filas ----------
const selectionMode = ref(false);
/** @type {import('vue').Ref<number[]>} */
const selectedEmployeeIds = ref([]);

const allOnPageSelected = computed(() => {
    const ids = props.employees.data.map((e) => e.id);
    return ids.length > 0 && ids.every((id) => selectedEmployeeIds.value.includes(id));
});

const someOnPageSelected = computed(() =>
    props.employees.data.some((e) => selectedEmployeeIds.value.includes(e.id)),
);

const toggleSelectionMode = () => {
    selectionMode.value = !selectionMode.value;
    if (!selectionMode.value) {
        selectedEmployeeIds.value = [];
    }
};

const toggleSelectAll = () => {
    const ids = props.employees.data.map((e) => e.id);
    if (allOnPageSelected.value) {
        selectedEmployeeIds.value = selectedEmployeeIds.value.filter((id) => !ids.includes(id));
    } else {
        selectedEmployeeIds.value = [...new Set([...selectedEmployeeIds.value, ...ids])];
    }
};

const toggleEmployee = (id) => {
    if (selectedEmployeeIds.value.includes(id)) {
        selectedEmployeeIds.value = selectedEmployeeIds.value.filter((i) => i !== id);
    } else {
        selectedEmployeeIds.value = [...selectedEmployeeIds.value, id];
    }
};

// ---------- export ----------
const showExportModal = ref(false);
const exportFormat = ref('excel');
const excelTemplate = ref('normal');
/** @type {import('vue').Ref<string[]>} */
const selectedOptionalColumns = ref([]);

const optionalExportColumns = [
    { key: 'dni', label: 'DNI' },
    { key: 'establishment', label: 'Establecimiento' },
    { key: 'categoria', label: 'Categoría' },
    { key: 'modalidad', label: 'Modalidad' },
    { key: 'estado', label: 'Estado' },
    { key: 'fecha_inicio', label: 'Fecha inicio' },
    { key: 'direccion', label: 'Dirección' },
    { key: 'telefono', label: 'Teléfono' },
];

const showColumnSelection = computed(
    () => exportFormat.value === 'pdf' || (exportFormat.value === 'excel' && excelTemplate.value === 'normal'),
);

const openExportModal = () => {
    showExportModal.value = true;
};

const closeExportModal = () => {
    showExportModal.value = false;
};

const buildExportUrl = () => {
    const params = new URLSearchParams();
    params.set('format', exportFormat.value);

    if (exportFormat.value === 'excel') {
        params.set('template', excelTemplate.value);
    }

    if (search.value) {
        params.set('search', search.value);
    }

    if (establishmentId.value) {
        params.set('establishment_id', String(establishmentId.value));
    }

    if (estado.value) {
        params.set('estado', String(estado.value));
    }

    if (showColumnSelection.value) {
        const cols = ['nombre_apellido', 'cuil', ...selectedOptionalColumns.value];
        cols.forEach((c) => params.append('columns[]', c));
    }

    if (selectedEmployeeIds.value.length > 0) {
        selectedEmployeeIds.value.forEach((id) => params.append('employee_ids[]', id));
    }

    return `${route('employees.export')}?${params.toString()}`;
};

const runExport = () => {
    window.location.href = buildExportUrl();
    closeExportModal();
};

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
                <div class="flex shrink-0 flex-wrap items-center gap-2">
                    <button
                        type="button"
                        @click="toggleSelectionMode"
                        :class="[
                            'inline-flex items-center gap-1.5 rounded-md border px-3 py-2 text-xs font-semibold uppercase tracking-widest shadow-sm transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2',
                            selectionMode
                                ? 'border-indigo-600 bg-indigo-50 text-indigo-700 hover:bg-indigo-100'
                                : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50',
                        ]"
                    >
                        <svg v-if="selectionMode" xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        {{ selectionMode ? 'Cancelar selección' : 'Seleccionar' }}
                        <span
                            v-if="selectionMode && selectedEmployeeIds.length > 0"
                            class="ml-1 rounded-full bg-indigo-600 px-1.5 py-0.5 text-xs font-bold text-white"
                        >{{ selectedEmployeeIds.length }}</span>
                    </button>
                    <SecondaryButton type="button" @click="openExportModal">
                        Exportar
                    </SecondaryButton>
                    <Link :href="route('employees.create')">
                        <PrimaryButton>Nuevo empleado</PrimaryButton>
                    </Link>
                </div>
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
                                    <th v-if="selectionMode" class="w-10 px-4 py-3">
                                        <input
                                            type="checkbox"
                                            :checked="allOnPageSelected"
                                            :indeterminate="someOnPageSelected && !allOnPageSelected"
                                            @change="toggleSelectAll"
                                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                        />
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Nombre y apellido</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Establecimiento</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Categoría / Modalidad</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Estado</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Fecha inicio</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr
                                    v-for="employee in employees.data"
                                    :key="employee.id"
                                    :class="selectionMode && selectedEmployeeIds.includes(employee.id) ? 'bg-indigo-50' : ''"
                                >
                                    <td v-if="selectionMode" class="w-10 px-4 py-4">
                                        <input
                                            type="checkbox"
                                            :checked="selectedEmployeeIds.includes(employee.id)"
                                            @change="toggleEmployee(employee.id)"
                                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                        />
                                    </td>
                                    <td
                                        class="px-6 py-4"
                                        :class="selectionMode ? 'cursor-pointer' : ''"
                                        @click="selectionMode ? toggleEmployee(employee.id) : null"
                                    >
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

        <Modal :show="showExportModal" max-width="xl" @close="closeExportModal">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900">
                    Exportar empleados
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                    <template v-if="selectedEmployeeIds.length > 0">
                        Se exportarán los <strong>{{ selectedEmployeeIds.length }} empleados seleccionados</strong>.
                    </template>
                    <template v-else>
                        Se exportan todos los empleados según los filtros actuales (toda la lista, no solo esta página).
                    </template>
                </p>

                <div class="mt-6 space-y-4">
                    <div>
                        <span class="block text-sm font-medium text-gray-700">Formato</span>
                        <div class="mt-2 flex flex-wrap gap-4">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input
                                    v-model="exportFormat"
                                    type="radio"
                                    value="excel"
                                    class="border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                />
                                Excel
                            </label>
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input
                                    v-model="exportFormat"
                                    type="radio"
                                    value="pdf"
                                    class="border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                />
                                PDF
                            </label>
                        </div>
                    </div>

                    <div v-if="exportFormat === 'excel'">
                        <span class="block text-sm font-medium text-gray-700">Plantilla Excel</span>
                        <div class="mt-2 flex flex-col gap-2 sm:flex-row sm:gap-6">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input
                                    v-model="excelTemplate"
                                    type="radio"
                                    value="normal"
                                    class="border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                />
                                Normal (columnas seleccionables)
                            </label>
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input
                                    v-model="excelTemplate"
                                    type="radio"
                                    value="campos"
                                    class="border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                />
                                Campos con fechas
                            </label>
                        </div>
                        <p v-if="excelTemplate === 'campos'" class="mt-2 text-xs text-gray-500">
                            Incluye título, columnas de nombre y CUIL y columnas vacías para fechas (como el listado modelo).
                        </p>
                    </div>

                    <div v-if="showColumnSelection">
                        <span class="block text-sm font-medium text-gray-700">Columnas</span>
                        <p class="mt-1 text-xs text-gray-500">
                            Nombre y CUIL siempre se incluyen.
                        </p>
                        <div class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2">
                            <label class="flex items-center gap-2 text-sm text-gray-600">
                                <input type="checkbox" checked disabled class="rounded border-gray-300" />
                                Nombre y Apellido
                            </label>
                            <label class="flex items-center gap-2 text-sm text-gray-600">
                                <input type="checkbox" checked disabled class="rounded border-gray-300" />
                                CUIL
                            </label>
                            <label
                                v-for="col in optionalExportColumns"
                                :key="col.key"
                                class="flex cursor-pointer items-center gap-2 text-sm text-gray-700"
                            >
                                <input
                                    v-model="selectedOptionalColumns"
                                    type="checkbox"
                                    :value="col.key"
                                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                />
                                {{ col.label }}
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2 border-t border-gray-100 pt-4">
                    <SecondaryButton type="button" @click="closeExportModal">
                        Cancelar
                    </SecondaryButton>
                    <PrimaryButton type="button" @click="runExport">
                        Descargar
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
