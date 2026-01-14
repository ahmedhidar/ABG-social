<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $user->name }}'s Profile
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">

            <!-- User Info Card -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6 p-6 text-center">
                <div class="w-32 h-32 bg-gray-300 rounded-full mx-auto mb-4 overflow-hidden">
                    @if($user->profile_picture)
                        <img src="{{ Storage::url($user->profile_picture) }}" class="w-full h-full object-cover">
                    @endif
                </div>
                <h3 class="text-2xl font-bold dark:text-white">{{ $user->name }}</h3>
                <p class="text-gray-500 dark:text-gray-400 mb-4">{{ $user->bio ?? 'No bio yet.' }}</p>

                @if(auth()->id() !== $user->id)
                    <div x-data="{ 
                        status: '{{ $isFriend ? 'friend' : ($sentRequest ? 'sent' : ($receivedRequest ? 'received' : 'none')) }}',
                        loading: false,
                        async sendRequest() {
                            this.loading = true;
                            try {
                                await axios.post('{{ route('friends.send') }}', { receiver_id: {{ $user->id }} });
                                this.status = 'sent';
                            } catch (e) { 
                                console.error(e);
                                alert('Failed to send request.');
                            } finally {
                                this.loading = false;
                            }
                        },
                        async cancelRequest() {
                            this.loading = true;
                            try {
                                await axios.delete('{{ route('friends.cancel', $user) }}');
                                this.status = 'none';
                            } catch (e) { 
                                console.error(e);
                                alert('Failed to cancel request.');
                            } finally {
                                this.loading = false;
                            }
                        }
                    }" class="inline-block">
                        <template x-if="status === 'friend'">
                            <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-semibold">Friends</span>
                        </template>

                        <template x-if="status === 'received'">
                            <div class="flex flex-col items-center space-y-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Sent you a friend request</p>
                                <div class="flex space-x-2">
                                    <form action="{{ route('friends.accept', $receivedRequest ?? 0) }}" method="POST">
                                        @csrf @method('PATCH')
                                        <x-primary-button>{{ __('Accept') }}</x-primary-button>
                                    </form>
                                    <form action="{{ route('friends.reject', $receivedRequest ?? 0) }}" method="POST">
                                        @csrf @method('DELETE')
                                        <x-danger-button>{{ __('Reject') }}</x-danger-button>
                                    </form>
                                </div>
                            </div>
                        </template>

                        <template x-if="status === 'sent'">
                            <x-danger-button @click="cancelRequest()" ::disabled="loading">
                                <span x-show="!loading">{{ __('Cancel Request') }}</span>
                                <span x-show="loading">{{ __('Processing...') }}</span>
                            </x-danger-button>
                        </template>

                        <template x-if="status === 'none'">
                            <x-primary-button @click="sendRequest()" ::disabled="loading">
                                <span x-show="!loading">{{ __('Add Friend') }}</span>
                                <span x-show="loading">{{ __('Processing...') }}</span>
                            </x-primary-button>
                        </template>
                    </div>
                @else
                    <a href="{{ route('profile.edit') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                        Edit Profile
                    </a>
                @endif
            </div>

            <h3 class="font-bold text-xl mb-4 dark:text-gray-200">Posts</h3>

            <!-- Posts Feed -->
            @forelse ($posts as $post)
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
                                    <button class="text-red-500 hover:text-red-700 text-sm">Delete</button>
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
                        <div class="mt-4 space-y-2">
                            @foreach($post->comments as $comment)
                                <div class="bg-gray-50 dark:bg-gray-900 p-2 rounded text-sm">
                                    <span class="font-bold dark:text-gray-300">{{ $comment->user->name }}</span>
                                    <span class="text-gray-700 dark:text-gray-400">{{ $comment->content }}</span>
                                </div>
                            @endforeach

                            <form action="{{ route('comments.store') }}" method="POST" class="mt-2 flex">
                                @csrf
                                <input type="hidden" name="post_id" value="{{ $post->id }}">
                                <input type="text" id="comment-input-{{ $post->id }}" name="content" class="flex-1 rounded-l-md border-gray-300 text-sm dark:bg-gray-900 dark:text-gray-300"
                                    placeholder="Write a comment...">
                                <button class="bg-blue-500 text-white px-4 rounded-r-md text-sm">Post</button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-center text-gray-500 py-8">No posts yet.</p>
            @endforelse

            {{ $posts->links() }}
        </div>
    </div>

    @push('scripts')
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
    @endpush
</x-app-layout>