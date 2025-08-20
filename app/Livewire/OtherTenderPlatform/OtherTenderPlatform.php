<?php

namespace App\Livewire\OtherTenderPlatform;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')] // <-- أضف هذا السطر
class OtherTenderPlatform extends Component
{
    public function render()
    {
        return view('livewire.othertenderplatform.other-tender-platform');
    }
}
