<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Services\PostService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

class PostController extends Controller
{
    protected $postService;

    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }

    #[OA\Get(
        path: "/api/posts",
        operationId: "getPosts",
        summary: "Get news feed",
        description: "Returns list of posts from user and friends",
        security: [["apiAuth" => []]],
        tags: ["Posts"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful operation",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "data", type: "array", items: new OA\Items(type: "object"))
                    ]
                )
            )
        ]
    )]
    public function index(Request $request)
    {
        $posts = $this->postService->getNewsFeed($request->user()->id);
        return JsonResource::collection($posts);
    }

    #[OA\Post(
        path: "/api/posts",
        operationId: "createPost",
        summary: "Create a new post",
        description: "Creates a new post with optional image",
        security: [["apiAuth" => []]],
        tags: ["Posts"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ["content"],
                    properties: [
                        new OA\Property(property: "content", type: "string", example: "Hello world!"),
                        new OA\Property(property: "image", type: "string", format: "binary")
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Post created successfully")
        ]
    )]
    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
            'image' => 'nullable|image|max:2048',
        ]);

        $data = [
            'user_id' => $request->user()->id,
            'content' => $request->input('content'),
        ];

        $post = $this->postService->createPost($data, $request->file('image'));

        return new JsonResource($post->load('user'));
    }

    #[OA\Get(
        path: "/api/posts/{id}",
        operationId: "getPostById",
        summary: "Get post information",
        description: "Returns post data",
        security: [["apiAuth" => []]],
        tags: ["Posts"],
        parameters: [
            new OA\Parameter(name: "id", description: "Post id", required: true, in: "path", schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Successful operation"),
            new OA\Response(response: 404, description: "Post not found")
        ]
    )]
    public function show(Post $post)
    {
        return new JsonResource($post->load(['user', 'comments.user', 'likes']));
    }

    #[OA\Put(
        path: "/api/posts/{id}",
        operationId: "updatePost",
        summary: "Update existing post",
        security: [["apiAuth" => []]],
        tags: ["Posts"],
        parameters: [
            new OA\Parameter(name: "id", description: "Post id", required: true, in: "path", schema: new OA\Schema(type: "integer"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["content"],
                properties: [
                    new OA\Property(property: "content", type: "string", example: "Updated content")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Post updated successfully")
        ]
    )]
    public function update(Request $request, Post $post)
    {
        $this->authorize('update', $post);

        $request->validate([
            'content' => 'required|string|max:1000',
            'image' => 'nullable|image|max:2048',
        ]);

        $data = [
            'content' => $request->input('content'),
        ];

        $updatedPost = $this->postService->updatePost($post, $data, $request->file('image'));

        return new JsonResource($updatedPost);
    }

    #[OA\Delete(
        path: "/api/posts/{id}",
        operationId: "deletePost",
        summary: "Delete existing post",
        security: [["apiAuth" => []]],
        tags: ["Posts"],
        parameters: [
            new OA\Parameter(name: "id", description: "Post id", required: true, in: "path", schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Post deleted successfully")
        ]
    )]
    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);

        $this->postService->deletePost($post);

        return response()->json(['message' => 'Post deleted successfully']);
    }
}
