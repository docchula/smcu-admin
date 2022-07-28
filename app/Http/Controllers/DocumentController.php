<?php

namespace App\Http\Controllers;

use App\Helper;
use App\Models\Department;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class DocumentController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): \Inertia\Response {
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
    public function create(Request $request): \Inertia\Response {
        return $this->edit(new Document([]));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse {
        return $this->update($request, new Document());
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Document $document): \Inertia\Response {
        $isAuthorized = $request->user()->can('update-document', $document);
        $document->can = [
            'update-document' => $isAuthorized
        ];
        $document->has_attachment = $isAuthorized && !empty($document->attachment_path);
        $document->load(['user:id,name', 'department:id,name', 'project:id,name']);
        if ($document->user) {
            unset($document->user->id);
        }
        return Inertia::render('DocumentShow', [
            'item' => $document
        ]);
    }

    public function download(Document $document): \Symfony\Component\HttpFoundation\StreamedResponse {
        $this->authorize('update-document', $document);
        abort_if(empty($document->attachment_path), 404);
        abort_if(!file_exists($document->attachment_path), 404);

        return Storage::download(
            $document->attachment_path,
            'สพจ ' . $document->number . '-' . $document->year . ' ' . substr($document->title, 0, 25)
        );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Document $document): \Inertia\Response {
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
    public function update(Request $request, Document $document): \Illuminate\Http\RedirectResponse {
        $this->validate($request, [
            'title' => 'required|filled|string|min:5|max:255',
            'recipient' => 'required|filled|string|max:255',
            'department_id' => 'required|integer|min:1',
            'amount' => 'nullable|integer|min:1|max:500',
            'attachment' => 'nullable|file|mimes:pdf,docx,doc|max:20000' // File size limit: 20,000 KB
        ]);
        $this->authorize('update-document', $document);
        $document->fill($request->all());
        if (empty($document->user_id)) {
            $document->user_id = Auth::id();
        }
        if (!$document->id) {
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

        return redirect()->route('documents.index')->with('flash.banner', 'บันทึกเอกสาร เลขที่ ' . $document->number . '/' . $document->year . ' แล้ว')->with('flash.bannerStyle', 'success');
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
