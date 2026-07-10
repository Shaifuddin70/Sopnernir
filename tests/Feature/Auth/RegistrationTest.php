<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'register-test@example.com',
            'phone' => '01700000001',
            'nid_number' => 'NID-REG-'.uniqid(),
            'address' => '10 Test Road, Dhaka',
            'password' => 'password',
            'password_confirmation' => 'password',
            'nominee' => [
                'name' => 'Nominee Person',
                'email' => 'nominee-register@example.com',
                'phone' => '01800000002',
                'nid_number' => 'NID-NOM-'.uniqid(),
                'address' => '20 Nominee Lane',
            ],
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        $user = User::where('email', 'register-test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('Nominee Person', $user->nominee?->name);
    }
}
