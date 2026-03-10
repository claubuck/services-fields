<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    liquidations: Array,
});

const meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

const mesNombre = (mes) => meses[mes] || mes;

const destroy = (id) => {
    if (confirm('¿Eliminar esta liquidación y todos sus recibos?')) {
        router.delete(route('liquidations.destroy', id));
    }
};
</script>

<template>
    <Head title="Liquidaciones" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Liquidaciones mensuales
                </h2>
                <Link :href="route('liquidations.create')">
                    <PrimaryButton>Nueva liquidación</PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div v-if="$page.props.flash?.success" class="mb-4 rounded-md bg-green-100 p-4 text-green-700">
                    {{ $page.props.flash.success }}
                </div>
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div v-if="liquidations.length === 0" class="text-center text-gray-500">
                            No hay liquidaciones. Creá una para un período (mes/año).
                        </div>
                        <table v-else class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Período</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Fecha de pago</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Recibos</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="liq in liquidations" :key="liq.id">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{{ mesNombre(liq.mes) }} {{ liq.anio }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">{{ liq.fecha_pago ? new Date(liq.fecha_pago).toLocaleDateString('es-AR') : '—' }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">{{ liq.payroll_receipts_count ?? 0 }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                        <Link :href="route('liquidations.show', liq.id)" class="text-indigo-600 hover:text-indigo-900">Ver / Recibos</Link>
                                        <button type="button" @click="destroy(liq.id)" class="ml-4 text-red-600 hover:text-red-900">Eliminar</button>
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
