<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;

class SetLanguage
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // apply the language fromt the url to the requests
        if (!in_array($request->language, ['en', 'nl'])) {
            $request->language = config('app.fallback_locale');
        }

        App::setLocale($request->language);
        URL::defaults(['language' => $request->language]);

        return $next($request);
    }
}
