<?php

namespace App\Services;

use App\Models\Notice;
use App\Models\NoticeAttachment;
use App\Models\User;
use App\Support\NoticeHtml;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class NoticeService
{
    public function __construct(
        private readonly InvestorNotificationService $notifications,
    ) {}

    /**
     * @param  array{title:string,body:string,is_pinned?:bool,status?:string}  $data
     * @param  list<UploadedFile>  $files
     */
    public function create(User $author, array $data, array $files = []): Notice
    {
        return DB::transaction(function () use ($author, $data, $files) {
            $status = $data['status'] ?? Notice::STATUS_DRAFT;
            $publish = $status === Notice::STATUS_PUBLISHED;

            $notice = Notice::query()->create([
                'title' => trim($data['title']),
                'body' => NoticeHtml::sanitize($data['body']),
                'status' => $publish ? Notice::STATUS_PUBLISHED : Notice::STATUS_DRAFT,
                'is_pinned' => (bool) ($data['is_pinned'] ?? false),
                'published_at' => $publish ? now() : null,
                'created_by' => $author->id,
            ]);

            $this->storeAttachments($notice, $files);

            if ($publish) {
                $this->notifications->noticePublished($notice);
                $notice->forceFill(['emailed_at' => now()])->save();
            }

            return $notice->fresh(['attachments', 'author']);
        });
    }

    /**
     * @param  array{title:string,body:string,is_pinned?:bool,status?:string}  $data
     * @param  list<UploadedFile>  $files
     */
    public function update(Notice $notice, array $data, array $files = []): Notice
    {
        return DB::transaction(function () use ($notice, $data, $files) {
            $wasPublished = $notice->isPublished();
            $status = $data['status'] ?? $notice->status;
            $publish = $status === Notice::STATUS_PUBLISHED;

            $notice->fill([
                'title' => trim($data['title']),
                'body' => NoticeHtml::sanitize($data['body']),
                'is_pinned' => (bool) ($data['is_pinned'] ?? false),
                'status' => $publish ? Notice::STATUS_PUBLISHED : Notice::STATUS_DRAFT,
            ]);

            if ($publish && ! $wasPublished) {
                $notice->published_at = $notice->published_at ?? now();
            }

            if (! $publish) {
                $notice->published_at = null;
            }

            $notice->save();
            $this->storeAttachments($notice, $files);

            if ($publish && ! $wasPublished) {
                $this->notifications->noticePublished($notice);
                $notice->forceFill(['emailed_at' => now()])->save();
            }

            return $notice->fresh(['attachments', 'author']);
        });
    }

    public function publish(Notice $notice, bool $sendEmail = true): Notice
    {
        $wasPublished = $notice->isPublished();

        $notice->forceFill([
            'status' => Notice::STATUS_PUBLISHED,
            'published_at' => $notice->published_at ?? now(),
        ])->save();

        if ($sendEmail && (! $wasPublished || $notice->emailed_at === null)) {
            $this->notifications->noticePublished($notice);
            $notice->forceFill(['emailed_at' => now()])->save();
        }

        return $notice->fresh(['attachments', 'author']);
    }

    public function unpublish(Notice $notice): Notice
    {
        $notice->forceFill([
            'status' => Notice::STATUS_DRAFT,
            'published_at' => null,
        ])->save();

        return $notice->fresh(['attachments', 'author']);
    }

    public function resendEmail(Notice $notice): Notice
    {
        if (! $notice->isPublished()) {
            $this->publish($notice, sendEmail: true);

            return $notice->fresh(['attachments', 'author']);
        }

        $this->notifications->noticePublished($notice);
        $notice->forceFill(['emailed_at' => now()])->save();

        return $notice->fresh(['attachments', 'author']);
    }

    public function delete(Notice $notice): void
    {
        DB::transaction(function () use ($notice) {
            $notice->loadMissing('attachments');

            foreach ($notice->attachments as $attachment) {
                $this->deleteAttachmentFile($attachment);
            }

            $notice->delete();
        });
    }

    public function deleteAttachment(NoticeAttachment $attachment): void
    {
        $this->deleteAttachmentFile($attachment);
        $attachment->delete();
    }

    public function markRead(Notice $notice, User $user): void
    {
        if (! $notice->isPublished()) {
            return;
        }

        DB::table('notice_reads')->updateOrInsert(
            [
                'notice_id' => $notice->id,
                'user_id' => $user->id,
            ],
            [
                'read_at' => now(),
                'updated_at' => now(),
                'created_at' => now(),
            ],
        );
    }

    public function unreadCountFor(User $user): int
    {
        return Notice::query()
            ->published()
            ->whereDoesntHave('readers', fn ($q) => $q->where('user_id', $user->id))
            ->count();
    }

    /**
     * @param  list<UploadedFile>  $files
     */
    private function storeAttachments(Notice $notice, array $files): void
    {
        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $path = $file->store('notice_attachments/'.$notice->id, 'local');

            NoticeAttachment::query()->create([
                'notice_id' => $notice->id,
                'disk' => 'local',
                'path' => $path,
                'mime' => $file->getMimeType(),
                'size' => (int) $file->getSize(),
                'original_name' => $file->getClientOriginalName(),
            ]);
        }
    }

    private function deleteAttachmentFile(NoticeAttachment $attachment): void
    {
        Storage::disk($attachment->disk)->delete($attachment->path);
    }
}
