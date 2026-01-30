@extends('layouts.app')

@section('title', 'Registre-se')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-blue-50 to-indigo-100">
    <div class="max-w-md w-full space-y-8">
        <div>
            <div class="mx-auto h-16 w-16 flex items-center justify-center rounded-full bg-white shadow-md">
                <img
                    src="https://lirp.cdn-website.com/e8a01f29/dms3rep/multi/opt/02_Icone_Rosa_rgb-1920w.png"
                    alt="Logo"
                    class="h-16 w-16 object-contain"
                />
            </div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Crie sua conta
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Preencha os dados para se registrar
            </p>
        </div>
        <form id="registerForm" class="mt-8 space-y-6 bg-white p-8 rounded-xl shadow-lg">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nome Completo <span class="text-red-500">*</span></label>
                <input type="text" id="name" name="name" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                <p class="mt-1 text-sm text-red-600" id="error-name"></p>
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">E-mail <span class="text-red-500">*</span></label>
                <input type="email" id="email" name="email" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                <p class="mt-1 text-sm text-red-600" id="error-email"></p>
            </div>
            <div>
                <label for="cpf" class="block text-sm font-medium text-gray-700 mb-1">CPF <span class="text-red-500">*</span></label>
                <input type="text" id="cpf" name="cpf" maxlength="14" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="000.000.000-00" required>
                <p class="mt-1 text-sm text-red-600" id="error-cpf"></p>
            </div>
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                <input type="text" id="phone" name="phone" maxlength="15" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="(00) 00000-0000">
                <p class="mt-1 text-sm text-red-600" id="error-phone"></p>
            </div>
            <div>
                <label for="birth_date" class="block text-sm font-medium text-gray-700 mb-1">Data de Nascimento</label>
                <input type="date" id="birth_date" name="birth_date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <p class="mt-1 text-sm text-red-600" id="error-birth_date"></p>
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Senha <span class="text-red-500">*</span></label>
                <input type="password" id="password" name="password" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                <p class="mt-1 text-sm text-red-600" id="error-password"></p>
            </div>
            <input type="hidden" id="recaptcha_token" name="recaptcha_token">
            <div class="flex gap-2">
                <button type="button" onclick="window.location.href='/'" class="w-1/2 py-2 px-4 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Voltar</button>
                <button type="submit" class="w-1/2 py-2 px-4 bg-indigo-600 text-white rounded-md font-semibold hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">Registrar</button>
            </div>
        </form>
        <div id="register-success" class="hidden mt-4 text-green-600 text-center font-semibold"></div>
    </div>
</div>
<script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.key') }}"></script>
<script>
    function formatCPF(value) {
        value = value.replace(/\D/g, '');
        if (value.length <= 11) {
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        }
        return value;
    }
    function formatPhone(value) {
        value = value.replace(/\D/g, '');
        if (value.length <= 11) {
            value = value.replace(/^(\d{2})(\d)/g, '($1) $2');
            value = value.replace(/(\d)(\d{4})$/, '$1-$2');
        }
        return value;
    }
    document.getElementById('cpf').addEventListener('input', function(e) {
        e.target.value = formatCPF(e.target.value);
    });
    document.getElementById('phone').addEventListener('input', function(e) {
        e.target.value = formatPhone(e.target.value);
    });
    document.getElementById('registerForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        document.getElementById('register-success').classList.add('hidden');
        Array.from(document.querySelectorAll('[id^=error-]')).forEach(el => el.textContent = '');
        const btn = document.querySelector('#registerForm button[type="submit"]');
        btn.disabled = true;
        btn.classList.add('opacity-50', 'cursor-not-allowed');
        grecaptcha.ready(async function() {
            const token = await grecaptcha.execute('{{ config('services.recaptcha.key') }}', {action: 'register'});
            document.getElementById('recaptcha_token').value = token;
            const formData = Object.fromEntries(new FormData(e.target));
            // Remove máscara do CPF antes de enviar
            if (formData.cpf) {
                formData.cpf = formData.cpf.replace(/\D/g, '');
            }
            fetch('/api/public-register', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(formData)
            })
            .then(async res => {
                let data = {};
                try {
                    data = await res.json();
                } catch (e) {
                    data = {};
                }
                if (res.status === 201 || res.status === 200) {
                    document.getElementById('register-success').textContent = 'Registro criado com sucesso! Redirecionando...';
                    document.getElementById('register-success').classList.remove('hidden');
                    document.getElementById('register-success').classList.remove('text-red-600');
                    document.getElementById('register-success').classList.add('text-green-600');
                    setTimeout(() => { window.location.href = '/'; }, 1500);
                } else if (data.errors) {
                    Object.entries(data.errors).forEach(([k, v]) => {
                        const el = document.getElementById('error-' + k);
                        if (el) el.textContent = v[0];
                    });
                    document.getElementById('register-success').textContent = 'Erro ao registrar. Verifique os campos destacados.';
                    document.getElementById('register-success').classList.remove('hidden');
                    document.getElementById('register-success').classList.remove('text-green-600');
                    document.getElementById('register-success').classList.add('text-red-600');
                    btn.disabled = false;
                    btn.classList.remove('opacity-50', 'cursor-not-allowed');
                } else {
                    document.getElementById('register-success').textContent = data.message || 'Erro ao registrar.';
                    document.getElementById('register-success').classList.remove('hidden');
                    document.getElementById('register-success').classList.remove('text-green-600');
                    document.getElementById('register-success').classList.add('text-red-600');
                    btn.disabled = false;
                    btn.classList.remove('opacity-50', 'cursor-not-allowed');
                }
            })
            .catch((err) => {
                document.getElementById('register-success').textContent = 'Erro de rede ou servidor. Tente novamente.';
                document.getElementById('register-success').classList.remove('hidden');
                document.getElementById('register-success').classList.remove('text-green-600');
                document.getElementById('register-success').classList.add('text-red-600');
                btn.disabled = false;
                btn.classList.remove('opacity-50', 'cursor-not-allowed');
            });
        });
    });
</script>
@endsection
