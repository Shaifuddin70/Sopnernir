<?php

namespace Tests\Feature;

use App\Models\SpecialInstallment;
use App\Models\SpecialInstallmentPayment;
use App\Models\User;
use App\Notifications\SpecialInstallmentRecordedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SpecialInstallmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_special_installment(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.special-installments.store'), [
                'title' => 'Eid collection',
                'amount' => '1500',
            ])
            ->assertRedirect();

        $installment = SpecialInstallment::query()->first();
        $this->assertNotNull($installment);
        $this->assertSame('Eid collection', $installment->title);
        $this->assertSame('1500.00', $installment->amount);

        $this->actingAs($admin)
            ->get(route('admin.special-installments.show', $installment))
            ->assertOk()
            ->assertSee('Eid collection', false)
            ->assertSee('1,500.00', false)
            ->assertSee(__('Create investment'), false)
            ->assertSee('special_installment='.$installment->id, false);
    }

    public function test_paid_special_installment_is_visible_to_the_investor(): void
    {
        Notification::fake();

        $admin = User::factory()->admin()->create();
        $investor = User::factory()->create();
        $installment = SpecialInstallment::query()->create([
            'title' => 'Eid collection',
            'amount' => '1500.00',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->patchJson(route('admin.special-installments.update', $installment), [
                'user_id' => $investor->id,
                'paid' => true,
            ])
            ->assertOk()
            ->assertJsonPath('is_paid', true);

        $this->assertTrue(
            SpecialInstallmentPayment::query()
                ->where('user_id', $investor->id)
                ->first()
                ?->isPaid() ?? false
        );

        Notification::assertSentTo($investor, SpecialInstallmentRecordedNotification::class);

        $this->actingAs($investor)
            ->get(route('payments.index'))
            ->assertOk()
            ->assertSee('Eid collection', false)
            ->assertSee('1,500.00', false)
            ->assertSee(__('One-time'), false);
    }

    public function test_unpaid_special_installment_stays_hidden(): void
    {
        $investor = User::factory()->create();

        SpecialInstallment::query()->create([
            'title' => 'Hidden collection',
            'amount' => '9000.00',
            'created_by' => $investor->id,
        ]);

        $this->actingAs($investor)
            ->get(route('payments.index'))
            ->assertOk()
            ->assertDontSee('Hidden collection', false)
            ->assertDontSee('9,000.00', false);
    }
}
