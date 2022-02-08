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
            if ($parts[0]) {
                $query->where('year_number', $parts[0]);
            }
            if ($parts[1]) {
                $query->where('year', $parts[1]);
            } else {
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
        $document->can = [
            'update-document' => $request->user()->can('update-document', $document)
        ];
        return Inertia::render('DocumentShow', [
            'item' => $document->load(['user', 'department', 'project'])
        ]);
    }

    public function download(Document $document): \Symfony\Component\HttpFoundation\StreamedResponse {
        $this->authorize('update-document', $document);

        return Storage::download(
            $document->attachment_path,
            'สพจ ' . $document->number . '-' . $document->year . ' ' . substr($document->title, 0, 25)
        );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Document $document): \Inertia\Response {
        return Inertia::render('DocumentCreate', [
            'item' => $document,
            'static_departments' => Department::optionList()
        ]);
    }

    /**
     * Update the specified resource in storage.
     * @throws \Throwable
     */
    public function update(Request $request, Document $item): \Illuminate\Http\RedirectResponse {
        $this->validate($request, [
            'title' => 'required|filled|string|min:5|max:255',
            'recipient' => 'required|filled|string|max:255',
            'department_id' => 'required|integer|min:1',
            'amount' => 'nullable|integer|min:1|max:500',
            'attachment' => 'nullable|file|mimes:pdf,docx,doc|max:20000' // File size limit: 20,000 KB
        ]);
        $userId = Auth::id();
        if (isset($item->user_id) and ($item->user_id != $userId)) {
            abort(403);
        }
        $item->fill($request->all());
        $item->user_id = $userId;
        if (!$item->id) {
            $item->year = Helper::buddhistYear();
            $previousRecord = Document::latestOfYear($item->year);
            $item->number = $previousRecord ? (($previousRecord->number_to ?? $previousRecord->number) + 1) : 1;
            $amount = $request->input('amount', 1);
            if ($amount > 1) {
                $item->number_to = $item->number + $amount - 1;
            }
        }
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('documents');
            if ($item->attachment_path) {
                Storage::delete($item->attachment_path);
            }
            $item->attachment_path = $path;
        }
        $item->saveOrFail();

        return redirect()->route('documents.index')->with('flash.banner', 'บันทึกเอกสาร เลขที่ ' . $item->number . '/' . $item->year . ' แล้ว')->with('flash.bannerStyle', 'success');
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
