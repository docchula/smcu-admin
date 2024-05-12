<?php

namespace App\Http\Controllers;

use App\Helper;
use App\Models\Department;
use App\Models\Personnel;
use App\Models\User;
use Cache;
use Docchula\VestaClient\Facades\VestaClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Laravel\Facades\Image;

class PersonnelController extends Controller
{
    public function index(Request $request)
    {
        $latestYear = Personnel::getYearList()[0] ?? Helper::buddhistYear();
        $year = $request->input('year', $latestYear);

        return Inertia::render('PersonnelIndex', [
            'list' => Personnel::query()->with('department')->where('year', $year)->orderBy('sequence')->get(),
            'year' => $year,
            'is_admin' => $request->user()->can('admin-action'),
            'is_outdated' => $latestYear < Helper::termYear(),
        ]);
    }

    public function indexApi(Request $request)
    {
        $yearList = Personnel::getYearList();
        return response()->json([
            'years' => $yearList,
            'personnels' => Personnel::getYear($request->input('year', $yearList[0] ?? Helper::termYear()))
                ->reject(function (Personnel $personnel) {
                    return $personnel->sequence >= 200;
                })->map(function (Personnel $personnel) {
                $personnel->photo_url = $personnel->photo_path ? Storage::disk('public')->url($personnel->photo_path) : null;
                return $personnel;
            })->setVisible(['id', 'name', 'name_en', 'position', 'position_en', 'year', 'sequence', 'photo_url']),
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
        $personnel->is_admin = $personnel->email && User::where('email', $personnel->email)->first()?->can('admin-action');

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
            'email' => 'nullable|email|max:100',
            'department_id' => 'required|integer|min:1',
            'year' => 'required|integer|min:2480|max:2700',
            'sequence' => 'nullable|integer',
            'supervisor' => 'nullable|integer',
            'attachment' => 'nullable|file|mimetypes:image/jpeg,image/webp,image/avif,image/png|max:10000',
        ]);
        $this->authorize('admin-action');
        if ($personnel->id and $request->input('supervisor') == $personnel->id) {
            return back()->withErrors(['supervisor' => 'Invalid ID']);
        }
        $personnel->fill($request->all());

        if ($request->hasFile('attachment')) {
            if (!$personnel->id) {
                $personnel->save();
            }
            $fileName = str_replace(' ', '-', strtolower(collect([
                    $personnel->id,
                    substr($personnel->name_en, 0, 15),
                ])->reject(fn($v) => empty($v))->implode('_'))).'.';

            if ($request->file('attachment')->getSize() > 50000
                or !in_array($request->file('attachment')->getMimeType(), ['image/jpeg', 'image/webp', 'image/avif'])) {
                // If image size > 100 kB -> resize and convert to webp
                $path = 'personnels/'.$fileName.'webp';
                Storage::disk('public')->put($path, Image::read($request->file('attachment'))
                    ->scaleDown(600, 700)->encode(new WebpEncoder(quality: 80)));
            } else {
                $path = $request->file('attachment')
                    ->storePubliclyAs('personnels', $fileName.$request->file('attachment')->guessExtension(), 'public');
            }
            if ($personnel->photo_path and $personnel->photo_path != $path) {
                Storage::disk('public')->delete($personnel->photo_path);
            }
            $personnel->photo_path = $path;
        }
        $personnel->saveOrFail();

        // Set admin status
        if ($personnel->email and $user = User::where('email', $personnel->email)->first()) {
            $user->roles = $request->input('is_admin') ? 'admin' : '';
            $user->save();
        }

        // Invalidate cache
        Cache::delete('personnel-year-'.$personnel->year);

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
