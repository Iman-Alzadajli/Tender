<?php

namespace App\Livewire;

use Livewire\Component;

class DashboardOverview extends Component
{
    public function render()
    {
        // Mock data for demonstration
        $stats = [
            'total_tenders' => 25,
            'active_tenders' => 12,
            'pending_tenders' => 8,
            'completed_tenders' => 5
        ];

        $recentTenders = [
            [
                'id' => 'TND-2024-001',
                'name' => 'Tender Project 1',
                'client' => 'Dubai Municipality',
                'status' => 'Active',
                'date' => '2024-01-15'
            ],
            [
                'id' => 'TND-2024-002',
                'name' => 'Tender Project 2',
                'client' => 'Emirates Airlines',
                'status' => 'Pending',
                'date' => '2024-01-10'
            ],
            [
                'id' => 'TND-2024-003',
                'name' => 'Tender Project 3',
                'client' => 'Ministry of Health',
                'status' => 'Completed',
                'date' => '2024-01-05'
            ]
        ];

        return view('livewire.dashboard-overview', compact('stats', 'recentTenders'));
    }
}