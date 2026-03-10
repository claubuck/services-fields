<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    receipt: Object,
});

const form = useForm({
    categoria: props.receipt.categoria ?? '',
    total_bruto: String(props.receipt.total_bruto ?? 0),
    total_retencion: String(props.receipt.total_retencion ?? 0),
    total_no_remunerativo: String(props.receipt.total_no_remunerativo ?? 0),
    neto_a_cobrar: String(props.receipt.neto_a_cobrar ?? 0),
    lines: (props.receipt.lines && props.receipt.lines.length)
        ? props.receipt.lines.map(l => ({
            id: l.id,
            codigo: l.codigo ?? '',
            concepto: l.concepto ?? '',
            remuneracion: String(l.remuneracion ?? 0),
            retencion: String(l.retencion ?? 0),
            no_remunerativo: String(l.no_remunerativo ?? 0),
        }))
        : [{ codigo: '', concepto: '', remuneracion: '0', retencion: '0', no_remunerativo: '0' }],
});

const addLine = () => {
    form.lines.push({ codigo: '', concepto: '', remuneracion: '0', retencion: '0', no_remunerativo: '0' });
};

const removeLine = (index) => {
    if (form.lines.length > 1) form.lines.splice(index, 1);
};

const submit = () => {
    form.put(route('payroll-receipts.update', props.receipt.id));
};
</script>

<template>
    <Head title="Editar recibo" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('liquidations.show', receipt.liquidation_id)" class="text-gray-600 hover:text-gray-900">← Liquidación</Link>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Editar recibo - {{ receipt.employee?.nombre_apellido }}
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6">
                        <div class="rounded-md bg-gray-50 p-3 text-sm text-gray-600">
                            Empleado: {{ receipt.employee?.nombre_apellido }} — DNI {{ receipt.employee?.dni }} — CUIL {{ receipt.employee?.cuil }}
                        </div>
                        <div class="mt-4">
                            <InputLabel for="categoria" value="Categoría" />
                            <TextInput id="categoria" type="text" class="mt-1 block w-full" v-model="form.categoria" />
                            <InputError class="mt-2" :message="form.errors.categoria" />
                        </div>

                        <div class="mt-6">
                            <div class="flex items-center justify-between">
                                <InputLabel value="Conceptos" />
                                <button type="button" @click="addLine" class="text-sm text-indigo-600 hover:text-indigo-900">+ Agregar línea</button>
                            </div>
                            <div class="mt-2 overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 text-sm">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-2 py-2 text-left text-xs font-medium text-gray-500">Código</th>
                                            <th class="px-2 py-2 text-left text-xs font-medium text-gray-500">Concepto</th>
                                            <th class="px-2 py-2 text-right text-xs font-medium text-gray-500">Remun.</th>
                                            <th class="px-2 py-2 text-right text-xs font-medium text-gray-500">Retención</th>
                                            <th class="px-2 py-2 text-right text-xs font-medium text-gray-500">No rem.</th>
                                            <th class="w-10"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        <tr v-for="(line, idx) in form.lines" :key="idx">
                                            <td class="px-2 py-1"><input v-model="line.codigo" type="text" class="w-20 rounded border-gray-300 text-sm" /></td>
                                            <td class="px-2 py-1"><input v-model="line.concepto" type="text" class="w-full min-w-[180px] rounded border-gray-300 text-sm" /></td>
                                            <td class="px-2 py-1"><input v-model="line.remuneracion" type="number" step="0.01" min="0" class="w-24 rounded border-gray-300 text-right text-sm" /></td>
                                            <td class="px-2 py-1"><input v-model="line.retencion" type="number" step="0.01" min="0" class="w-24 rounded border-gray-300 text-right text-sm" /></td>
                                            <td class="px-2 py-1"><input v-model="line.no_remunerativo" type="number" step="0.01" min="0" class="w-24 rounded border-gray-300 text-right text-sm" /></td>
                                            <td><button type="button" @click="removeLine(idx)" class="text-red-600 hover:text-red-900">×</button></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
                            <div>
                                <InputLabel value="Total bruto" />
                                <input v-model="form.total_bruto" type="number" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                            </div>
                            <div>
                                <InputLabel value="Total retención" />
                                <input v-model="form.total_retencion" type="number" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                            </div>
                            <div>
                                <InputLabel value="Total no remunerativo" />
                                <input v-model="form.total_no_remunerativo" type="number" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                            </div>
                            <div>
                                <InputLabel value="Neto a cobrar" />
                                <input v-model="form.neto_a_cobrar" type="number" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required />
                            </div>
                        </div>

                        <div class="mt-6 flex gap-4">
                            <PrimaryButton type="submit" :disabled="form.processing">Guardar</PrimaryButton>
                            <a :href="route('payroll-receipts.print', receipt.id)" target="_blank" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Imprimir</a>
                            <Link :href="route('liquidations.show', receipt.liquidation_id)" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancelar</Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
