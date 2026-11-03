@extends('layouts.app')
@section('title', 'Storage Locations')
@section('subtitle', 'Manage physical storage locations, warehouses, and addresses.')
@section('actions')
    @if(auth()->user()->hasPermission('storage_locations.create'))
        <a class="button button-primary" href="{{ route('storage-locations.create') }}">＋ Add Storage Location</a>
    @endif
@endsection

@section('content')
<section class="panel">
    <div class="toolbar">
        <form method="GET" class="search-form">
            <input name="search" value="{{ request('search') }}" placeholder="Search storage location or address…" aria-label="Search storage locations">
            <button class="button button-secondary">Search</button>
        </form>
        <span class="result-count">{{ $storageLocations->total() }} {{ Str::plural('storage location', $storageLocations->total()) }}</span>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>STORAGE LOCATION NAME</th>
                    <th>ADDRESS</th>
                    <th>STATUS</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($storageLocations as $location)
                    <tr>
                        <td>
                            <strong class="table-primary">{{ $location->name }}</strong>
                        </td>
                        <td>{{ $location->address ?: '—' }}</td>
                        <td>
                            <span class="badge {{ $location->is_active ? 'badge-success' : 'badge-neutral' }}">
                                {{ $location->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="actions-cell">
                            @if(auth()->user()->hasPermission('storage_locations.edit'))
                                <a class="row-action" href="{{ route('storage-locations.edit', $location) }}">Edit</a>
                            @endif
                            @if(auth()->user()->hasPermission('storage_locations.delete'))
                                <form method="POST"
                                      action="{{ route('storage-locations.destroy', $location) }}"
                                      data-confirm="This will permanently delete the storage location “{{ $location->name }}”. This action cannot be undone."
                                      data-confirm-title="Delete storage location?"
                                      data-confirm-action="Delete storage location"
                                      data-confirm-tone="danger">
                                    @csrf
                                    @method('DELETE')
                                    <button class="row-action danger-link" type="submit">Delete</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">
                            <div class="empty-state">
                                <strong>No storage locations yet</strong>
                                <p>Create storage locations to organize where inventory is housed.</p>
                                @if(auth()->user()->hasPermission('storage_locations.create'))
                                    <a class="button button-primary" href="{{ route('storage-locations.create') }}">Add Storage Location</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrap">
        {{ $storageLocations->links() }}
    </div>
</section>
@endsection

