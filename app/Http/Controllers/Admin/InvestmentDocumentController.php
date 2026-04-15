<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Investment;
use App\Models\InvestmentDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InvestmentDocumentController extends Controller
{
    public function store(Request $request, Investment $investment): RedirectResponse
    {
        $this->authorize('manageDocuments', $investment);

        $request->validate([
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:12288'],
        ]);

        $uploaded = $request->file('file');
        $path = $uploaded->store('investment_documents/'.$investment->id, 'local');

        InvestmentDocument::create([
            'investment_id' => $investment->id,
            'disk' => 'local',
            'path' => $path,
            'mime' => $uploaded->getMimeType(),
            'original_name' => $uploaded->getClientOriginalName(),
        ]);

        return back()->with('status', 'Document uploaded.');
    }

    public function destroy(Investment $investment, InvestmentDocument $document): RedirectResponse
    {
        $this->authorize('manageDocuments', $investment);

        if ($document->investment_id !== $investment->id) {
            abort(404);
        }

        Storage::disk($document->disk)->delete($document->path);
        $document->delete();

        return back()->with('status', 'Document removed.');
    }

    public function download(Investment $investment, InvestmentDocument $document): StreamedResponse
    {
        $this->authorize('view', $investment);

        if ($document->investment_id !== $investment->id) {
            abort(404);
        }

        return Storage::disk($document->disk)->download($document->path, $document->original_name);
    }
}
