<?php

namespace App\Http\Controllers;

use App\Helper;
use App\Models\Department;
use App\Models\Personnel;
use Docchula\VestaClient\Facades\VestaClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class PersonnelController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->input('year', Personnel::getYearList()[0]);

        return Inertia::render('PersonnelIndex', [
            'list' => Personnel::query()->with('department')->where('year', $year)->orderBy('sequence')->get(),
            'year' => $year,
            'is_admin' => $request->user()->can('admin-action'),
        ]);
    }

    public function create(Request $request)
    {
        return $this->edit(new Personnel(['year' => $request->input('year') ?? Helper::buddhistYear()]));
    }

    public function edit(Personnel $personnel)
    {
        $this->authorize('admin-action');
        $personnel->photo_url = $personnel->photo_path ? Storage::url($personnel->photo_path) : null;

        return Inertia::render('PersonnelCreate', [
            'item' => $personnel,
            'static_departments' => Department::optionList(),
        ]);
    }

    public function searchStudent(Request $request)
    {
        $this->validate($request, [
            'q' => 'required|string',
        ]);
        $this->authorize('admin-action');
        $q = $request->input('q');
        $vestaResponse = VestaClient::retrieveStudent($q, $request->user()->email,
            ['title', 'first_name', 'last_name', 'first_name_en', 'last_name_en', 'email', 'nickname']);
        if ($vestaResponse->successful()) {
            $data = $vestaResponse->json();

            return response()->json([
                'name' => $data['title'].$data['first_name'].' '.$data['last_name'],
                'name_en' => $data['first_name_en'].' '.$data['last_name_en'],
                'email' => $data['email'],
                'nickname' => $data['nickname'],
            ]);
        } else {
            return response()->json(['error' => 'Student not found!'], 404);
        }
    }

    public function store(Request $request)
    {
        return $this->update($request, new Personnel());
    }

    public function update(Request $request, Personnel $personnel)
    {
        $this->validate($request, [
            'name' => 'required|filled|string|min:5|max:250',
            'name_en' => 'nullable|string|min:5|max:250',
            'position' => 'required|filled|string|min:5|max:250',
            'position_en' => 'nullable|string|min:5|max:250',
            'email' => 'required|email|max:100',
            'department_id' => 'required|integer|min:1',
            'year' => 'required|integer|min:2480|max:2700',
            'sequence' => 'nullable|integer',
            'supervisor' => 'nullable|integer',
            'attachment' => 'nullable|file|mimetypes:image/jpeg,image/webp,image/avif,image/png',
        ]);
        $this->authorize('admin-action');
        if ($personnel->id and $request->input('supervisor') == $personnel->id) {
            return back()->withErrors(['supervisor' => 'Invalid ID']);
        }
        $personnel->fill($request->all());

        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->storePublicly('personnels', 'public');
            if ($personnel->photo_path) {
                Storage::disk('public')->delete($personnel->photo_path);
            }
            $personnel->photo_path = $path;
        }
        $personnel->saveOrFail();

        return redirect()
            ->route('personnels.index', ['year' => $personnel->year])
            ->with('flash.banner', 'Personnel #'.$personnel->id.' saved!')
            ->with('flash.bannerStyle', 'success');
    }

    public function show($id)
    {
    }

    public function destroy($id)
    {
    }
}
