<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Post;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Main User
        $me = User::create([
            'name' => 'Ahmed',
            'email' => 'ahmed@example.com',
            'password' => Hash::make('password'),
            'bio' => 'Full Stack Developer',
        ]);

        // Create some posts for me
        Post::create([
            'user_id' => $me->id,
            'content' => 'Hello world! This is my first post on the new social network.',
        ]);

        Post::create([
            'user_id' => $me->id,
            'content' => 'Building this with Laravel is fun!',
        ]);

        // Create Other Users
        $users = [
            ['name' => 'John Doe', 'email' => 'john@example.com', 'bio' => 'Loves coffee'],
            ['name' => 'Jane Smith', 'email' => 'jane@example.com', 'bio' => 'Traveler'],
            ['name' => 'Bob Wilson', 'email' => 'bob@example.com', 'bio' => 'Gamer'],
            ['name' => 'Alice Brown', 'email' => 'alice@example.com', 'bio' => 'Artist'],
        ];

        foreach ($users as $userData) {
            $user = User::create(array_merge($userData, [
                'password' => Hash::make('password'),
            ]));

            // Create a post for each user
            Post::create([
                'user_id' => $user->id,
                'content' => 'Just joined! Happy to be here.',
            ]);
        }
    }
}
