<?php

namespace App\Exceptions;

use App\Providers\RouteServiceProvider;
use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (Exception $e, $request) {
            if (($e->getPrevious() instanceof TokenMismatchException) &&  str_contains($request->getRequestUri(), "/logout")) {
                return redirect()->route(RouteServiceProvider::HOME_ROUTE_NAME, ["language" => $request->language]);
            } else if (($e->getPrevious() instanceof TokenMismatchException) &&  str_contains($request->getRequestUri(), "/login")) {
                return redirect()->route(RouteServiceProvider::HOME_ROUTE_NAME, ["language" => $request->language]);
            }
        });
    }
}
