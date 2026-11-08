<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use App\Models\NoticeAttachment;
use App\Services\NoticeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NoticeController extends Controller
{
    public function __construct(private readonly NoticeService $notices) {}

    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $search = trim($request->string('q')->toString());

        $query = Notice::query()
            ->with(['author', 'attachments'])
            ->withCount('attachments')
            ->latest('updated_at');

        if (in_array($status, [Notice::STATUS_DRAFT, Notice::STATUS_PUBLISHED], true)) {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%'.$search.'%')
                    ->orWhere('body', 'like', '%'.$search.'%');
            });
        }

        return view('admin.notices.index', [
            'notices' => $query->paginate(15)->withQueryString(),
            'status' => $status,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('admin.notices.create', [
            'notice' => new Notice([
                'status' => Notice::STATUS_DRAFT,
                'is_pinned' => false,
                'body' => '',
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);
        $files = $request->file('attachments', []);
        $files = is_array($files) ? array_values(array_filter($files)) : [];

        $notice = $this->notices->create($request->user(), $validated, $files);

        return redirect()
            ->route('admin.notices.edit', $notice)
            ->with('status', $notice->isPublished()
                ? __('Notice published and emailed to users.')
                : __('Notice saved as draft.'));
    }

    public function edit(Notice $notice): View
    {
        $notice->load(['attachments', 'author']);

        return view('admin.notices.edit', [
            'notice' => $notice,
        ]);
    }

    public function update(Request $request, Notice $notice): RedirectResponse
    {
        $validated = $this->validated($request);
        $files = $request->file('attachments', []);
        $files = is_array($files) ? array_values(array_filter($files)) : [];

        $wasPublished = $notice->isPublished();
        $notice = $this->notices->update($notice, $validated, $files);

        $message = __('Notice updated.');
        if (! $wasPublished && $notice->isPublished()) {
            $message = __('Notice published and emailed to users.');
        }

        return redirect()
            ->route('admin.notices.edit', $notice)
            ->with('status', $message);
    }

    public function destroy(Notice $notice): RedirectResponse
    {
        $this->notices->delete($notice);

        return redirect()
            ->route('admin.notices.index')
            ->with('status', __('Notice deleted.'));
    }

    public function publish(Notice $notice): RedirectResponse
    {
        $this->notices->publish($notice, sendEmail: true);

        return back()->with('status', __('Notice published and emailed to users.'));
    }

    public function unpublish(Notice $notice): RedirectResponse
    {
        $this->notices->unpublish($notice);

        return back()->with('status', __('Notice moved to draft.'));
    }

    public function resend(Notice $notice): RedirectResponse
    {
        $this->notices->resendEmail($notice);

        return back()->with('status', __('Notice email resent to users.'));
    }

    public function destroyAttachment(Notice $notice, NoticeAttachment $attachment): RedirectResponse
    {
        if ($attachment->notice_id !== $notice->id) {
            abort(404);
        }

        $this->notices->deleteAttachment($attachment);

        return back()->with('status', __('Attachment removed.'));
    }

    public function downloadAttachment(Notice $notice, NoticeAttachment $attachment): StreamedResponse
    {
        if ($attachment->notice_id !== $notice->id) {
            abort(404);
        }

        return \Illuminate\Support\Facades\Storage::disk($attachment->disk)
            ->download($attachment->path, $attachment->original_name);
    }

    /**
     * @return array{title:string,body:string,is_pinned:bool,status:string}
     */
    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'body' => [
                'required',
                'string',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! is_string($value) || trim(strip_tags($value)) === '') {
                        $fail(__('The notice content cannot be empty.'));
                    }
                },
            ],
            'status' => ['required', 'in:draft,published'],
            'is_pinned' => ['sometimes', 'boolean'],
            'attachments' => ['sometimes', 'array', 'max:10'],
            'attachments.*' => ['file', 'max:12288', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,webp,txt,zip'],
        ]);

        $validated['is_pinned'] = $request->boolean('is_pinned');

        return $validated;
    }
}
