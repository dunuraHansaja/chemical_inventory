@extends('layouts.app')
@section('title', 'Roles & permissions')
@section('subtitle', 'Create custom roles and choose the inventory actions each role can perform.')
@section('actions')<a class="button button-primary" href="{{ route('settings.roles.create') }}">＋ Create role</a>@endsection
@section('content')
    <section class="panel">
        <div class="table-wrap"><table>
            <thead><tr><th>ROLE</th><th>USERS</th><th>PERMISSIONS</th><th>TYPE</th><th></th></tr></thead>
            <tbody>
            @forelse($roles as $role)
                <tr>
                    <td><strong class="table-primary">{{ $role->name }}</strong></td>
                    <td>{{ $role->users_count }}</td>
                    <td>{{ $role->is_system ? 'All permissions' : count($role->permissions ?? []) }}</td>
                    <td><span class="badge {{ $role->is_system ? 'badge-success' : 'badge-neutral' }}">{{ $role->is_system ? 'System' : 'Custom' }}</span></td>
                    <td class="actions-cell">
                        @if(!$role->is_system)
                            <a class="row-action" href="{{ route('settings.roles.edit', $role) }}">Edit</a>
                            <form method="POST" action="{{ route('settings.roles.destroy', $role) }}" data-confirm="Delete the role “{{ $role->name }}”? This cannot be undone." data-confirm-title="Delete role?" data-confirm-action="Delete role" data-confirm-tone="danger">@csrf @method('DELETE')<button class="row-action danger-link" type="submit">Delete</button></form>
                        @else
                            <span class="table-secondary">Protected role</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5"><div class="empty-state"><strong>No roles available</strong><p>Create a role to assign permissions to user accounts.</p></div></td></tr>
            @endforelse
            </tbody>
        </table></div>
        <div class="pagination-wrap">{{ $roles->links() }}</div>
    </section>
@endsection
