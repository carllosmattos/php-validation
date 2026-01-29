<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $clientId = $this->route('client');

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', Rule::unique('clients')->ignore($clientId)],
            'cpf' => ['sometimes', 'string', 'size:11', Rule::unique('clients')->ignore($clientId)],
            'phone' => ['sometimes', 'string', 'max:20'],
            'birth_date' => ['sometimes', 'date', 'before:today'],
            'password' => ['sometimes', Password::min(8)],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        // Remove campos vazios para não sobrescrever com null
        $this->merge(
            array_filter($this->all(), fn($value) => !is_null($value))
        );
    }
}
