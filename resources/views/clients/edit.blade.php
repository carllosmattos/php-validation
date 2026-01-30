@extends('layouts.app')

@section('title', 'Editar Cliente')

@section('content')
<div class="min-h-screen bg-gray-50 py-8" x-data="editClient({{ $id }})">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8" x-init="
        if (!$store.auth.isAuthenticated()) {
            window.location.href = '/';
            return;
        }
        await init();
    ">
        <!-- Header -->
        <div class="mb-8">
            <a href="/dashboard" class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800 mb-4">
                <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Voltar para lista
            </a>
            <h1 class="text-3xl font-bold text-gray-900">Editar Cliente</h1>
        </div>

        <!-- Loading -->
        <div x-show="loadingData" class="bg-white rounded-lg shadow-sm p-12 text-center">
            <div class="flex justify-center items-center">
                <div class="spinner"></div>
                <span class="ml-3 text-gray-500">Carregando dados...</span>
            </div>
        </div>

        <!-- Form -->
        <div x-show="!loadingData" class="bg-white rounded-lg shadow-sm p-6">
            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nome -->
                    <div class="md:col-span-2">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                            Nome Completo <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="name"
                            x-model="form.name"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            :class="{ 'border-red-300 focus:ring-red-500': errors.name }"
                            required
                        >
                        <p x-show="errors.name" class="mt-1 text-sm text-red-600" x-text="errors.name?.[0]"></p>
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                            E-mail <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="email"
                            id="email"
                            x-model="form.email"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            :class="{ 'border-red-300 focus:ring-red-500': errors.email }"
                            required
                        >
                        <p x-show="errors.email" class="mt-1 text-sm text-red-600" x-text="errors.email?.[0]"></p>
                    </div>

                    <!-- CPF -->
                    <div>
                        <label for="cpf" class="block text-sm font-medium text-gray-700 mb-1">
                            CPF <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="cpf"
                            x-model="form.cpf"
                            @input="formatCPF()"
                            maxlength="14"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            :class="{ 'border-red-300 focus:ring-red-500': errors.cpf }"
                            placeholder="000.000.000-00"
                            required
                        >
                        <p x-show="errors.cpf" class="mt-1 text-sm text-red-600" x-text="errors.cpf?.[0]"></p>
                    </div>

                    <!-- Telefone -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                            Telefone
                        </label>
                        <input
                            type="text"
                            id="phone"
                            x-model="form.phone"
                            @input="formatPhone()"
                            maxlength="15"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            :class="{ 'border-red-300 focus:ring-red-500': errors.phone }"
                            placeholder="(00) 00000-0000"
                        >
                        <p x-show="errors.phone" class="mt-1 text-sm text-red-600" x-text="errors.phone?.[0]"></p>
                    </div>

                    <!-- Data de Nascimento -->
                    <div>
                        <label for="birth_date" class="block text-sm font-medium text-gray-700 mb-1">
                            Data de Nascimento
                        </label>
                        <input
                            type="date"
                            id="birth_date"
                            x-model="form.birth_date"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            :class="{ 'border-red-300 focus:ring-red-500': errors.birth_date }"
                        >
                        <p x-show="errors.birth_date" class="mt-1 text-sm text-red-600" x-text="errors.birth_date?.[0]"></p>
                    </div>


                    <!-- Status -->
                    <div class="md:col-span-2">
                        <div class="flex items-center">
                            <input
                                type="checkbox"
                                id="is_active"
                                x-model="form.is_active"
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                            >
                            <label for="is_active" class="ml-2 block text-sm text-gray-900">
                                Cliente Ativo
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end space-x-4 pt-6 border-t">
                    <a
                        href="/dashboard"
                        class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        Cancelar
                    </a>
                    <button
                        type="submit"
                        :disabled="loading"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span x-show="!loading">Salvar Alterações</span>
                        <span x-show="loading" class="flex items-center">
                            <div class="spinner mr-2"></div>
                            Salvando...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
