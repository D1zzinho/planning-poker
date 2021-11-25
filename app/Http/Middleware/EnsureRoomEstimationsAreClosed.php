<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;

class EnsureRoomEstimationsAreClosed
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     * @throws Exception
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $filteredEstimations = $request->game->estimations->filter(
            fn($value, $key) => in_array($value->status, ['open', 'finished'])
        );

        if ($filteredEstimations->count() > 0) {
            abort(409,'You have to close all estimations before closing the whole game');
        }

        return $next($request);
    }
}
