<?php

namespace App\Traits;

use Throwable;

trait ApiResponse
{
    /**
     * Return a success JSON response.
     *
     * @param  mixed  $data
     * @param  string|null  $message
     * @param  int  $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function success($data = null, ?string $message = null, int $code = 200)
    {
        return response()->json([
            'status'  => true,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    /**
     * Return an error JSON response.
     *
     * @param  string|null  $message
     * @param  int  $code
     * @param  mixed  $errors
     * @return \Illuminate\Http\JsonResponse
     */
    protected function error(?string $message = null, int $code = 400, $errors = [])
    {
        $response = [
            'status'  => false,
            'message' => $message,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    /**
     * Return a server error JSON response.
     *
     * @param  string  $message
     * @param  \Throwable|null  $e
     * @return \Illuminate\Http\JsonResponse
     */
    protected function serverError(string $message = 'Server Error', ?Throwable $e = null)
    {
        $response = [
            'status'  => false,
            'message' => $message,
        ];

        if (config('app.debug') && $e !== null) {
            $response['error_details'] = $e->getMessage();
        }

        return response()->json($response, 500);
    }
}
