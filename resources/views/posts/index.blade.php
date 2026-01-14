<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('News Feed') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <!-- Create Post Form -->
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6 p-6">
                @if (session('success'))
                    <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <textarea name="content" rows="3"
                        class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        placeholder="What's on your mind?">{{ old('content') }}</textarea>

                    <div class="mt-4 flex items-center justify-between">
                        <input type="file" name="image" accept="image/jpeg,image/png,image/jpg,image/gif"
                            class="text-sm text-gray-500 dark:text-gray-400">
                        <x-primary-button type="submit">{{ __('Post') }}</x-primary-button>
                    </div>
                    @error('content') <span class="text-red-500 text-sm block mt-1">{{ $message }}</span> @enderror
                    @error('image') <span class="text-red-500 text-sm block mt-1">{{ $message }}</span> @enderror
                </form>
            </div>

            <!-- Posts Feed -->
            @foreach ($posts as $post)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-4">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full bg-gray-300 mr-3 overflow-hidden">
                                    @if($post->user->profile_picture)
                                        <img src="{{ Storage::url($post->user->profile_picture) }}"
                                            alt="{{ $post->user->name }}" class="w-full h-full object-cover">
                                    @endif
                                </div>
                                <div>
                                    <div class="font-bold dark:text-gray-200">{{ $post->user->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $post->created_at->diffForHumans() }}</div>
                                </div>
                            </div>
                            @if(auth()->id() === $post->user_id)
                                <form action="{{ route('posts.destroy', $post) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-500 hover:text-red-700">Delete</button>
                                </form>
                            @endif
                        </div>

                        <p class="mb-4 text-gray-800 dark:text-gray-200">{{ $post->content }}</p>

                        @if ($post->image_path)
                            <img src="{{ Storage::url($post->image_path) }}" class="rounded-lg mb-4 w-full" alt="Post Image">
                        @endif

                        <div class="flex items-center space-x-4 border-t border-gray-200 dark:border-gray-700 pt-4">
                            <button onclick="toggleLike({{ $post->id }})" id="like-btn-{{ $post->id }}"
                                class="flex items-center hover:text-blue-500 {{ $post->is_liked ? 'text-blue-500' : 'text-gray-500' }}">
                                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5" />
                                </svg>
                                <span id="like-count-{{ $post->id }}">{{ $post->likes_count ?? 0 }}</span> Likes
                            </button>
                            <button class="flex items-center text-gray-500 hover:text-blue-500"
                                onclick="document.getElementById('comment-input-{{ $post->id }}').focus()">
                                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                </svg>
                                Comment
                            </button>
                        </div>

                        <!-- Comments Section -->
                        <div class="mt-4 space-y-3">
                            @foreach($post->comments as $comment)
                                <div class="bg-gray-50 dark:bg-gray-900 p-3 rounded text-sm">
                                    <span class="font-bold dark:text-gray-300">{{ $comment->user->name }}</span>
                                    <span class="text-gray-700 dark:text-gray-400">{{ $comment->content }}</span>
                                </div>
                            @endforeach

                            <form action="{{ route('comments.store') }}" method="POST" class="mt-2 flex">
                                @csrf
                                <input type="hidden" name="post_id" value="{{ $post->id }}">
                                <input type="text" id="comment-input-{{ $post->id }}" name="content"
                                    class="flex-1 rounded-l-md border-gray-300 text-sm" placeholder="Write a comment...">
                                <button class="bg-blue-500 text-white px-4 rounded-r-md text-sm">Post</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach

            {{ $posts->links() }}
        </div>
    </div>
    <script>
        async function toggleLike(postId) {
            const btn = document.getElementById(`like-btn-${postId}`);
            const countSpan = document.getElementById(`like-count-${postId}`);

            try {
                const response = await fetch(`/posts/${postId}/like`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.status === 'liked') {
                    btn.classList.remove('text-gray-500');
                    btn.classList.add('text-blue-500');
                } else {
                    btn.classList.remove('text-blue-500');
                    btn.classList.add('text-gray-500');
                }

                countSpan.innerText = data.count;
            } catch (error) {
                console.error('Error toggling like:', error);
            }
        }
    </script>
</x-app-layout>