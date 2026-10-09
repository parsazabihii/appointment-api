<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_local_accounts_and_preserves_existing_passwords(): void
    {
        $this->seed();
        foreach (['admin', 'provider', 'customer'] as $role) {
            $user = User::where('email', $role.'@example.com')->firstOrFail();
            $this->assertSame($role, $user->role->value);
            $this->assertTrue(Hash::check('password123', $user->password));
        }
        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $admin->update(['password' => 'changed-password']);
        $this->seed();
        $this->assertDatabaseCount('users', 3);
        $this->assertTrue(Hash::check('changed-password', $admin->fresh()->password));
        $this->assertDatabaseCount('providers', 1);
        $this->assertDatabaseCount('services', 2);
        $this->assertDatabaseCount('availabilities', 5);
    }
}
