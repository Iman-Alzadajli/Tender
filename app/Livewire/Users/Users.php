<?php

namespace App\Livewire\Users;

use Livewire\Component;
use App\Models\User;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.app', ['header' => 'Users List'])]
class Users extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // خصائص الواجهة
    public string $search = '';

    // خصائص الفرز
    public string $sortBy = 'id';
    public string $sortDir = 'ASC';

    // دالة الفرز

    public function setSortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDir = ($this->sortDir === 'ASC') ? 'DESC' : 'ASC';
        } else {
            $this->sortBy = $field;
            $this->sortDir = 'ASC';
        }
    }


    // إعادة تعيين الصفحة عند البحث
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    // دالة العرض الرئيسية
    public function render()
    {
        $users = User::query()
            ->when($this->search, function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortBy, $this->sortDir) 
            ->paginate(5);

        return view('livewire.users.users', [
            'users' => $users
        ]);
    }
}
