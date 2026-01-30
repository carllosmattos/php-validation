@extends('layouts.app')

@section('title', 'Login - Gerenciamento de Clientes')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-blue-50 to-indigo-100">
    <div class="max-w-md w-full space-y-8" x-data="{
        email: '',
        password: '',
        loading: false,
        errors: {},

        async login() {
            this.loading = true;
            this.errors = {};

            try {
                const response = await fetch('/api/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        email: this.email,
                        password: this.password
                    })
                });

                const data = await response.json();

                if (response.ok) {
                    const token = data.data.token;
                    this.$store.auth.setToken(token);
                    await this.$store.auth.fetchUser();
                    window.location.href = '/dashboard';
                } else {
                    if (data.errors) {
                        this.errors = data.errors;
                    } else {
                        this.$dispatch('notify', {
                            message: data.message || 'Credenciais inválidas',
                            type: 'error'
                        });
                    }
                }
            } catch (error) {
                console.error('Login error:', error);
                this.$dispatch('notify', {
                    message: 'Erro ao fazer login. Tente novamente.',
                    type: 'error'
                });
            } finally {
                this.loading = false;
            }
        }
    }">
        <div>
            <div class="mx-auto h-16 w-16 flex items-center justify-center rounded-full bg-white shadow-md">
                <img
                    src="https://lirp.cdn-website.com/e8a01f29/dms3rep/multi/opt/02_Icone_Rosa_rgb-1920w.png"
                    alt="Logo"
                    class="h-16 w-16 object-contain"
                />
            </div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Gerenciamento de Clientes
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Faça login para acessar o sistema
            </p>
        </div>

        <form class="mt-8 space-y-6 bg-white p-8 rounded-xl shadow-lg" @submit.prevent="login">
            <div class="rounded-md shadow-sm space-y-4">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                        E-mail
                    </label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        autocomplete="email"
                        required
                        x-model="email"
                        class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm transition"
                        :class="{ 'border-red-300 focus:ring-red-500 focus:border-red-500': errors.email }"
                        placeholder="seu@email.com"
                    >
                    <p x-show="errors.email" class="mt-1 text-sm text-red-600" x-text="errors.email?.[0]"></p>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                        Senha
                    </label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="current-password"
                        required
                        x-model="password"
                        class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm transition"
                        :class="{ 'border-red-300 focus:ring-red-500 focus:border-red-500': errors.password }"
                        placeholder="••••••••"
                    >
                    <p x-show="errors.password" class="mt-1 text-sm text-red-600" x-text="errors.password?.[0]"></p>
                </div>
            </div>

            <div>
                <button
                    type="submit"
                    :disabled="loading"
                    class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
                >
                    <span x-show="!loading">Entrar</span>
                    <span x-show="loading" class="flex items-center">
                        <div class="spinner mr-2"></div>
                        Entrando...
                    </span>
                </button>
            </div>
        </form>

        <div class="text-center text-sm text-gray-600">
            <p>Conta de teste:</p>
            <p class="font-mono text-xs mt-1">email: qualquer cliente cadastrado</p>
            <p class="font-mono text-xs">senha: password</p>
        </div>
    </div>
</div>
@endsection
