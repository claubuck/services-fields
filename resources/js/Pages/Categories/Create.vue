<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    nombre: '',
});

const submit = () => {
    form.post(route('categories.store'));
};
</script>

<template>
    <Head title="Nueva categoría" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('categories.index')" class="text-gray-600 hover:text-gray-900">← Categorías</Link>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Nueva categoría
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6">
                        <div>
                            <InputLabel for="nombre" value="Nombre de la categoría" />
                            <TextInput
                                id="nombre"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.nombre"
                                required
                                autofocus
                                placeholder="Ej: COSECHADOR, OPERARIO"
                            />
                            <InputError class="mt-2" :message="form.errors.nombre" />
                        </div>
                        <div class="mt-6 flex gap-4">
                            <PrimaryButton type="submit" :disabled="form.processing">Guardar</PrimaryButton>
                            <Link :href="route('categories.index')" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancelar</Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
