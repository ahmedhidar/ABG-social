<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    #[OA\Get(
        path: "/api/notifications",
        operationId: "getNotifications",
        summary: "Get user notifications",
        security: [["apiAuth" => []]],
        tags: ["Notifications"],
        responses: [
            new OA\Response(response: 200, description: "List of notifications")
        ]
    )]
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()->latest()->paginate(20);
        return JsonResource::collection($notifications);
    }

    #[OA\Post(
        path: "/api/notifications/mark-read",
        operationId: "markNotificationsAsRead",
        summary: "Mark all notifications as read",
        security: [["apiAuth" => []]],
        tags: ["Notifications"],
        responses: [
            new OA\Response(response: 200, description: "Notifications marked as read")
        ]
    )]
    public function markAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['message' => 'All notifications marked as read']);
    }
}