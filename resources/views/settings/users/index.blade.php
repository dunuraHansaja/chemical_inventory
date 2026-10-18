@extends('layouts.app')
@section('title', 'User accounts')
@section('subtitle', 'Create accounts, assign one role per user, and control account access.')
@section('actions')<a class="button button-primary" href="{{ route('settings.users.create') }}">＋ Create user account</a>@endsection
@section('content')
    <section class="panel">
        <div class="table-wrap"><table>
            <thead><tr><th>USER</th><th>EMAIL</th><th>ROLE</th><th>STATUS</th><th></th></tr></thead>
            <tbody>
            @forelse($users as $user)
                <tr>
                    <td><strong class="table-primary">{{ $user->name }}</strong></td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->role?->name ?? 'No role assigned' }}</td>
                    <td><span class="badge {{ $user->is_active ? 'badge-success' : 'badge-neutral' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td class="actions-cell"><a class="row-action" href="{{ route('settings.users.edit', $user) }}">Edit account</a></td>
                </tr>
            @empty
                <tr><td colspan="5"><div class="empty-state"><strong>No user accounts</strong><p>Create an account and assign a role to grant system access.</p></div></td></tr>
            @endforelse
            </tbody>
        </table></div>
        <div class="pagination-wrap">{{ $users->links() }}</div>
    </section>
@endsection
