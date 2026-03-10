<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    liquidation: Object,
    employees: Array,
});

const meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
const periodoLabel = () => `${meses[props.liquidation.mes]} ${props.liquidation.anio}`;

const destroyRecibo = (id) => {
    if (confirm('¿Eliminar este recibo?')) {
        router.delete(route('payroll-receipts.destroy', id));
    }
};
</script>

<template>
    <Head :title="`Liquidación ${periodoLabel()}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('liquidations.index')" class="text-gray-600 hover:text-gray-900">← Liquidaciones</Link>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">
                        Liquidación {{ periodoLabel() }}
                    </h2>
                </div>
                <Link :href="route('payroll-receipts.create', liquidation.id)">
                    <PrimaryButton>Nuevo recibo</PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div v-if="$page.props.flash?.success" class="mb-4 rounded-md bg-green-100 p-4 text-green-700">
                    {{ $page.props.flash.success }}
                </div>
                <p class="mb-4 text-sm text-gray-600">
                    Fecha de pago en recibos: {{ liquidation.fecha_pago ? new Date(liquidation.fecha_pago).toLocaleDateString('es-AR') : '—' }}
                </p>
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div v-if="!liquidation.payroll_receipts || liquidation.payroll_receipts.length === 0" class="text-center text-gray-500">
                            No hay recibos en esta liquidación. Agregá uno con "Nuevo recibo".
                        </div>
                        <table v-else class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Empleado</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Categoría</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Neto a cobrar</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="rec in liquidation.payroll_receipts" :key="rec.id">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{{ rec.employee?.nombre_apellido }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">{{ rec.categoria || '—' }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">$ {{ Number(rec.neto_a_cobrar).toLocaleString('es-AR') }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                        <a :href="route('payroll-receipts.print', rec.id)" target="_blank" class="text-indigo-600 hover:text-indigo-900">Imprimir</a>
                                        <Link :href="route('payroll-receipts.edit', rec.id)" class="ml-4 text-indigo-600 hover:text-indigo-900">Editar</Link>
                                        <button type="button" @click="destroyRecibo(rec.id)" class="ml-4 text-red-600 hover:text-red-900">Eliminar</button>
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
