<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')] // <-- أضف هذا السطر
class OtherTenderPlatform extends Component
{
    public function render()
    {
        return view('livewire.other-tender-platform');
    }
}
