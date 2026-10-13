<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Document;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Proposal forms, NIC copies, receipts — stored privately, only the owner can download */
class DocumentController extends Controller
{
    public function store(Request $request, Client $client): RedirectResponse
    {
        $this->authorize('update', $client);
        $data = $request->validate([
            'file' => ['required', 'file', 'max:'.config('rakawarana.max_upload_kb'), 'mimes:'.implode(',', config('rakawarana.allowed_mimes'))],
            'name' => ['nullable', 'string', 'max:160'],
            'insurance_policy_id' => ['nullable', 'string', Rule::exists('insurance_policies', 'id')->where('client_id', $client->id)],
        ]);
        $file = $request->file('file');
        $path = $file->store('documents/'.$request->user()->id, 'local');
        $doc = new Document([
            'client_id' => $client->id,
            'insurance_policy_id' => $data['insurance_policy_id'] ?? null,
            'name' => ($data['name'] ?? null) ?: $file->getClientOriginalName(),
            'path' => $path,
            'mime' => $file->getMimeType() ?? 'application/octet-stream',
            'size' => $file->getSize(),
        ]);
        $doc->user_id = $request->user()->id;
        $doc->save();
        Audit::log('document.uploaded', $doc, $doc->name);

        return back()->with('success', __('Document uploaded.'));
    }

    public function show(Document $document): StreamedResponse
    {
        $this->authorize('view', $document);
        Audit::log('document.downloaded', $document, $document->name);
        $ext = pathinfo($document->path, PATHINFO_EXTENSION);
        $name = preg_replace('/[^\pL\pN ._-]+/u', '', $document->name) ?: 'document';
        if ($ext && ! str_ends_with(strtolower($name), '.'.strtolower($ext))) {
            $name .= '.'.$ext;
        }

        return Storage::disk('local')->download($document->path, $name);
    }

    public function destroy(Document $document): RedirectResponse
    {
        $this->authorize('delete', $document);
        Storage::disk('local')->delete($document->path);
        $document->delete();
        Audit::log('document.deleted', $document, $document->name);

        return back()->with('success', __('Document deleted.'));
    }
}
