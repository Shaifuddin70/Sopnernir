<?php

namespace Tests\Feature;

use App\Models\Notice;
use App\Models\User;
use App\Notifications\NoticePublishedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NoticeBoardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_draft_notice(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.notices.store'), [
                'title' => 'Office closed Friday',
                'body' => '<p>We will be closed this Friday.</p>',
                'status' => 'draft',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('notices', [
            'title' => 'Office closed Friday',
            'status' => Notice::STATUS_DRAFT,
        ]);
    }

    public function test_publishing_notice_emails_active_users(): void
    {
        Notification::fake();
        Storage::fake('local');

        $admin = User::factory()->admin()->create(['email' => 'admin@example.com']);
        $investor = User::factory()->create(['email' => 'investor@example.com']);
        User::factory()->create(['email' => 'inactive@example.com', 'is_active' => false]);

        $this->actingAs($admin)
            ->post(route('admin.notices.store'), [
                'title' => 'AGM next week',
                'body' => '<p>Please join the annual meeting.</p>',
                'status' => 'published',
                'is_pinned' => '1',
                'attachments' => [
                    UploadedFile::fake()->create('agenda.pdf', 120, 'application/pdf'),
                ],
            ])
            ->assertRedirect();

        $notice = Notice::query()->first();
        $this->assertNotNull($notice);
        $this->assertTrue($notice->isPublished());
        $this->assertTrue($notice->is_pinned);
        $this->assertSame(1, $notice->attachments()->count());
        $this->assertNotNull($notice->emailed_at);

        Notification::assertSentTo($investor, NoticePublishedNotification::class);
        Notification::assertSentTo($admin, NoticePublishedNotification::class);
    }

    public function test_investors_see_published_notices_only(): void
    {
        $admin = User::factory()->admin()->create();
        $investor = User::factory()->create();

        $published = Notice::query()->create([
            'title' => 'Visible notice',
            'body' => '<p>Hello</p>',
            'status' => Notice::STATUS_PUBLISHED,
            'published_at' => now(),
            'created_by' => $admin->id,
        ]);

        Notice::query()->create([
            'title' => 'Hidden draft',
            'body' => '<p>Secret</p>',
            'status' => Notice::STATUS_DRAFT,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($investor)
            ->get(route('notices.index'))
            ->assertOk()
            ->assertSee('Visible notice')
            ->assertDontSee('Hidden draft');

        $this->actingAs($investor)
            ->get(route('notices.show', $published))
            ->assertOk()
            ->assertSee('Visible notice');

        $this->assertDatabaseHas('notice_reads', [
            'notice_id' => $published->id,
            'user_id' => $investor->id,
        ]);
    }

    public function test_investor_cannot_access_admin_notice_manage(): void
    {
        $investor = User::factory()->create();

        $this->actingAs($investor)
            ->get(route('admin.notices.index'))
            ->assertForbidden();
    }

    public function test_investor_cannot_open_draft_notice(): void
    {
        $admin = User::factory()->admin()->create();
        $investor = User::factory()->create();
        $draft = Notice::query()->create([
            'title' => 'Draft only',
            'body' => '<p>Soon</p>',
            'status' => Notice::STATUS_DRAFT,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($investor)
            ->get(route('notices.show', $draft))
            ->assertNotFound();
    }
}
