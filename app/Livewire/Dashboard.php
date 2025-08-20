<?php

namespace App\Livewire;

use Livewire\Component;

class Dashboard extends Component
{
    public $activeTab = 'dashboard';
    public $darkMode = false;

    public function mount()
    {
        // Check if user has dark mode preference in session
        $this->darkMode = session('dark_mode', false);
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function toggleDarkMode()
    {
        $this->darkMode = !$this->darkMode;
        session(['dark_mode' => $this->darkMode]);
        $this->dispatch('dark-mode-toggled', $this->darkMode);
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}