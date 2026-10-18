<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::withCount('materials')->orderBy('name')->paginate(20);

        return view('categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('categories.form', ['category' => new Category(['is_active' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $category = Category::create($this->validated($request));

        return redirect()->route('categories.index')
            ->with('success_title', 'Category Added')
            ->with('success', "The category “{$category->name}” was added successfully.");
    }

    public function edit(Category $category): View
    {
        return view('categories.form', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $category->update($this->validated($request, $category));

        return redirect()->route('categories.index')->with('success', 'Category updated.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->materials()->exists()) {
            return back()->withErrors(['category' => 'This category is in use. Reassign its materials before deleting it.']);
        }

        $category->delete();

        return redirect()->route('categories.index')
            ->with('warning', "The category “{$category->name}” was deleted.");
    }

    private function validated(Request $request, ?Category $category = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:191', Rule::unique('categories', 'name')->ignore($category?->id)],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
