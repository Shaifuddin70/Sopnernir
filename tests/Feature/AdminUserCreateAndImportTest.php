<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserCreateAndImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_user_via_form(): void
    {
        $admin = User::factory()->admin()->create();

        $payload = [
            'name' => 'New Member',
            'email' => 'newmember@example.com',
            'phone' => '01700000000',
            'nid_number' => 'nid-new-member-unique',
            'address' => '123 Test Street',
            'nominee' => [
                'name' => 'Nominee Person',
                'email' => 'nominee@example.com',
                'phone' => '01800000000',
                'address' => '456 Nominee Ave',
            ],
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
        ];

        $this->actingAs($admin)
            ->post(route('admin.users.store'), $payload)
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $user = User::query()->where('email', 'newmember@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('Password1!', $user->password));
        $this->assertFalse($user->isAdmin());
        $this->assertNotNull($user->email_verified_at);
        $this->assertSame('Nominee Person', $user->nominee?->name);
    }

    public function test_admin_can_import_users_from_csv(): void
    {
        $admin = User::factory()->admin()->create();

        $header = 'name,email,phone,nid_number,address,nominee_name,nominee_email,nominee_phone,nominee_address,password,is_admin';
        $row1 = 'One,one@example.com,01711111111,nid-csv-one,Addr 1,N1,n1@example.com,01722222222,N addr 1,Password1!,0';
        $row2 = 'Two,two@example.com,01711111112,nid-csv-two,Addr 2,N2,n2@example.com,01722222223,N addr 2,Password1!,1';
        $csv = $header."\n".$row1."\n".$row2."\n";

        $file = UploadedFile::fake()->createWithContent('users.csv', $csv);

        $this->actingAs($admin)
            ->post(route('admin.users.import.store'), ['file' => $file])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.users.import'));

        $this->assertSame(2, User::query()->whereIn('email', ['one@example.com', 'two@example.com'])->count());
        $u2 = User::query()->where('email', 'two@example.com')->first();
        $this->assertTrue($u2->isAdmin());
    }

    public function test_non_admin_cannot_create_user(): void
    {
        $investor = User::factory()->create();

        $this->actingAs($investor)
            ->get(route('admin.users.create'))
            ->assertForbidden();
    }
}
