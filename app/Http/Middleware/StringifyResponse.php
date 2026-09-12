<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StringifyResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only process JsonResponse
        if ($response instanceof \Illuminate\Http\JsonResponse) {
            $data = $response->getData(true); // Get data as array
            $data = $this->castToStringRecursive($data);
            $response->setData($data);
        }

        return $response;
    }

    /**
     * Recursively cast all values in an array or object to strings.
     * 
     * @param mixed $data
     * @return mixed
     */
    private function castToStringRecursive($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->castToStringRecursive($value);
            }
            return $data;
        }

        if (is_object($data)) {
            $data = (array) $data;
            foreach ($data as $key => $value) {
                $data[$key] = $this->castToStringRecursive($value);
            }
            return $data;
        }

        if (is_bool($data)) {
            return $data ? "true" : "false";
        }

        if (is_null($data)) {
            return "";
        }

        return (string) $data;
    }
}
