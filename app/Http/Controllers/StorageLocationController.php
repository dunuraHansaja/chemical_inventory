<?php

namespace App\Http\Controllers;

use App\Models\StorageLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StorageLocationController extends Controller
{
    public function index(Request $request): View
    {
        $query = StorageLocation::query();

        if ($request->filled('search')) {
            $search = '%'.$request->input('search').'%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                  ->orWhere('address', 'like', $search);
            });
        }

        $storageLocations = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('storage-locations.index', compact('storageLocations'));
    }

    public function create(): View
    {
        return view('storage-locations.form', [
            'storageLocation' => new StorageLocation(['is_active' => true]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $storageLocation = StorageLocation::create($this->validated($request));

        return redirect()->route('storage-locations.index')
            ->with('success_title', 'Storage Location Added')
            ->with('success', "The storage location “{$storageLocation->name}” was added successfully.");
    }

    public function edit(StorageLocation $storageLocation): View
    {
        return view('storage-locations.form', compact('storageLocation'));
    }

    public function update(Request $request, StorageLocation $storageLocation): RedirectResponse
    {
        $storageLocation->update($this->validated($request, $storageLocation));

        return redirect()->route('storage-locations.index')
            ->with('success_title', 'Storage Location Updated')
            ->with('success', "The storage location “{$storageLocation->name}” was updated successfully.");
    }

    public function destroy(StorageLocation $storageLocation): RedirectResponse
    {
        $storageLocation->delete();

        return redirect()->route('storage-locations.index')
            ->with('warning', "The storage location “{$storageLocation->name}” was deleted.");
    }

    private function validated(Request $request, ?StorageLocation $storageLocation = null): array
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:191',
                Rule::unique('storage_locations', 'name')->ignore($storageLocation?->id),
            ],
            'address' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}

