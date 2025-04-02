<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class ApiVersion
{
    const APP_VERSION = 'app.version';

    public function handle(Request $request, Closure $next, ?string $version = null)
    {
        Config::set(self::APP_VERSION, $version);

        return $next($request);
    }

    public static function getVersion() {
        return Config::get(self::APP_VERSION);
    }
}
