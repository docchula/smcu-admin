<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\VestaService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DocumentController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): \Inertia\Response {
        $searchKeyword = $request->input('search');

        return Inertia::render('DocumentIndex', [
            'list' => User::where('id', 0)->paginate(15)->withQueryString(),
            'keyword' => $searchKeyword
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): \Inertia\Response {
        return $this->edit($request, new User([]));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        return response('Not Implemented', 501);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
        return response('Not Implemented', 501);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, User $item): \Inertia\Response {
        /** @var User $user */
        return Inertia::render('DocumentCreate', [
            'item' => $item,
            'static_departments' => [['id' => 1, 'name' => 'ฝ่ายเทคโนโลยีสารสนเทศ'], ['id' => 2, 'name' => 'ฝ่ายวิชาการ']]
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        //
    }
}
