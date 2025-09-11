<div>
    <x-slot name="header">
        <h2 class="h4 font-weight-bold">
            Users List
        </h2>
    </x-slot>

    <div class="card shadow-sm mb-4">
        <div class="card-body d-flex justify-content-end">
            <div class="input-group searchbar" style="max-width: 300px;">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input wire:model.live.debounce.300ms="search" type="text" class="form-control" placeholder="Search by name or email...">
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                   
                        <th wire:click="setSortBy('id')" style="cursor: pointer;">
                            ID
                            @if($sortBy === 'id')<i class="bi bi-arrow-{{ $sortDir === 'ASC' ? 'up' : 'down' }}"></i>@endif
                        </th>

                   
                        <th wire:click="setSortBy('name')" style="cursor: pointer;">
                            Name
                            @if($sortBy === 'name')<i class="bi bi-arrow-{{ $sortDir === 'ASC' ? 'up' : 'down' }}"></i>@endif
                        </th>

                     
                        <th>Email</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted py-4">No users found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
        <div class="card-footer bg-white d-flex justify-content-end">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>