<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (\Throwable $e) {
            if ($this->shouldReport($e) && app()->bound('sentry')) {
                app('sentry')->captureException($e);
            }
        });
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'data' => null,
                'message' => 'No autenticado',
                'status' => 401,
            ], 401);
        }

        return redirect()->guest(route('login'));
    }

    public function render($request, \Throwable $e)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            $status = 500;
            $message = 'Error interno del servidor';

            if ($e instanceof ValidationException) {
                return response()->json([
                    'data' => null,
                    'message' => 'Error de validación',
                    'errors' => $e->errors(),
                    'status' => 422,
                ], 422);
            }

            if ($e instanceof NotFoundHttpException) {
                $status = 404;
                $message = 'Recurso no encontrado';
            }

            if ($e instanceof HttpException) {
                $status = $e->getStatusCode();
                $message = $e->getMessage() ?: 'Error HTTP';
            }

            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                $status = 403;
                $message = 'No autorizado';
            }

            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                $status = 404;
                $message = 'Recurso no encontrado';
            }

            return response()->json([
                'data' => null,
                'message' => $message,
                'status' => $status,
            ], $status);
        }

        return parent::render($request, $e);
    }
}
