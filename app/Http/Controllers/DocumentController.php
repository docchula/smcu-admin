<?php

namespace App\Http\Controllers;

use App\Helper;
use App\Models\Department;
use App\Models\Document;
use App\Models\Personnel;
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
        $query = Document::searchQuery($keyword);

        return Inertia::render('DocumentIndex', [
            'list' => $query->orderByDesc('year')->orderByDesc('number')->paginate(20)->withQueryString(),
            'keyword' => $keyword
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): Response {
        $document = new Document([
            'tag' => $request->input('tag'),
        ]);
        if ($request->input('tag') == 'summary' and $request->input('project_id')) {
            $document->project()->associate($request->input('project_id'));
            $document->title = 'รายงานผลการดำเนินงานโครงการ'.$document->project->name;
            $document->recipient = 'รองคณบดีด้านกิจการนิสิต';
            $document->department_id = $document->project->department_id;
        }

        return $this->edit($request, $document);
    }

    /**
     * Store a newly created resource in storage.
     * @throws \Throwable
     */
    public function store(Request $request): \Symfony\Component\HttpFoundation\Response {
        return $this->update($request, new Document());
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Document $document): Response {
        $isAuthorized = $request->user()->can('update-document', $document);
        $document->load(['user:id,name', 'department:id,name,group', 'project:id,name,advisor']);
        if ($document->user) {
            unset($document->user->id);
        }

        // Get signers
        $signers = [];
        $personnel = Personnel::getYear($document->year);
        if ($document->department?->group) {
            if ($person = $personnel->where('department_id', $document->department_id)->first()) {
                $i = 0;
                do {
                    $signers[] = $person;
                    $person = $person->supervisor ? $personnel->where('id', $person->supervisor)->first() : null;
                    $i++;
                } while (!empty($person) and $i < 10);
            }
        }

        return Inertia::render('DocumentShow', [
            'item' => $document,
            'can' => [
                'download-document' => $isAuthorized or $request->user()->can('download-action'),
                'update-document' => $isAuthorized and ($document->created_at->diffInDays() <= 14),
            ],
            'has_attachment' => $isAuthorized && !empty($document->attachment_path),
            'has_approved' => $isAuthorized && !empty($document->approved_path),
            'signers' => $signers,
        ]);
    }

    public function download(Document $document): StreamedResponse {
        $this->authorize('update-document', $document);
        abort_if(empty($document->attachment_path), 404);
        abort_if(Storage::missing($document->attachment_path), 404);

        return Storage::response(
            $document->attachment_path,
            'SMCU '.$document->number.'-'.$document->year.' '.substr($document->title, 0, 25).' Draft.'.Str::after($document->attachment_path, '.')
        );
    }

    public function downloadApproved(Request $request, Document $document): StreamedResponse|\Illuminate\Http\Response
    {
        $this->authorize('update-document', $document);
        abort_if(empty($document->approved_path), 404);
        abort_if(Storage::missing($document->approved_path), 404);

        if ($request->filled('download')) {
            return Storage::response(
                $document->approved_path,
                'SMCU '.$document->number.'-'.$document->year.' Signed.'.Str::after($document->approved_path, '.')
            );
        } else {
            // This only works with PDF
            return response()->view('base64-pdf-viewer', ['encoded' => base64_encode(Storage::get($document->approved_path))]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Document $document): Response
    {
        $this->authorize('update-document', $document);
        return Inertia::render('DocumentCreate', [
            'item' => $document->load('project', 'project.department'),
            'static_departments' => Department::optionList(),
            'is_admin' => $request->user()->can('admin-action'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     * @throws \Throwable
     */
    public function update(Request $request, Document $document): \Symfony\Component\HttpFoundation\Response {
        $this->validate($request, [
            'title' => 'required|filled|string|min:5|max:255',
            'recipient' => 'required|filled|string|max:255',
            'department_id' => 'required|integer|min:1',
            'amount' => 'nullable|integer|min:1|max:500',
            'tag' => 'nullable|string|in:approval,summary',
            'attachment' => 'nullable|file|mimes:pdf,docx,doc|max:20000', // File size limit: 20,000 KB
            'approved_attachment' => 'nullable|file|mimes:pdf|max:20000',
            'project_id' => 'nullable|required_with:tag|integer|min:1|exists:projects,id',
            'objectives' => 'required_if:tag,summary|array',
            'expense' => 'nullable|array',
        ]);
        $this->authorize('update-document', $document);
        $document->fill($request->except('objectives', 'expense'));
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

        // Save project summary info
        if ($request->input('tag') == 'summary' and $request->input('project_id')) {
            $document->project()->associate($request->input('project_id'));
            $document->project->objectives = $request->input('objectives');
            if ($document->project->expense) {
                $this->validate($request, [
                    'expense' => 'required|array',
                ]);
                $document->project->expense = $request->input('expense');
            }
            $document->project->saveOrFail();

            if ($request->filled('generate_document')) {
                return Inertia::location(route('projects.generateSummaryDocument', ['project' => $document->project->id]));
            }
        }

        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')
                ->storeAs(
                    'documents/'.$document->year,
                    $document->id.'_'.$document->number.'-'.$document->year.'_Draft.'.$request->file('attachment')->guessExtension()
                );
            if ($document->attachment_path and $document->attachment_path != $path) {
                Storage::delete($document->attachment_path);
            }
            $document->attachment_path = $path;
        }
        if ($request->hasFile('approved_attachment')) {
            if ($document->attachment_path) {
                Storage::delete($document->attachment_path);
            }
            $document->approved_path = $request->file('approved_attachment')
                ->storeAs(
                    'documents/'.$document->year,
                    $document->id.'_'.$document->number.'-'.$document->year.'_Signed.'.$request->file('approved_attachment')->guessExtension()
                );
            $document->status = Document::STATUS_APPROVED;
        }
        $document->saveOrFail();

        return redirect()
            ->route('documents.show', ['document' => $document->id])
            ->with('flash.banner', 'บันทึกเอกสารแล้ว')
            ->with('flash.bannerStyle', 'success');
    }
}
