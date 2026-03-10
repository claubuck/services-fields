<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { watch, computed } from 'vue';

const SAC_CODIGO = '*00.100';
const SAC_CONCEPTO = '*00.100 SAC PROPORCIONAL 8,33%';
const SAC_PORCENTAJE = 8.33 / 100; // 8,33%

const props = defineProps({
    liquidation: Object,
    employees: Array,
    preselectedEmployeeId: Number,
});

const form = useForm({
    liquidation_id: props.liquidation.id,
    employee_id: props.preselectedEmployeeId || '',
    categoria: '',
    total_bruto: '0',
    total_retencion: '0',
    total_no_remunerativo: '0',
    neto_a_cobrar: '0',
    lines: [
        { codigo: '', concepto: '', cantidad: '', remuneracion: '0', retencion: '0', no_remunerativo: '0' },
    ],
});

const selectedEmployee = () => {
    const id = Number(form.employee_id);
    return props.employees.find((e) => e.id === id) ?? null;
};

const modalityForFirstLine = () => selectedEmployee()?.liquidation_modality ?? selectedEmployee()?.liquidationModality ?? null;

const isSacLine = (idx) => modalityForFirstLine() && form.lines[idx]?.codigo === SAC_CODIGO;

// Al elegir empleado: categoría, primer concepto con modalidad y segunda línea SAC si aplica
watch(
    () => form.employee_id,
    (employeeId) => {
        const emp = props.employees.find((e) => e.id === Number(employeeId));
        form.categoria = emp?.category?.nombre ?? '';
        const mod = emp?.liquidation_modality ?? emp?.liquidationModality;
        if (form.lines.length === 0) return;
        const first = form.lines[0];
        const concepto = mod?.nombre ?? '';
        const newFirst = {
            ...first,
            concepto,
            cantidad: mod ? '' : (first.cantidad ?? ''),
            remuneracion: mod ? '0' : (first.remuneracion ?? '0'),
        };
        const rest = form.lines.slice(1);
        if (mod) {
            const rem0 = parseFloat(String(newFirst.remuneracion).replace(',', '.')) || 0;
            const sacRem = (rem0 * SAC_PORCENTAJE).toFixed(2);
            const sacLine = { codigo: SAC_CODIGO, concepto: SAC_CONCEPTO, cantidad: '', remuneracion: sacRem, retencion: '0', no_remunerativo: '0' };
            if (rest.length > 0 && rest[0].codigo === SAC_CODIGO) {
                form.lines = [newFirst, { ...rest[0], ...sacLine, remuneracion: sacRem }, ...rest.slice(1)];
            } else {
                form.lines = [newFirst, sacLine, ...rest];
            }
        } else {
            form.lines = [newFirst, ...rest];
        }
    },
    { immediate: true }
);

// Primer concepto: remuneración = cantidad × precio por unidad
watch(
    () => form.lines[0]?.cantidad,
    (cantidad) => {
        const mod = modalityForFirstLine();
        if (!mod || !form.lines.length) return;
        const q = parseFloat(String(cantidad).replace(',', '.')) || 0;
        const precio = parseFloat(String(mod.precio_por_unidad).replace(',', '.')) || 0;
        form.lines[0].remuneracion = String((q * precio).toFixed(2));
    },
    { immediate: true }
);

// Segundo concepto: SAC PROPORCIONAL 8,33% sobre la remuneración del primer concepto
watch(
    () => form.lines[0]?.remuneracion,
    (rem0) => {
        if (!modalityForFirstLine() || form.lines.length < 2) return;
        const line1 = form.lines[1];
        if (line1?.codigo !== SAC_CODIGO) return;
        const base = parseFloat(String(rem0).replace(',', '.')) || 0;
        form.lines[1].remuneracion = String((base * SAC_PORCENTAJE).toFixed(2));
    },
    { immediate: true }
);

// Total bruto = suma de todas las columnas remuneración
const totalBrutoCalculado = computed(() => {
    const sum = form.lines.reduce((acc, line) => acc + (parseFloat(String(line.remuneracion).replace(',', '.')) || 0), 0);
    return sum.toFixed(2);
});
watch(
    () => totalBrutoCalculado.value,
    (val) => { form.total_bruto = val; },
    { immediate: true }
);

const addLine = () => {
    form.lines.push({ codigo: '', concepto: '', cantidad: '', remuneracion: '0', retencion: '0', no_remunerativo: '0' });
};

const removeLine = (index) => {
    if (form.lines.length > 1) form.lines.splice(index, 1);
};

const submit = () => {
    const mod = modalityForFirstLine();
    if (mod && form.lines.length > 0) {
        form.lines[0].concepto = mod.nombre ?? form.lines[0].concepto;
    }
    form.total_bruto = totalBrutoCalculado.value;
    form.post(route('payroll-receipts.store'));
};
</script>

