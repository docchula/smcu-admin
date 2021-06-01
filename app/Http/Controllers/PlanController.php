<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class PlanController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): \Inertia\Response {
        return Inertia::render('PlanIndex', [
            'list' => [],
        ]);
    }
}
