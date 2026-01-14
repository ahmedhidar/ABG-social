<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Services\LikeService;
use Illuminate\Http\JsonResponse;

class LikeController extends Controller
{
    public function __construct(protected LikeService $likeService)
    {
    }

    public function togglePostLike(Post $post): JsonResponse
    {
        $result = $this->likeService->toggleLike($post, auth()->id());

        return response()->json($result);
    }
}
