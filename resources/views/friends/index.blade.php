<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('My Friends') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Pending Requests -->
            @if($requests->count() > 0)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="font-bold text-lg mb-4 dark:text-gray-200">Friend Requests</h3>
                    <div class="space-y-4">
                        @foreach($requests as $request)
                            <div class="flex justify-between items-center border-b pb-2 dark:border-gray-700">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-gray-300 rounded-full mr-3 overflow-hidden">
                                        @if($request->sender->profile_picture)
                                            <img src="{{ Storage::url($request->sender->profile_picture) }}"
                                                class="w-full h-full object-cover">
                                        @endif
                                    </div>
                                    <span class="dark:text-gray-200">{{ $request->sender->name }}</span>
                                </div>
                                <div class="flex space-x-2">
                                    <form action="{{ route('friends.accept', $request) }}" method="POST">
                                        @csrf @method('PATCH')
                                        <button class="bg-green-500 text-white px-3 py-1 rounded">Accept</button>
                                    </form>
                                    <form action="{{ route('friends.reject', $request) }}" method="POST">
                                        @csrf @method('DELETE')
                                        <button class="bg-red-500 text-white px-3 py-1 rounded">Reject</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Friends List -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="font-bold text-lg mb-4 dark:text-gray-200">All Friends</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($friends as $friend)
                        <div class="border dark:border-gray-700 p-4 rounded flex items-center">
                            <div class="w-12 h-12 bg-gray-300 rounded-full mr-4 overflow-hidden">
                                @if($friend->profile_picture)
                                    <img src="{{ Storage::url($friend->profile_picture) }}" class="w-full h-full object-cover">
                                @endif
                            </div>
                            <span class="font-bold dark:text-gray-200">{{ $friend->name }}</span>
                        </div>
                    @endforeach
                </div>
                {{ $friends->links() }}
            </div>

        </div>
    </div>
</x-app-layout>