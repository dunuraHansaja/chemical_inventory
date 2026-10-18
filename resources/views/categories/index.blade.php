@extends('layouts.app')
@section('title', 'Categories')
@section('subtitle', 'Organize chemical materials into useful groups.')
@section('actions')@if(auth()->user()->hasPermission('categories.create'))<a class="button button-primary" href="{{ route('categories.create') }}">＋ Add category</a>@endif @endsection
@section('content')
<section class="panel"><div class="table-wrap"><table><thead><tr><th>CATEGORY</th><th>DESCRIPTION</th><th>MATERIALS</th><th>STATUS</th><th></th></tr></thead><tbody>
@forelse($categories as $category)<tr><td><strong class="table-primary">{{ $category->name }}</strong></td><td>{{ $category->description ?: '—' }}</td><td>{{ $category->materials_count }}</td><td><span class="badge {{ $category->is_active ? 'badge-success' : 'badge-neutral' }}">{{ $category->is_active ? 'Active' : 'Inactive' }}</span></td><td class="actions-cell">@if(auth()->user()->hasPermission('categories.edit'))<a class="row-action" href="{{ route('categories.edit', $category) }}">Edit</a>@endif @if(auth()->user()->hasPermission('categories.delete'))<form method="POST" action="{{ route('categories.destroy', $category) }}" data-confirm="This will permanently delete the category “{{ $category->name }}”. This action cannot be undone." data-confirm-title="Delete category?" data-confirm-action="Delete category" data-confirm-tone="danger">@csrf @method('DELETE')<button class="row-action danger-link" type="submit">Delete</button></form>@endif</td></tr>
@empty<tr><td colspan="5"><div class="empty-state"><strong>No categories yet</strong><p>Create categories to group materials.</p></div></td></tr>@endforelse
</tbody></table></div><div class="pagination-wrap">{{ $categories->links() }}</div></section>
@endsection
