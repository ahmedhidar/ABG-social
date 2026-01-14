<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\FriendService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

class FriendController extends Controller
{
    protected $friendService;

    public function __construct(FriendService $friendService)
    {
        $this->friendService = $friendService;
    }

    #[OA\Post(
        path: "/api/friends/request/{userId}",
        operationId: "sendFriendRequest",
        summary: "Send friend request",
        security: [["apiAuth" => []]],
        tags: ["Friends"],
        parameters: [
            new OA\Parameter(name: "userId", description: "User id to send request to", required: true, in: "path", schema: new
                OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Friend request sent"),
            new OA\Response(response: 400, description: "Bad Request")
        ]
    )]
    public function sendRequest(Request $request, User $user)
    {
        // Prevent sending request to self
        if ($request->user()->id === $user->id) {
            return response()->json(['message' => 'Cannot send friend request to yourself'], 400);
        }

        $this->friendService->sendRequest($request->user()->id, $user->id);

        return response()->json(['message' => 'Friend request sent']);
    }

    #[OA\Post(
        path: "/api/friends/accept/{userId}",
        operationId: "acceptFriendRequest",
        summary: "Accept friend request",
        security: [["apiAuth" => []]],
        tags: ["Friends"],
        parameters: [
            new OA\Parameter(name: "userId", description: "User id to accept request from", required: true, in: "path", schema: new
                OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Friend request accepted"),
            new OA\Response(response: 404, description: "Friend request not found")
        ]
    )]
    public function acceptRequest(Request $request, User $user)
    {
        $friendRequest = \App\Models\FriendRequest::where('sender_id', $user->id)
            ->where('receiver_id', $request->user()->id)
            ->where('status', 'pending')
            ->firstOrFail();

        $this->authorize('update', $friendRequest);

        $this->friendService->acceptRequest($friendRequest);

        return response()->json(['message' => 'Friend request accepted']);
    }

    #[OA\Post(
        path: "/api/friends/reject/{userId}",
        operationId: "rejectFriendRequest",
        summary: "Reject friend request",
        security: [["apiAuth" => []]],
        tags: ["Friends"],
        parameters: [
            new OA\Parameter(name: "userId", description: "User id to reject request from", required: true, in: "path", schema: new
                OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Friend request rejected"),
            new OA\Response(response: 404, description: "Friend request not found")
        ]
    )]
    public function rejectRequest(Request $request, User $user)
    {
        $friendRequest = \App\Models\FriendRequest::where('sender_id', $user->id)
            ->where('receiver_id', $request->user()->id)
            ->where('status', 'pending')
            ->firstOrFail();

        $this->authorize('delete', $friendRequest);

        $this->friendService->rejectRequest($friendRequest);

        return response()->json(['message' => 'Friend request rejected']);
    }

    #[OA\Delete(
        path: "/api/friends/{userId}",
        operationId: "removeFriend",
        summary: "Remove friend",
        security: [["apiAuth" => []]],
        tags: ["Friends"],
        parameters: [
            new OA\Parameter(name: "userId", description: "User id to remove from friends", required: true, in: "path", schema: new
                OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Friend removed")
        ]
    )]
    public function removeFriend(Request $request, User $user)
    {
        $this->friendService->removeFriend($request->user()->id, $user->id);

        return response()->json(['message' => 'Friend removed']);
    }

    #[OA\Get(
        path: "/api/friends/requests",
        operationId: "getFriendRequests",
        summary: "Get pending friend requests",
        security: [["apiAuth" => []]],
        tags: ["Friends"],
        responses: [
            new OA\Response(response: 200, description: "List of pending requests")
        ]
    )]
    public function getRequests(Request $request)
    {
        $requests = $this->friendService->getPendingRequests($request->user()->id);
        return JsonResource::collection($requests);
    }

    #[OA\Get(
        path: "/api/friends",
        operationId: "getFriends",
        summary: "Get friends list",
        security: [["apiAuth" => []]],
        tags: ["Friends"],
        responses: [
            new OA\Response(response: 200, description: "List of friends")
        ]
    )]
    public function getFriends(Request $request)
    {
        $friends = $request->user()->friends;
        return JsonResource::collection($friends);
    }

    #[OA\Delete(
        path: "/api/friends/cancel/{userId}",
        operationId: "cancelFriendRequest",
        summary: "Cancel sent friend request",
        security: [["apiAuth" => []]],
        tags: ["Friends"],
        parameters: [
            new OA\Parameter(name: "userId", description: "User id to cancel request to", required: true, in: "path", schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Friend request cancelled")
        ]
    )]
    public function cancelRequest(Request $request, User $user)
    {
        $friendRequest = \App\Models\FriendRequest::where('sender_id', $request->user()->id)
            ->where('receiver_id', $user->id)
            ->where('status', 'pending')
            ->firstOrFail();

        $this->authorize('delete', $friendRequest);

        $this->friendService->cancelRequest($request->user()->id, $user->id);

        return response()->json(['message' => 'Friend request cancelled']);
    }
}