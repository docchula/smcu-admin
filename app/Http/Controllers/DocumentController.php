<?php

namespace App\Http\Controllers;

use App\Helper;
use App\Models\Department;
use App\Models\Document;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response {
        $keyword = $request->input('search');
        $query = Document::query()->with(['department']);
        if (empty($keyword)) {
            $query->where('year', Helper::buddhistYear());
        } elseif (preg_match("/^[\/\d]+/", $keyword)) {
            $parts = explode('/', $keyword, 2);
            if (!empty($parts[0])) {
                $query->where('number', $parts[0]);
            }
            if (!empty($parts[1])) {
                $query->where('year', $parts[1]);
            } elseif (strlen($parts[0]) === 4) {
                $query->orWhere('year', $parts[0]);
            }
        } else {
            $query->where('title', 'LIKE', '%' . $keyword . '%');
        }

        return Inertia::render('DocumentIndex', [
            'list' => $query->orderByDesc('year')->orderByDesc('number')->paginate(20)->withQueryString(),
            'keyword' => $keyword
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): Response {
        return $this->edit(new Document([]));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse {
        return $this->update($request, new Document());
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Document $document): Response {
        $isAuthorized = $request->user()->can('update-document', $document);
        $document->can = [
            'download-document' => $isAuthorized,
            'update-document' => $isAuthorized AND $document->created_at->isCurrentWeek(),
        ];
        $document->has_attachment = $isAuthorized && !empty($document->attachment_path);
        $document->has_approved = $isAuthorized && !empty($document->approved_path);
        $document->load(['user:id,name', 'department:id,name', 'project:id,name']);
        if ($document->user) {
            unset($document->user->id);
        }
        return Inertia::render('DocumentShow', [
            'item' => $document
        ]);
    }

    public function download(Document $document): StreamedResponse {
        $this->authorize('update-document', $document);
        abort_if(empty($document->attachment_path), 404);
        abort_if(Storage::missing($document->attachment_path), 404);

        return Storage::response(
            $document->attachment_path,
            'SMCU '.$document->number.'-'.$document->year.' '.substr($document->title, 0, 25).' Draft.'.Str::after($document->approved_path, '.')
        );
    }

    public function downloadApproved(Document $document): \Illuminate\Http\Response
    {
        $this->authorize('update-document', $document);
        abort_if(empty($document->approved_path), 404);
        abort_if(Storage::missing($document->approved_path), 404);

        // This only works with PDF
        return response()->view('base64-pdf-viewer', ['encoded' => base64_encode(Storage::get($document->approved_path))]);
        // 'SMCU '.$document->number.'-'.$document->year.' '.substr($document->title, 0, 25).' Signed.'.Str::after($document->approved_path, '.')
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Document $document): Response {
        $this->authorize('update-document', $document);
        return Inertia::render('DocumentCreate', [
            'item' => $document->load('project', 'project.department'),
            'static_departments' => Department::optionList()
        ]);
    }

    /**
     * Update the specified resource in storage.
     * @throws \Throwable
     */
    public function update(Request $request, Document $document): RedirectResponse {
        $this->validate($request, [
            'title' => 'required|filled|string|min:5|max:255',
            'recipient' => 'required|filled|string|max:255',
            'department_id' => 'required|integer|min:1',
            'amount' => 'nullable|integer|min:1|max:500',
            'tag' => 'nullable|string|in:approval,summary',
            'attachment' => 'nullable|file|mimes:pdf,docx,doc|max:20000' // File size limit: 20,000 KB
        ]);
        $this->authorize('update-document', $document);
        $document->fill($request->all());
        if (empty($document->user_id)) {
            $document->user_id = Auth::id();
        }
        if (!$document->id) {
            if (str_starts_with($document->title, 'โครงการ')) {
                $document->title = Str::replaceFirst('โครงการ', '', $document->title);
            }
            $document->year = Helper::buddhistYear();
            $previousRecord = Document::latestOfYear($document->year);
            $document->number = $previousRecord ? (($previousRecord->number_to ?? $previousRecord->number) + 1) : 1;
            $amount = $request->input('amount', 1);
            if ($amount > 1) {
                $document->number_to = $document->number + $amount - 1;
            }
        }
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('documents');
            if ($document->attachment_path) {
                Storage::delete($document->attachment_path);
            }
            $document->attachment_path = $path;
        }
        $document->saveOrFail();

        return redirect()
            ->route('documents.show', ['document' => $document->id])
            ->with('flash.banner', 'บันทึกเอกสารแล้ว')
            ->with('flash.bannerStyle', 'success');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        return response('Not Implemented', 501);
    }
}
