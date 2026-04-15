<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleSwitcherTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_switch_locale_and_session_persists(): void
    {
        $this->post(route('locale.update'), ['locale' => 'bn'])
            ->assertRedirect();

        $this->assertEquals('bn', session('locale'));

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('ইমেইল', false);
    }

    public function test_invalid_locale_is_rejected(): void
    {
        $this->post(route('locale.update'), ['locale' => 'fr'])
            ->assertSessionHasErrors('locale');
    }
}
