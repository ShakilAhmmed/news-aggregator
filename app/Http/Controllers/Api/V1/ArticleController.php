<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\V1\ArticleFilterAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ArticleApiResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ArticleController extends Controller
{
    public function index(Request $request, ArticleFilterAction $filter): JsonResponse
    {
        try {
            $articles = $filter->execute($request);
            logger()->info('article:fetch');

            return $this->paginatedResponse(data: ArticleApiResource::collection($articles),
                message: 'Articles fetched successfully.',
                code: Response::HTTP_OK);
        } catch (Throwable $exception) {
            logger()->critical('article:fetch -> '.$exception->getMessage());

            return $this->errorResponse('Articles fetch failed.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
