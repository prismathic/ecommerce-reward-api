<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ProvidesApiResponses
{
    /**
     * Send a formatted 200 HTTP response.
     *
     * @param string $message
     * @param mixed $data
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function okResponse(string $message, $data = null): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data,
        ]);
    }

    /**
     * Send a formatted 201 HTTP response.
     *
     * @param string $message
     * @param mixed $data
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function createdResponse(string $message, $data = null): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data,
        ], JsonResponse::HTTP_CREATED);
    }

    /**
     * Send a formatted 400 HTTP response.
     *
     * @param string $message
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function badRequestResponse(string $message): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $message,
        ], JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Send a formatted 404 HTTP response.
     *
     * @param string|null $message
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function notFoundResponse(?string $message = null): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $message ?? 'Resource not found.',
        ], JsonResponse::HTTP_NOT_FOUND);
    }

    /**
     * Send a formatted 500 HTTP response.
     *
     * @param string|null $message
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function serverErrorResponse(?string $message = null): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $message ?? 'Internal server error',
        ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
    }
}
