<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {})

    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (NotFoundHttpException $exception, Request $request) {
            if ($request->is('api/*')) {
                if ($exception instanceof ValidationException) {
                    return $this->convertValidationExceptionToResponse($exception, $request);
                }

                if ($exception instanceof ModelNotFoundException) {
                    $modelo = strtolower(class_basename($exception->getModel()));
                    return $this->errorResponse("No existe ninguna instancia de {$modelo} con el id especificado", 404);
                }

                if ($exception instanceof AuthenticationException) {
                    return $this->unauthenticated($request, $exception);
                }

                if ($exception instanceof AuthorizationException) {
                    return $this->errorResponse('No posee permisos para ejecutar esta acción', 403);
                }

                if ($exception instanceof NotFoundHttpException) {
                    return $this->errorResponse('No se encontró la URL especificada', 404);
                }

                if ($exception instanceof MethodNotAllowedHttpException) {
                    return $this->errorResponse('El método especificado en la petición no es válido', 405);
                }

                if ($exception instanceof HttpException) {
                    return $this->errorResponse($exception->getMessage(), $exception->getStatusCode());
                }

                if ($exception instanceof QueryException) {
                    $code = $exception->errorInfo[1];
                    if ($code == 1451) {
                        return $this->errorResponse('No se puede eliminar de forma permanente el recurso porque está relacionado con algún otro.', 409);
                    } else {
                        return $this->errorResponse($exception->getMessage(), 409);
                    }
                }

                if (config('app.debug')) {
                    return parent::render($request, $exception);
                }

                return $this->errorResponse('Falla inesperada. Intente luego', 500);
            }
        });
    })->create();
