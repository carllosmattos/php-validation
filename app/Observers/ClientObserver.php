<?php

namespace App\Observers;

use App\Models\Client;
use Illuminate\Support\Facades\Log;

class ClientObserver
{
    /**
     * Handle the Client "created" event.
     */
    public function created(Client $client): void
    {
        Log::channel('audit')->info('Cliente criado', [
            'client_id' => $client->id,
            'email' => $client->email,
            'cpf' => $client->cpf,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'authenticated_user' => auth()->id(),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Handle the Client "updated" event.
     */
    public function updated(Client $client): void
    {
        Log::channel('audit')->info('Cliente atualizado', [
            'client_id' => $client->id,
            'changes' => $client->getChanges(),
            'original' => $client->getOriginal(),
            'ip' => request()->ip(),
            'authenticated_user' => auth()->id(),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Handle the Client "deleted" event.
     */
    public function deleted(Client $client): void
    {
        Log::channel('audit')->warning('Cliente deletado', [
            'client_id' => $client->id,
            'email' => $client->email,
            'cpf' => $client->cpf,
            'ip' => request()->ip(),
            'authenticated_user' => auth()->id(),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Handle the Client "restored" event.
     */
    public function restored(Client $client): void
    {
        Log::channel('audit')->info('Cliente restaurado', [
            'client_id' => $client->id,
            'email' => $client->email,
            'ip' => request()->ip(),
            'authenticated_user' => auth()->id(),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Handle the Client "force deleted" event.
     */
    public function forceDeleted(Client $client): void
    {
        Log::channel('audit')->error('Cliente deletado permanentemente', [
            'client_id' => $client->id,
            'email' => $client->email,
            'cpf' => $client->cpf,
            'ip' => request()->ip(),
            'authenticated_user' => auth()->id(),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }
}
