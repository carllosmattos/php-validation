@extends('layouts.app')

@section('title', 'Dashboard - Clientes')

@section('content')
<div class="min-h-screen bg-gray-50"
    x-data="clientsManager()"
    x-init="
        console.log('Alpine dashboard x-init start');
        if (!$store.auth.isAuthenticated()) {
            console.log('Usuário não autenticado, redirecionando...');
            window.location.href = '/';
            return;
        }
        await $store.auth.fetchUser();
        await init();
        console.log('Alpine dashboard x-init end');
    "
    @keydown.escape.window="showDeleteModal = false; showFormModal = false"
>
    <!-- Header -->
    <nav class="bg-white shadow-sm sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <img
                            src="https://lirp.cdn-website.com/e8a01f29/dms3rep/multi/opt/02_Icone_Rosa_rgb-1920w.png"
                            alt="Logo"
                            class="h-12 w-12 object-contain"
                        />
                        <span class="ml-2 text-xl font-bold text-gray-900">Gerenciamento de Clientes</span>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-600" x-text="$store.auth.user?.name || $store.auth.user?.email"></span>
                    <button
                        @click="
                            $store.auth.clearToken();
                            window.location.href = '/';
                        "
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition"
                    >
                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Sair
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Filters & Actions -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
                    <input
                        type="text"
                        x-model="filters.name"
                        @input.debounce.500ms="fetchClients(1)"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm"
                        placeholder="Filtrar por nome..."
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
                    <input
                        type="text"
                        x-model="filters.email"
                        @input.debounce.500ms="fetchClients(1)"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm"
                        placeholder="Filtrar por e-mail..."
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CPF</label>
                    <input
                        type="text"
                        x-model="filters.cpf"
                        @input.debounce.500ms="fetchClients(1)"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm"
                        placeholder="000.000.000-00"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select
                        x-model="filters.is_active"
                        @change="fetchClients(1)"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm"
                    >
                        <option value="">Todos</option>
                        <option value="1">Ativos</option>
                        <option value="0">Inativos</option>
                    </select>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center space-x-4">
                    <button
                        @click="window.location.href = '/clients/create'"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition"
                    >
                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Novo Cliente
                    </button>

                    <button
                        x-show="selectedIds.length > 0"
                        @click="confirmDelete()"
                        class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition"
                    >
                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Deletar <span x-text="`(${selectedIds.length})`"></span>
                    </button>
                </div>

                <div class="flex items-center space-x-2">
                    <label class="text-sm text-gray-600">Por página:</label>
                    <select
                        x-model.number="filters.per_page"
                        @change="fetchClients(1)"
                        class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm"
                    >
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-12">
                                <input
                                    type="checkbox"
                                    @change="toggleSelectAll()"
                                    :checked="selectedIds.length === clients.length && clients.length > 0"
                                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                >
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" @click="toggleSort('name')">
                                <div class="flex items-center">
                                    Nome
                                    <svg x-show="filters.sort_by === 'name'" class="ml-1 h-4 w-4" :class="{ 'transform rotate-180': filters.sort_order === 'desc' }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                    </svg>
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" @click="toggleSort('email')">
                                <div class="flex items-center">
                                    E-mail
                                    <svg x-show="filters.sort_by === 'email'" class="ml-1 h-4 w-4" :class="{ 'transform rotate-180': filters.sort_order === 'desc' }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                    </svg>
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CPF</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Telefone</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" @click="toggleSort('created_at')">
                                <div class="flex items-center">
                                    Cadastro
                                    <svg x-show="filters.sort_by === 'created_at'" class="ml-1 h-4 w-4" :class="{ 'transform rotate-180': filters.sort_order === 'desc' }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                    </svg>
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <template x-if="loading">
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center">
                                    <div class="flex justify-center items-center">
                                        <div class="spinner"></div>
                                        <span class="ml-3 text-gray-500">Carregando...</span>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <template x-if="!loading && clients.length === 0">
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                    Nenhum cliente encontrado
                                </td>
                            </tr>
                        </template>

                        <template x-for="client in clients" :key="client.id">
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input
                                        type="checkbox"
                                        :value="client.id"
                                        x-model="selectedIds"
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                    >
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900" x-text="client.name"></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500" x-text="client.email"></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="client.cpf"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="client.phone || '-'"></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                        :class="client.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                        x-text="client.is_active ? 'Ativo' : 'Inativo'">
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="new Date(client.created_at).toLocaleDateString('pt-BR')"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                    <a
                                        :href="`/clients/${client.id}/edit`"
                                        class="text-indigo-600 hover:text-indigo-900 inline-flex items-center"
                                    >
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    <button
                                        @click="confirmDelete(client.id)"
                                        class="text-red-600 hover:text-red-900 inline-flex items-center"
                                    >
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                <div class="flex items-center justify-between">
                    <div class="flex-1 flex justify-between sm:hidden">
                        <button
                            @click="fetchClients(pagination.current_page - 1)"
                            :disabled="pagination.current_page === 1"
                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Anterior
                        </button>
                        <button
                            @click="fetchClients(pagination.current_page + 1)"
                            :disabled="pagination.current_page === pagination.last_page"
                            class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Próximo
                        </button>
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Mostrando
                                <span class="font-medium" x-text="((pagination.current_page - 1) * pagination.per_page) + 1"></span>
                                até
                                <span class="font-medium" x-text="Math.min(pagination.current_page * pagination.per_page, pagination.total)"></span>
                                de
                                <span class="font-medium" x-text="pagination.total"></span>
                                resultados
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                <button
                                    @click="fetchClients(pagination.current_page - 1)"
                                    :disabled="pagination.current_page === 1"
                                    class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <span class="sr-only">Anterior</span>
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </button>

                                <template x-for="page in Array.from({length: pagination.last_page}, (_, i) => i + 1).filter(p => p === 1 || p === pagination.last_page || Math.abs(p - pagination.current_page) <= 2)" :key="page">
                                    <button
                                        @click="fetchClients(page)"
                                        :class="page === pagination.current_page ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'"
                                        class="relative inline-flex items-center px-4 py-2 border text-sm font-medium"
                                        x-text="page"
                                    ></button>
                                </template>

                                <button
                                    @click="fetchClients(pagination.current_page + 1)"
                                    :disabled="pagination.current_page === pagination.last_page"
                                    class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <span class="sr-only">Próximo</span>
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div
        x-show="showDeleteModal"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed z-[9999] inset-0 flex items-center justify-center bg-gray-900 bg-opacity-70"
        style="z-index:9999;"
    >
        <div class="w-full max-w-md px-4">
            <div
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:p-6"
            >
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <!-- ...nenhum log ou debug... -->
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Confirmar Deleção
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                <span x-show="deleteTarget">Tem certeza que deseja deletar este cliente? Esta ação não pode ser desfeita.</span>
                                <span x-show="!deleteTarget && selectedIds.length > 0">Tem certeza que deseja deletar <strong x-text="selectedIds.length"></strong> cliente(s) selecionado(s)? Esta ação não pode ser desfeita.</span>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <button
                        @click="executeDelete()"
                        type="button"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
                    >
                        Deletar
                    </button>
                    <button
                        @click="showDeleteModal = false; deleteTarget = null"
                        type="button"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm"
                    >
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
