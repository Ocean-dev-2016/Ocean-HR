<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleRawData
{
    /**
     * Handle an incoming request.
     * Parse raw request body and force JSON response
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Force JSON content type for request
        if ($request->isMethod('post') || $request->isMethod('put') || $request->isMethod('patch')) {
            $contentType = $request->header('Content-Type');
            
            // If raw data is sent, parse it
            if (strpos($contentType, 'application/json') !== false || empty($contentType)) {
                $rawBody = $request->getContent();
                
                if (!empty($rawBody)) {
                    $decoded = json_decode($rawBody, true);
                    
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        // Merge decoded data into request
                        $request->merge($decoded);
                    } else {
                        // If not JSON, treat as raw string
                        $request->merge(['raw_data' => $rawBody]);
                    }
                }
            }
        }

        // Force JSON response
        $request->headers->set('Accept', 'application/json');
        
        $response = $next($request);
        
        // Ensure response is JSON
        if (!$response->headers->has('Content-Type')) {
            $response->headers->set('Content-Type', 'application/json');
        }
        
        return $response;
    }
}

