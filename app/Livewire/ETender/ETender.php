<?php

namespace App\Livewire\ETender;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')] 
class ETender extends Component
{
    public function render()
    {
        return view('livewire.e-tender.e-tender');
    }
}
