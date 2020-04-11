<?php

namespace PrettyRoutes;

use Route;
use Closure;

class PrettyRoutesController
{
    /**
     * Show pretty routes.
     *
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        $middlewareClosure = function ($middleware) {
            return $middleware instanceof Closure ? 'Closure' : $middleware;
        };

        $routes = collect(Route::getRoutes());

        foreach (config('pretty-routes.hide_matching') as $regex) {
            $routes = $routes->filter(function ($value, $key) use ($regex) {
                return ! preg_match($regex, $value->uri());
            });
        }

        return view('pretty-routes::routes', [
            'routes'            => $this->sortRoutes($routes),
            'middlewareClosure' => $middlewareClosure,
        ]);
    }

    private function sortRoutes($routes)
    {
        if (null == ($sort = config('pretty-routes.sorting'))) {
            return $routes;
        }
        if (! is_callable($sort)) {
            return $routes;
        }

        return call_user_func(config('pretty-routes.sorting'), $routes);
    }
}
