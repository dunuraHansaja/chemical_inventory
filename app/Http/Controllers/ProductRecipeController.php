<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProductRecipeController extends Controller
{
    public function index()
    {
        return redirect()->route('production.index');
    }

    public function create()
    {
        return redirect()->route('production.index');
    }

    public function store(Request $request)
    {
        return redirect()->route('production.index');
    }

    public function edit($id)
    {
        return redirect()->route('production.index');
    }

    public function update(Request $request, $id)
    {
        return redirect()->route('production.index');
    }

    public function destroy($id)
    {
        return redirect()->route('production.index');
    }
}

