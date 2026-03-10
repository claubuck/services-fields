<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    employee: Object,
    establishments: Array,
    categories: Array,
    modalidades: Array,
});

const formatDate = (value) => {
    if (!value) return '';
    const d = new Date(value);
    return d.toISOString().slice(0, 10);
};

const form = useForm({
    establishment_id: props.employee.establishment_id,
    category_id: props.employee.category_id ?? '',
    liquidation_modality_id: props.employee.liquidation_modality_id ?? '',
    nombre_apellido: props.employee.nombre_apellido,
    cuil: props.employee.cuil,
    dni: props.employee.dni,
    direccion: props.employee.direccion ?? '',
    telefono: props.employee.telefono ?? '',
    estado: props.employee.estado ?? 'activo',
    fecha_inicio: formatDate(props.employee.fecha_inicio),
});

const submit = () => {
    form.put(route('employees.update', props.employee.id));
};
</script>

<template>
    <Head title="Editar empleado" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('employees.index')" class="text-gray-600 hover:text-gray-900">← Empleados</Link>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Editar empleado
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6">
                        <div>
                            <InputLabel for="establishment_id" value="Establecimiento" />
                            <select
                                id="establishment_id"
                                v-model="form.establishment_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            >
                                <option value="">Seleccionar establecimiento</option>
                                <option v-for="est in establishments" :key="est.id" :value="est.id">
                                    {{ est.nombre }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.establishment_id" />
                        </div>

                        <div class="mt-4">
                            <InputLabel for="category_id" value="Categoría" />
                            <select
                                id="category_id"
                                v-model="form.category_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">Sin categoría</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                    {{ cat.nombre }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.category_id" />
                        </div>

                        <div class="mt-4">
                            <InputLabel for="liquidation_modality_id" value="Modalidad de liquidación" />
                            <select
                                id="liquidation_modality_id"
                                v-model="form.liquidation_modality_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">Sin modalidad</option>
                                <option v-for="mod in modalidades" :key="mod.id" :value="mod.id">
                                    {{ mod.nombre }} ($ {{ Number(mod.precio_por_unidad).toLocaleString('es-AR', { minimumFractionDigits: 2 }) }})
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.liquidation_modality_id" />
                        </div>

                        <div class="mt-4">
                            <InputLabel for="nombre_apellido" value="Nombre y apellido" />
                            <TextInput
                                id="nombre_apellido"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.nombre_apellido"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.nombre_apellido" />
                        </div>

                        <div class="mt-4">
                            <InputLabel for="cuil" value="CUIL" />
                            <TextInput
                                id="cuil"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.cuil"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.cuil" />
                        </div>

                        <div class="mt-4">
                            <InputLabel for="dni" value="DNI" />
                            <TextInput
                                id="dni"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.dni"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.dni" />
                        </div>

                        <div class="mt-4">
                            <InputLabel for="estado" value="Estado" />
                            <select
                                id="estado"
                                v-model="form.estado"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            >
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                                <option value="suspendido">Suspendido</option>
                                <option value="pendiente_de_baja">Pendiente de baja</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.estado" />
                        </div>

                        <div class="mt-4">
                            <InputLabel for="fecha_inicio" value="Fecha de inicio (relación laboral)" />
                            <input
                                id="fecha_inicio"
                                v-model="form.fecha_inicio"
                                type="date"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.fecha_inicio" />
                        </div>

                        <div class="mt-4">
                            <InputLabel for="direccion" value="Dirección (opcional)" />
                            <TextInput
                                id="direccion"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.direccion"
                            />
                            <InputError class="mt-2" :message="form.errors.direccion" />
                        </div>

                        <div class="mt-4">
                            <InputLabel for="telefono" value="Teléfono (opcional)" />
                            <TextInput
                                id="telefono"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.telefono"
                            />
                            <InputError class="mt-2" :message="form.errors.telefono" />
                        </div>

                        <div class="mt-6 flex gap-4">
                            <PrimaryButton type="submit" :disabled="form.processing">Guardar</PrimaryButton>
                            <Link :href="route('employees.index')" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancelar</Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
