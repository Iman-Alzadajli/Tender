<?php

namespace App\Livewire\ContactList;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Partnership;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

#[Layout('layouts.app')]
class PartnershipsList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // --- خصائص الفلترة والبحث ---
    public string $search = '';
    public string $clientFilter = '';
    public string $clientTypeFilter = '';
    public bool $showFilters = false;

    // --- خصائص الفرز ---
    public string $sortBy = 'created_at';
    public string $sortDir = 'DESC';

    // --- خصائص نافذة التعديل ---
    public bool $showEditModal = false;
    public ?Partnership $editingPartnership = null;
    public string $p_company_name = '';
    public string $p_person_name = '';
    public string $p_phone = '';
    public string $p_email = '';
    public string $p_details = '';
    public string $original_phone = '';
    public string $original_email = '';

    // --- خصائص نافذة الحذف ---
    public bool $showDeleteModal = false;
    public ?int $deletingPartnershipId = null;
    public ?string $deletingPartnershipName = '';

    public function updating($property)
    {
        if (in_array($property, ['search', 'clientFilter', 'clientTypeFilter'])) {
            $this->resetPage();
        }
    }

    public function setSortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDir = ($this->sortDir === 'ASC') ? 'DESC' : 'ASC';
        } else {
            $this->sortBy = $field;
            $this->sortDir = 'ASC';
        }
    }

    public function editPartnership($id, $phone = null, $email = null)
    {
        // ✅ استخدام phone و email للبحث عن سجل فعلي
        $partnership = Partnership::where('phone', $phone)
            ->where('email', $email)
            ->with('partnerable')
            ->first();

        if (!$partnership) {
            session()->flash('error', 'Partnership not found.');
            return;
        }

        $this->editingPartnership = $partnership;
        $this->p_company_name = $partnership->company_name;
        $this->p_person_name = $partnership->person_name;
        $this->p_phone = $partnership->phone;
        $this->p_email = $partnership->email;
        $this->p_details = $partnership->details;
        
        // حفظ القيم الأصلية لتحديد السجلات المكررة
        $this->original_phone = $partnership->phone;
        $this->original_email = $partnership->email;
        
        $this->resetValidation();
        $this->showEditModal = true;
    }

    public function updatePartnership()
    {
        $validatedData = $this->validate([
            'p_company_name' => 'required|string|max:255',
            'p_person_name' => 'required|string|max:255',
            'p_phone' => 'required|regex:/^(?:[9720+])[0-9]{7,12}$/',
            'p_email' => 'required|email|max:255',
            'p_details' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () {
                // تحديث السجلات المكررة في جميع الجداول
                $this->updateDuplicatePartnerships();
            });

            session()->flash('message', 'Partnership updated successfully.');
            $this->closeModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Error updating partnership: ' . $e->getMessage());
        }
    }

    private function updateDuplicatePartnerships()
    {
        $updatedCount = 0;

        // البحث عن جميع السجلات المكررة بناءً على phone و email الأصليين
        $duplicatePartnerships = Partnership::where('phone', $this->original_phone)
            ->where('email', $this->original_email)
            ->get();

        foreach ($duplicatePartnerships as $partnership) {
            $partnership->update([
                'company_name' => $this->p_company_name,
                'person_name' => $this->p_person_name,
                'phone' => $this->p_phone,
                'email' => $this->p_email,
                'details' => $this->p_details,
            ]);
            $updatedCount++;
        }

        return $updatedCount;
    }

    public function confirmDelete($id, $phone = null, $email = null)
    {
        // ✅ استخدام phone و email للبحث عن سجل فعلي
        $partnership = Partnership::where('phone', $phone)
            ->where('email', $email)
            ->first();

        if (!$partnership) {
            session()->flash('error', 'Partnership not found.');
            return;
        }

        $this->deletingPartnershipId = $partnership->id;
        $this->deletingPartnershipName = $partnership->company_name;
        $this->showDeleteModal = true;
    }

    public function deletePartnership()
    {
        $partnership = Partnership::find($this->deletingPartnershipId);
        if ($partnership) {
            $partnership->delete();
            session()->flash('message', 'Partnership deleted successfully.');
        }
        $this->showDeleteModal = false;
    }

    public function closeModal()
    {
        $this->showEditModal = false;
        $this->reset([
            'editingPartnership', 
            'p_company_name', 
            'p_person_name', 
            'p_phone', 
            'p_email', 
            'p_details',
            'original_phone',
            'original_email'
        ]);
        $this->resetValidation();
    }

    /**
     * ✅ تصدير PDF
     */
    public function exportPdf()
    {
        try {
            $data = $this->getExportData();

            $pdf = Pdf::loadView('livewire.exportfiles.partnerships-pdf', [
                'partnerships' => $data
            ])->setPaper('a4', 'landscape');

            return response()->streamDownload(
                fn() => print($pdf->output()),
                'Partnerships-Report-' . date('Y-m-d') . '.pdf'
            );
        } catch (\Exception $e) {
            session()->flash('error', 'Error generating PDF: ' . $e->getMessage());
        }
    }

    /**
     * ✅ تصدير Excel
     */
    public function exportExcel()
    {
        try {
            $data = $this->getExportData();

            $view = view('livewire.exportfiles.partnerships-excel', [
                'partnerships' => $data
            ])->render();

            $filename = 'Partnerships-Report-' . date('Y-m-d') . '.xls';

            return response()->streamDownload(
                fn() => print($view),
                $filename
            );
        } catch (\Exception $e) {
            session()->flash('error', 'Error generating Excel: ' . $e->getMessage());
        }
    }

    /**
     * ✅ دالة للحصول على البيانات للتصدير فقط (ترجع Collection)
     */
    private function getExportData()
    {
        $queryBuilder = $this->buildBaseQuery();
        return $queryBuilder->get();
    }

    /**
     * ✅ بناء الاستعلام الأساسي
     */
    private function buildBaseQuery()
    {
        // ✅ استعلامات لكل نوع
        $internalPartnershipQuery = DB::table('partnerships')
            ->join('internal_tenders', 'partnerships.partnerable_id', '=', 'internal_tenders.id')
            ->where('partnerships.partnerable_type', 'App\Models\InternalTender\InternalTender')
            ->select(
                'partnerships.id',
                'partnerships.company_name',
                'partnerships.person_name',
                'partnerships.phone',
                'partnerships.email',
                'partnerships.details',
                'partnerships.created_at',
                'internal_tenders.client_name',
                'internal_tenders.client_type'
            );

        $eTenderPartnershipQuery = DB::table('partnerships')
            ->join('e_tenders', 'partnerships.partnerable_id', '=', 'e_tenders.id')
            ->where('partnerships.partnerable_type', 'App\Models\ETender\ETender')
            ->select(
                'partnerships.id',
                'partnerships.company_name',
                'partnerships.person_name',
                'partnerships.phone',
                'partnerships.email',
                'partnerships.details',
                'partnerships.created_at',
                'e_tenders.client_name',
                'e_tenders.client_type'
            );

        $otherPartnershipQuery = DB::table('partnerships')
            ->join('other_tenders', 'partnerships.partnerable_id', '=', 'other_tenders.id')
            ->where('partnerships.partnerable_type', 'App\Models\OtherTenderPlatform\OtherTender')
            ->select(
                'partnerships.id',
                'partnerships.company_name',
                'partnerships.person_name',
                'partnerships.phone',
                'partnerships.email',
                'partnerships.details',
                'partnerships.created_at',
                'other_tenders.client_name',
                'other_tenders.client_type'
            );

        // دمج جميع الاستعلامات
        $partnershipsQuery = $internalPartnershipQuery
            ->union($eTenderPartnershipQuery)
            ->union($otherPartnershipQuery);

        // ✅ تجميع السجلات المكررة بناءً على phone و email
        $groupedQuery = DB::query()->fromSub($partnershipsQuery, 'partnerships_sub')
            ->select(
                DB::raw('MIN(id) as id'),
                DB::raw('MIN(company_name) as company_name'),
                DB::raw('MIN(person_name) as person_name'),
                'phone',
                'email',
                DB::raw('MIN(details) as details'),
                DB::raw('MIN(created_at) as created_at'),
                DB::raw('MIN(client_name) as client_name'),
                DB::raw('MIN(client_type) as client_type')
            )
            ->groupBy('phone', 'email');

        // تطبيق الفلترة والبحث
        $queryBuilder = DB::query()->fromSub($groupedQuery, 'grouped_partnerships');

        $queryBuilder->when($this->search, function ($query) {
            $query->where(function ($subQuery) {
                $subQuery->where('company_name', 'like', '%' . $this->search . '%')
                    ->orWhere('person_name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%')
                    ->orWhere('phone', 'like', '%' . $this->search . '%')
                    ->orWhere('client_name', 'like', '%' . $this->search . '%');
            });
        })
        ->when($this->clientFilter, fn($q) => $q->where('client_name', $this->clientFilter))
        ->when($this->clientTypeFilter, fn($q) => $q->where('client_type', $this->clientTypeFilter));

        return $queryBuilder->orderBy($this->sortBy, $this->sortDir);
    }

    public function render()
    {
        $queryBuilder = $this->buildBaseQuery();
        
        // الحصول على قيم الفلترة
        $clients = (clone $queryBuilder)->pluck('client_name')->unique()->filter()->sort();
        $clientTypes = (clone $queryBuilder)->pluck('client_type')->unique()->filter()->sort();

        // التطبيق النهائي مع الترتيب وال pagination
        $partnerships = $queryBuilder->paginate(10);

        return view('livewire.contact-list.partnerships-list', [
            'partnerships' => $partnerships,
            'clients' => $clients,
            'clientTypes' => $clientTypes,
        ]);
    }
}