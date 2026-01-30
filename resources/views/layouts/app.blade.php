<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Gerenciamento de Clientes')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script type="module" src="{{ Vite::asset('resources/js/app.js') }}" defer></script>
</head>
<body class="bg-gray-50 antialiased" x-data="notifications()" @notify.window="add($event.detail.message, $event.detail.type)">

    <!-- Notifications -->
    <div class="fixed top-4 right-4 z-50 space-y-2">
        <template x-for="item in items" :key="item.id">
            <div
                x-show="true"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-x-full"
                x-transition:enter-end="opacity-100 transform translate-x-0"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100 transform translate-x-0"
                x-transition:leave-end="opacity-0 transform translate-x-full"
                class="max-w-sm w-full shadow-lg rounded-lg pointer-events-auto overflow-hidden"
                :class="{
                    'bg-green-50 border border-green-200': item.type === 'success',
                    'bg-red-50 border border-red-200': item.type === 'error',
                    'bg-blue-50 border border-blue-200': item.type === 'info'
                }"
            >
                <div class="p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg x-show="item.type === 'success'" class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <svg x-show="item.type === 'error'" class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <svg x-show="item.type === 'info'" class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3 w-0 flex-1 pt-0.5">
                            <p class="text-sm font-medium" :class="{
                                'text-green-800': item.type === 'success',
                                'text-red-800': item.type === 'error',
                                'text-blue-800': item.type === 'info'
                            }" x-text="item.message"></p>
                        </div>
                        <div class="ml-4 flex-shrink-0 flex">
                            <button @click="remove(item.id)" class="rounded-md inline-flex focus:outline-none focus:ring-2"
                                :class="{
                                    'text-green-400 hover:text-green-500 focus:ring-green-500': item.type === 'success',
                                    'text-red-400 hover:text-red-500 focus:ring-red-500': item.type === 'error',
                                    'text-blue-400 hover:text-blue-500 focus:ring-blue-500': item.type === 'info'
                                }">
                                <span class="sr-only">Fechar</span>
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    @yield('content')
</body>
</html>
