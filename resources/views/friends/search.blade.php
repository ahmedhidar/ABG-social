<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Find Friends') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Search Bar -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form action="{{ route('friends.search') }}" method="GET" class="flex gap-4">
                    <input type="text" name="query" value="{{ $query ?? '' }}" class="w-full rounded-md border-gray-300"
                        placeholder="Search for people...">
                    <x-primary-button>{{ __('Search') }}</x-primary-button>
                </form>
            </div>

            <!-- Results -->
            @if(isset($users) && count($users) > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($users as $user)
                        <div
                            class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-12 h-12 rounded-full bg-gray-300 mr-4 overflow-hidden">
                                    @if($user->profile_picture)
                                        <img src="{{ Storage::url($user->profile_picture) }}" class="w-full h-full object-cover">
                                    @endif
                                </div>
                                <div class="font-bold dark:text-gray-200">{{ $user->name }}</div>
                            </div>
                            <div x-data="{ 
                                        status: '{{ $user->friends->count() > 0 ? 'friend' : ($user->friendRequestsReceived->count() > 0 ? 'sent' : ($user->friendRequestsSent->count() > 0 ? 'received' : 'none')) }}',
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
                                                await axios.delete('{{ route('friends.cancel', $user->id) }}');
                                                this.status = 'none';
                                            } catch (e) { 
                                                console.error(e);
                                                alert('Failed to cancel request.');
                                            } finally {
                                                this.loading = false;
                                            }
                                        }
                                    }">
                                <template x-if="status === 'friend'">
                                    <span
                                        class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-semibold">Friends</span>
                                </template>

                                <template x-if="status === 'received'">
                                    <span class="text-xs text-gray-500 italic">Sent you a request</span>
                                </template>

                                <template x-if="status === 'sent'">
                                    <x-danger-button @click="cancelRequest()" ::disabled="loading" class="text-xs">
                                        <span x-show="!loading">{{ __('Cancel') }}</span>
                                        <span x-show="loading">...</span>
                                    </x-danger-button>
                                </template>

                                <template x-if="status === 'none'">
                                    <x-primary-button @click="sendRequest()" ::disabled="loading" class="text-xs">
                                        <span x-show="!loading">{{ __('Add Friend') }}</span>
                                        <span x-show="loading">...</span>
                                    </x-primary-button>
                                </template>
                            </div>
                        </div>
                    @endforeach
                </div>
            @elseif(isset($query))
                <p class="text-center text-gray-500">No users found.</p>
            @endif
        </div>
    </div>
</x-app-layout>