<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Throwable;

class Handler extends ExceptionHandler
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

    public function unauthenticated($request, AuthenticationException $exception)
    {

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Tu sesión ha expirado o no estás autenticado.',
                'status' => 401,
                'error' => 'Unauthenticated',
                'suggestion' => 'Por favor vuelve a iniciar sesión para continuar.'
            ], 401);
        }


        return redirect()->guest(route('login'));
    }
}