<template>
    <Head title="Nuevo recibo" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('liquidations.show', liquidation.id)" class="text-gray-600 hover:text-gray-900">← Liquidación</Link>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Nuevo recibo
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6">
                        <div>
                            <InputLabel for="employee_id" value="Empleado" />
                            <select
                                id="employee_id"
                                v-model="form.employee_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            >
                                <option value="">Seleccionar empleado</option>
                                <option v-for="emp in employees" :key="emp.id" :value="emp.id">
                                    {{ emp.nombre_apellido }} - DNI {{ emp.dni }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.employee_id" />
                        </div>
                        <div class="mt-4">
                            <InputLabel value="Categoría" />
                            <p class="mt-1 rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700">
                                {{ form.employee_id ? (form.categoria || '—') : 'Seleccioná un empleado' }}
                            </p>
                        </div>

                        <div class="mt-6">
                            <div class="flex items-center justify-between">
                                <InputLabel value="Conceptos (líneas del recibo)" />
                                <button type="button" @click="addLine" class="text-sm text-indigo-600 hover:text-indigo-900">+ Agregar línea</button>
                            </div>
                            <p v-if="modalityForFirstLine()" class="mt-1 text-xs text-gray-500">
                                El primer concepto usa la modalidad del empleado: editá la cantidad y la remuneración se calcula automáticamente.
                            </p>
                            <div class="mt-2 overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 text-sm">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-2 py-2 text-left text-xs font-medium text-gray-500">Código</th>
                                            <th class="px-2 py-2 text-left text-xs font-medium text-gray-500">Concepto</th>
                                            <th class="px-2 py-2 text-right text-xs font-medium text-gray-500 w-20">Cant.</th>
                                            <th class="px-2 py-2 text-right text-xs font-medium text-gray-500">Remun.</th>
                                            <th class="px-2 py-2 text-right text-xs font-medium text-gray-500">Retención</th>
                                            <th class="px-2 py-2 text-right text-xs font-medium text-gray-500">No rem.</th>
                                            <th class="w-10"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        <tr v-for="(line, idx) in form.lines" :key="idx">
                                            <td class="px-2 py-1"><input v-model="line.codigo" type="text" class="w-20 rounded border-gray-300 text-sm" placeholder="*00.013" /></td>
                                            <td class="px-2 py-1">
                                                <input
                                                    v-if="!isSacLine(idx) && (idx !== 0 || !modalityForFirstLine())"
                                                    v-model="line.concepto"
                                                    type="text"
                                                    class="w-full min-w-[180px] rounded border-gray-300 text-sm"
                                                />
                                                <span v-else-if="isSacLine(idx)" class="block min-w-[180px] rounded border border-gray-200 bg-gray-50 px-2 py-1.5 text-sm text-gray-700">{{ SAC_CONCEPTO }}</span>
                                                <span v-else class="block min-w-[180px] rounded border border-gray-200 bg-gray-50 px-2 py-1.5 text-sm text-gray-700">{{ (modalityForFirstLine()?.nombre || line.concepto) || '—' }}</span>
                                            </td>
                                            <td class="px-2 py-1 text-right">
                                                <input
                                                    v-if="idx === 0 && modalityForFirstLine()"
                                                    v-model="line.cantidad"
                                                    type="number"
                                                    step="0.01"
                                                    min="0"
                                                    class="w-20 rounded border-gray-300 text-right text-sm"
                                                    placeholder="0"
                                                />
                                                <span v-else class="block w-20 text-right text-gray-400">—</span>
                                            </td>
                                            <td class="px-2 py-1">
                                                <input
                                                    v-if="!isSacLine(idx) && (idx !== 0 || !modalityForFirstLine())"
                                                    v-model="line.remuneracion"
                                                    type="number"
                                                    step="0.01"
                                                    min="0"
                                                    class="w-24 rounded border-gray-300 text-right text-sm"
                                                />
                                                <span v-else class="block w-24 rounded border border-gray-200 bg-gray-50 px-2 py-1.5 text-right text-sm text-gray-700">{{ line.remuneracion }}</span>
                                            </td>
                                            <td class="px-2 py-1"><input v-model="line.retencion" type="number" step="0.01" min="0" class="w-24 rounded border-gray-300 text-right text-sm" /></td>
                                            <td class="px-2 py-1"><input v-model="line.no_remunerativo" type="number" step="0.01" min="0" class="w-24 rounded border-gray-300 text-right text-sm" /></td>
                                            <td><button type="button" @click="removeLine(idx)" class="text-red-600 hover:text-red-900">×</button></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <InputError class="mt-2" :message="form.errors.lines" />
                        </div>

                        <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
                            <div>
                                <InputLabel value="Total bruto (suma de remuneraciones)" />
                                <p class="mt-1 rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-sm font-medium text-gray-700">{{ totalBrutoCalculado }}</p>
                                <InputError class="mt-2" :message="form.errors.total_bruto" />
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
                                <InputError class="mt-2" :message="form.errors.neto_a_cobrar" />
                            </div>
                        </div>

                        <div class="mt-6 flex gap-4">
                            <PrimaryButton type="submit" :disabled="form.processing">Guardar recibo</PrimaryButton>
                            <Link :href="route('liquidations.show', liquidation.id)" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancelar</Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
