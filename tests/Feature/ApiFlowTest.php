<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Post;
use App\Models\FriendRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ApiFlowTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $tokenA;
    protected $tokenB;
    protected $userA;
    protected $userB;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Register User A
        $responseA = $this->postJson('/api/register', [
            'name' => 'User A',
            'email' => 'a@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $responseA->assertStatus(201);
        $this->tokenA = $responseA->json('access_token');
        $this->userA = User::where('email', 'a@example.com')->first();

        // 2. Register User B
        $responseB = $this->postJson('/api/register', [
            'name' => 'User B',
            'email' => 'b@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $responseB->assertStatus(201);
        $this->tokenB = $responseB->json('access_token');
        $this->userB = User::where('email', 'b@example.com')->first();

        file_put_contents('test_debug.log', "User A: ID=" . $this->userA->id . " Email=" . $this->userA->email . " Token=" . substr($this->tokenA, 0, 10) . "...\n", FILE_APPEND);
        file_put_contents('test_debug.log', "User B: ID=" . $this->userB->id . " Email=" . $this->userB->email . " Token=" . substr($this->tokenB, 0, 10) . "...\n", FILE_APPEND);
    }

    public function test_full_api_workflow()
    {
        // 3. User A creates a post
        $postResponse = $this->actingAs($this->userA, 'sanctum')
            ->postJson('/api/posts', [
                    'content' => 'Hello from User A',
                ]);
        $postResponse->assertStatus(201);
        $postId = $postResponse->json('data.id') ?? $postResponse->json('id');
        file_put_contents('test_debug.log', "DEBUG: Created Post ID $postId\n", FILE_APPEND);

        // 4. User B gets feed (should NOT see User A's post yet)
        $feedResponse = $this->actingAs($this->userB, 'sanctum')
            ->getJson('/api/posts');
        $feedResponse->assertStatus(200);
        $this->assertEmpty(collect($feedResponse->json('data'))->where('id', $postId));

        // 5. User A sends friend request to User B
        $requestResponse = $this->actingAs($this->userA, 'sanctum')
            ->postJson("/api/friends/request/{$this->userB->id}");
        $requestResponse->assertStatus(200);

        // 6. User B lists requests
        $listResponse = $this->actingAs($this->userB, 'sanctum')
            ->getJson('/api/friends/requests');
        $listResponse->assertStatus(200);
        $this->assertNotEmpty(collect($listResponse->json('data'))->where('sender_id', $this->userA->id));

        // 7. User B accepts request
        $acceptResponse = $this->actingAs($this->userB, 'sanctum')
            ->postJson("/api/friends/accept/{$this->userA->id}");
        if ($acceptResponse->status() !== 200) {
            dump("ACCEPT FAIL: " . $acceptResponse->status());
            dump($acceptResponse->json());
        }
        $acceptResponse->assertStatus(200);

        // 8. User B gets feed again (should NOW see User A's post)
        $feedResponse2 = $this->actingAs($this->userB, 'sanctum')
            ->getJson('/api/posts');
        $feedResponse2->assertStatus(200);
        $this->assertNotEmpty(collect($feedResponse2->json('data'))->where('id', $postId));

        // 9. User B likes User A's post
        $likeResponse = $this->actingAs($this->userB, 'sanctum')
            ->postJson("/api/posts/{$postId}/like");
        $likeResponse->assertStatus(200)
            ->assertJsonPath('liked', true);

        // 10. User B comments on User A's post
        $commentResponse = $this->actingAs($this->userB, 'sanctum')
            ->postJson("/api/posts/{$postId}/comments", [
                    'content' => 'Great post, A!',
                ]);
        $commentResponse->assertStatus(201);

        // 11. User A checks notifications
        $notifResponse = $this->actingAs($this->userA, 'sanctum')
            ->getJson('/api/notifications');
        $notifResponse->assertStatus(200);
        $notifications = $notifResponse->json();
        $this->assertNotEmpty($notifications);

        // 12. Profile Check
        $profileResponse = $this->actingAs($this->userA, 'sanctum')
            ->getJson("/api/profile/{$this->userB->id}");
        $profileResponse->assertStatus(200);

        // 13. Update Profile
        $updateResponse = $this->actingAs($this->userA, 'sanctum')
            ->putJson('/api/profile', [
                    'name' => 'User A Updated',
                    'bio' => 'Brand new bio',
                ]);
        $updateResponse->assertStatus(200);

        $user = User::find($this->userA->id);
        \Illuminate\Support\Facades\Log::info("TEST: User " . $user->id . " name is " . $user->name);

        $this->assertEquals('User A Updated', $user->name);
    }
}
