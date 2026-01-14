<?php

use App\Models\User;
use App\Models\FriendRequest;
use App\Notifications\NewFriendRequest;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Sleeping for 10 seconds to allow browser login...\n";
sleep(10);

$user = User::where('email', 'realtime@test.com')->first();
if ($user) {
    echo "Found user: " . $user->name . "\n";
    $sender = User::factory()->create(['name' => 'FinalSender']);
    $user->notify(new NewFriendRequest($sender));
    echo "Notification sent from FinalSender to " . $user->name . "\n";
} else {
    echo "User realtime@test.com not found! Run the previous setup if needed.\n";
}
