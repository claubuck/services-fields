<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    employer: Object,
});

const form = useForm({
    razon_social: props.employer?.razon_social ?? '',
    domicilio: props.employer?.domicilio ?? '',
    cuit: props.employer?.cuit ?? '',
});

const submit = () => {
    form.put(route('employer.update'));
};
</script>

<template>
    <Head title="Datos del empleador" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Datos del empleador
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div v-if="$page.props.flash?.success" class="mb-4 rounded-md bg-green-100 p-4 text-green-700">
                    {{ $page.props.flash.success }}
                </div>
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6">
                        <p class="mb-4 text-sm text-gray-600">
                            Información del empleador que figura en los recibos de liquidación.
                        </p>
                        <div>
                            <InputLabel for="razon_social" value="Razón social / Nombre" />
                            <TextInput
                                id="razon_social"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.razon_social"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.razon_social" />
                        </div>
                        <div class="mt-4">
                            <InputLabel for="domicilio" value="Domicilio" />
                            <TextInput
                                id="domicilio"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.domicilio"
                            />
                            <InputError class="mt-2" :message="form.errors.domicilio" />
                        </div>
                        <div class="mt-4">
                            <InputLabel for="cuit" value="CUIT" />
                            <TextInput
                                id="cuit"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.cuit"
                            />
                            <InputError class="mt-2" :message="form.errors.cuit" />
                        </div>
                        <div class="mt-6">
                            <PrimaryButton type="submit" :disabled="form.processing">Guardar</PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
