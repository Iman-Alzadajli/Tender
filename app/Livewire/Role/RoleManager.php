<?php

namespace App\Livewire\Role;

use Livewire\Attributes\Layout;
use Livewire\Component;



#[Layout('layouts.app')]
class RoleManager extends Component
{
    public function render()
    {
        return view('livewire.role.role-manager');
    }
}
