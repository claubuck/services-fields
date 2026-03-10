<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    mes: new Date().getMonth() + 1,
    anio: new Date().getFullYear(),
    fecha_pago: '',
});

const submit = () => {
    form.post(route('liquidations.store'));
};

const meses = [
    { value: 1, label: 'Enero' }, { value: 2, label: 'Febrero' }, { value: 3, label: 'Marzo' },
    { value: 4, label: 'Abril' }, { value: 5, label: 'Mayo' }, { value: 6, label: 'Junio' },
    { value: 7, label: 'Julio' }, { value: 8, label: 'Agosto' }, { value: 9, label: 'Septiembre' },
    { value: 10, label: 'Octubre' }, { value: 11, label: 'Noviembre' }, { value: 12, label: 'Diciembre' },
];
</script>

<template>
    <Head title="Nueva liquidación" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('liquidations.index')" class="text-gray-600 hover:text-gray-900">← Liquidaciones</Link>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Nueva liquidación mensual
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6">
                        <div>
                            <InputLabel for="mes" value="Mes" />
                            <select
                                id="mes"
                                v-model.number="form.mes"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            >
                                <option v-for="m in meses" :key="m.value" :value="m.value">{{ m.label }}</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.mes" />
                        </div>
                        <div class="mt-4">
                            <InputLabel for="anio" value="Año" />
                            <input
                                id="anio"
                                v-model.number="form.anio"
                                type="number"
                                min="2020"
                                max="2100"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.anio" />
                        </div>
                        <div class="mt-4">
                            <InputLabel for="fecha_pago" value="Fecha de pago (para el recibo)" />
                            <input
                                id="fecha_pago"
                                v-model="form.fecha_pago"
                                type="date"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.fecha_pago" />
                        </div>
                        <div class="mt-6 flex gap-4">
                            <PrimaryButton type="submit" :disabled="form.processing">Crear liquidación</PrimaryButton>
                            <Link :href="route('liquidations.index')" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancelar</Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
