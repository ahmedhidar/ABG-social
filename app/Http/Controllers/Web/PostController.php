<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;
use App\Services\PostService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PostController extends Controller
{
    public function __construct(protected PostService $postService)
    {
    }

    public function index(): View
    {
        $posts = $this->postService->getNewsFeed(auth()->id());
        return view('posts.index', compact('posts'));
    }

    public function store(StorePostRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();

        $this->postService->createPost(
            $data,
            $request->file('image')
        );

        return back()->with('success', 'Post created successfully!');
    }

    public function update(UpdatePostRequest $request, Post $post): RedirectResponse
    {
        $this->authorize('update', $post);

        $this->postService->updatePost(
            $post,
            $request->validated(),
            $request->file('image')
        );

        return back()->with('success', 'Post updated successfully!');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $this->authorize('delete', $post);

        $this->postService->deletePost($post);

        return back()->with('success', 'Post deleted successfully!');
    }
}
