<?php

namespace App\Http\Controllers\Investor;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use App\Models\NoticeAttachment;
use App\Services\NoticeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NoticeController extends Controller
{
    public function __construct(private readonly NoticeService $notices) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $search = trim($request->string('q')->toString());
        $filter = $request->string('filter')->toString();

        $query = Notice::query()
            ->published()
            ->with(['author', 'attachments'])
            ->withCount('attachments')
            ->boardOrder();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%'.$search.'%')
                    ->orWhere('body', 'like', '%'.$search.'%');
            });
        }

        if ($filter === 'unread') {
            $query->whereDoesntHave('readers', fn ($q) => $q->where('user_id', $user->id));
        } elseif ($filter === 'pinned') {
            $query->where('is_pinned', true);
        }

        $notices = $query
            ->paginate(12)
            ->withQueryString();

        $readIds = DB::table('notice_reads')
            ->where('user_id', $user->id)
            ->whereIn('notice_id', $notices->getCollection()->pluck('id'))
            ->pluck('notice_id')
            ->all();

        $notices->getCollection()->transform(function (Notice $notice) use ($readIds) {
            $notice->setAttribute('is_read', in_array($notice->id, $readIds, true));

            return $notice;
        });

        return view('notices.index', [
            'notices' => $notices,
            'search' => $search,
            'filter' => $filter,
            'unreadCount' => $this->notices->unreadCountFor($user),
            'canManage' => $user->isAdmin(),
        ]);
    }

    public function show(Request $request, Notice $notice): View
    {
        if (! $notice->isPublished() && ! $request->user()->isAdmin()) {
            abort(404);
        }

        $notice->load(['author', 'attachments']);
        $this->notices->markRead($notice, $request->user());
        $notice->setAttribute('is_read', true);

        return view('notices.show', [
            'notice' => $notice,
            'canManage' => $request->user()->isAdmin(),
        ]);
    }

    public function downloadAttachment(Request $request, Notice $notice, NoticeAttachment $attachment): StreamedResponse
    {
        if ($attachment->notice_id !== $notice->id) {
            abort(404);
        }

        if (! $notice->isPublished() && ! $request->user()->isAdmin()) {
            abort(404);
        }

        $this->notices->markRead($notice, $request->user());

        return Storage::disk($attachment->disk)->download($attachment->path, $attachment->original_name);
    }
}
