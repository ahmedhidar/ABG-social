<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendFriendRequestRequest;
use App\Models\FriendRequest;
use App\Models\User;
use App\Services\FriendService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FriendController extends Controller
{
    public function __construct(protected FriendService $friendService)
    {
    }

    public function index(): View
    {
        $user = auth()->user();
        $friends = $user->friends()->paginate(20);
        $requests = $this->friendService->getPendingRequests($user->id);

        return view('friends.index', compact('friends', 'requests'));
    }

    public function sendRequest(SendFriendRequestRequest $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $this->friendService->sendRequest(auth()->id(), $request->validated()['receiver_id']);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Friend request sent!']);
        }

        return back()->with('success', 'Friend request sent!');
    }

    public function acceptRequest(FriendRequest $friendRequest): RedirectResponse
    {
        $this->authorize('update', $friendRequest);

        $this->friendService->acceptRequest($friendRequest);

        return back()->with('success', 'Friend request accepted!');
    }

    public function rejectRequest(FriendRequest $friendRequest): RedirectResponse
    {
        $this->authorize('delete', $friendRequest);

        $this->friendService->rejectRequest($friendRequest);

        return back()->with('success', 'Friend request rejected.');
    }

    public function search(Request $request): View
    {
        $query = $request->input('query');
        $users = $query ? $this->friendService->searchUsers($query, auth()->id()) : [];

        return view('friends.search', compact('users', 'query'));
    }

    public function cancelRequest(Request $request, User $user): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $friendRequest = FriendRequest::where('sender_id', auth()->id())
            ->where('receiver_id', $user->id)
            ->where('status', 'pending')
            ->firstOrFail();

        $this->authorize('delete', $friendRequest);

        $this->friendService->cancelRequest(auth()->id(), $user->id);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Friend request cancelled!']);
        }

        return back()->with('success', 'Friend request cancelled.');
    }
}
