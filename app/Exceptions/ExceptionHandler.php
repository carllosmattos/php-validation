<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class ExceptionHandler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->is('api/*')) {
            return response()->json([
                'message' => 'Unauthenticated.',
                'error' => 'Token inválido ou ausente'
            ], 401);
        }

        return redirect()->guest(route('login'));
    }

    public function render($request, Throwable $e)
    {
        if ($request->is('api/*')) {
            return $this->handleApiException($request, $e);
        }

        return parent::render($request, $e);
    }

    private function handleApiException($request, Throwable $exception): JsonResponse
    {
        if ($exception instanceof ValidationException) {
            return response()->json([
                'message' => 'Erro de validação',
                'errors' => $exception->errors()
            ], 422);
        }

        if ($exception instanceof NotFoundHttpException) {
            return response()->json([
                'message' => 'Recurso não encontrado'
            ], 404);
        }

        $statusCode = method_exists($exception, 'getStatusCode')
            ? $exception->getStatusCode()
            : 500;

        return response()->json([
            'message' => $exception->getMessage() ?: 'Erro no servidor'
        ], $statusCode);
    }
}
