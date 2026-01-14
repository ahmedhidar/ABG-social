<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ProfileService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

class ProfileController extends Controller
{
    protected $profileService;

    public function __construct(ProfileService $profileService)
    {
        $this->profileService = $profileService;
    }

    #[OA\Get(
        path: "/api/profile/{userId}",
        operationId: "getUserProfile",
        summary: "Get user profile",
        security: [["apiAuth" => []]],
        tags: ["Profile"],
        parameters: [
            new OA\Parameter(name: "userId", description: "User id", required: true, in: "path", schema: new OA\Schema(type:
                "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "User profile data")
        ]
    )]
    public function show(Request $request, User $user)
    {
        $posts = $user->posts()->latest()->paginate(10);

        $isFriend = false;
        $hasSentRequest = false;
        $hasReceivedRequest = false;

        if ($request->user()->id !== $user->id) {
            $isFriend = $request->user()->friends()->where('friend_id', $user->id)->exists();
            if (!$isFriend) {
                $hasSentRequest = $request->user()->friendRequestsSent()->where('receiver_id', $user->id)->exists();
                $hasReceivedRequest = $request->user()->friendRequestsReceived()->where('sender_id', $user->id)->exists();
            }
        }

        return response()->json([
            'user' => new JsonResource($user),
            'posts' => JsonResource::collection($posts),
            'relationship' => [
                'is_self' => $request->user()->id === $user->id,
                'is_friend' => $isFriend,
                'has_sent_request' => $hasSentRequest,
                'has_received_request' => $hasReceivedRequest,
            ]
        ]);
    }

    #[OA\Put(
        path: "/api/profile",
        operationId: "updateProfile",
        summary: "Update user profile",
        security: [["apiAuth" => []]],
        tags: ["Profile"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ["name"],
                    properties: [
                        new OA\Property(property: "name", type: "string", example: "John Doe"),
                        new OA\Property(property: "bio", type: "string", example: "Software Developer"),
                        new OA\Property(property: "avatar", type: "string", format: "binary")
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Profile updated successfully")
        ]
    )]
    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'avatar' => 'nullable|image|max:2048',
            'bio' => 'nullable|string|max:500',
        ]);

        $user = $request->user();

        $data = [
            'name' => $request->name,
            'bio' => $request->bio,
        ];

        $updatedUser = $this->profileService->updateProfile($user, $data, $request->file('avatar'));

        return new JsonResource($updatedUser);
    }
}