<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();
        $nominee = $user->nominee;

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'profile-new@example.com',
                'phone' => $user->phone,
                'nid_number' => $user->nid_number,
                'address' => $user->address,
                'nominee' => [
                    'name' => $nominee->name,
                    'email' => $nominee->email,
                    'phone' => $nominee->phone,
                    'address' => $nominee->address,
                ],
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('profile-new@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();
        $nominee = $user->nominee;

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
                'phone' => $user->phone,
                'nid_number' => $user->nid_number,
                'address' => $user->address,
                'nominee' => [
                    'name' => $nominee->name,
                    'email' => $nominee->email,
                    'phone' => $nominee->phone,
                    'address' => $nominee->address,
                ],
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }
}
