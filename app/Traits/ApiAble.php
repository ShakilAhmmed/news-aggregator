<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait ApiAble
{
    /**
     * Success response  function
     *
     * @param mixed $data Main data pass.
     * @param string|null $message Some message.
     * @param int $code Status code pass.
     */
    protected function successResponse(mixed $data = [], ?string $message = null, int $code = Response::HTTP_OK): JsonResponse
    {
        return response()->json([
            'status' => 'Success',
            'message' => $message,
            'code' => $code,
            'data' => $data,
        ], $code);
    }

    /**
     * Error response  function
     *
     * @param string $message Some message.
     * @param int $code Status code pass.
     */
    protected function paginatedResponse($data, string $message, int $code): JsonResponse
    {
        $payload = $data->response()->getData(true);

        return response()->json([
            'data' => $payload['data'] ?? [],
            'links' => $payload['links'] ?? null,
            'meta' => $payload['meta'] ?? null,
            'message' => $message,
            'code' => $code,
        ], $code);
    }

    protected function errorResponse(string $message, int $code = Response::HTTP_INTERNAL_SERVER_ERROR): JsonResponse
    {
        return response()->json([
            'status' => 'Error',
            'message' => $message,
            'code' => $code,
            'data' => null,
        ], $code);
    }
}
