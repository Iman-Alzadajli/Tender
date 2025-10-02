<?php

namespace App\Livewire\ContactList;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use App\Models\InternalTender\FocalPoint as InternalTenderFocalPoint;
use App\Models\ETender\FocalPointE as ETenderFocalPoint;
use App\Models\OtherTenderPlatform\FocalPointO as OtherTenderFocalPoint;

#[Layout('layouts.app')]
class FocalPointsList extends Component
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
    public $editingFocalPoint;
    public string $fp_name = '';
    public string $fp_phone = '';
    public string $fp_email = '';
    public string $fp_department = '';
    public string $fp_other_info = '';
    public ?int $editingFocalPointId = null;
    public ?string $editingFocalPointType = null;

    // --- خصائص نافذة الحذف ---
    public bool $showDeleteModal = false;
    public ?int $deletingFocalPointId = null;
    public ?string $deletingFocalPointType = null;
    public ?string $deletingFocalPointName = '';

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

    public function editFocalPoint($id, $type)
    {
        $modelClass = $this->getFocalPointModelClass($type);
        $focalPoint = $modelClass::with('tender')->find($id);
        if (!$focalPoint) return;

        $this->editingFocalPoint = $focalPoint;
        $this->editingFocalPointId = $id;
        $this->editingFocalPointType = $type;
        $this->fp_name = $focalPoint->name;
        $this->fp_phone = $focalPoint->phone;
        $this->fp_email = $focalPoint->email;
        $this->fp_department = $focalPoint->department;
        $this->fp_other_info = $focalPoint->other_info;
        $this->resetValidation();
        $this->showEditModal = true;
    }

    public function updateFocalPoint()
    {
        $this->validate([
            'fp_name' => 'required|string|max:255',
            'fp_phone' => 'required|regex:/^(?:[9720+])[0-9]{7,12}$/',
            'fp_email' => 'required|email|max:255',
            'fp_department' => 'required|string|max:255',
            'fp_other_info' => 'nullable|string',
        ]);

        $clientName = $this->editingFocalPoint->tender->client_name;
        $isDuplicate = $this->isDuplicateContact($clientName, $this->fp_phone, $this->fp_email, $this->editingFocalPointId, $this->editingFocalPointType);

        if ($isDuplicate) {
            $this->addError('fp_email', 'This contact (phone & email) is already registered for this client.');
            return;
        }

        $this->editingFocalPoint->update([
            'name' => $this->fp_name,
            'phone' => $this->fp_phone,
            'email' => $this->fp_email,
            'department' => $this->fp_department,
            'other_info' => $this->fp_other_info,
        ]);

        session()->flash('message', 'Focal Point updated successfully.');
        $this->closeModal();
    }

    private function isDuplicateContact($clientName, $phone, $email, $excludeId, $excludeType)
    {
        $types = ['internal_tender', 'e_tender', 'other_tender'];

        foreach ($types as $type) {
            $modelClass = $this->getFocalPointModelClass($type);

            $query = $modelClass::where('phone', $phone)
                ->where('email', $email)
                ->whereHas('tender', fn($q) => $q->where('client_name', $clientName));

            if ($type === $excludeType) {
                $query->where('id', '!=', $excludeId);
            }

            if ($query->exists()) {
                return true;
            }
        }
        return false;
    }

    public function confirmDelete($id, $type)
    {
        $modelClass = $this->getFocalPointModelClass($type);
        $focalPoint = $modelClass::find($id);
        if (!$focalPoint) return;

        $this->deletingFocalPointId = $id;
        $this->deletingFocalPointType = $type;
        $this->deletingFocalPointName = $focalPoint->name;
        $this->showDeleteModal = true;
    }

    public function deleteFocalPoint()
    {
        $modelClass = $this->getFocalPointModelClass($this->deletingFocalPointType);
        $focalPoint = $modelClass::find($this->deletingFocalPointId);

        if ($focalPoint) {
            $focalPoint->delete();
            session()->flash('message', 'Focal Point deleted successfully.');
        }

        $this->showDeleteModal = false;
        $this->reset(['deletingFocalPointId', 'deletingFocalPointType', 'deletingFocalPointName']);
    }

    public function closeModal()
    {
        $this->showEditModal = false;
        $this->reset(['fp_name', 'fp_phone', 'fp_email', 'fp_department', 'fp_other_info', 'editingFocalPoint', 'editingFocalPointId', 'editingFocalPointType']);
        $this->resetValidation();
    }

    private function getFocalPointModelClass($type)
    {
        return match ($type) {
            'internal_tender' => InternalTenderFocalPoint::class,
            'e_tender' => ETenderFocalPoint::class,
            'other_tender' => OtherTenderFocalPoint::class,
            default => null,
        };
    }

    public function exportPdf()
    {
        session()->flash('error', 'PDF export is not implemented yet.');
    }

    public function exportExcel()
    {
        session()->flash('error', 'Excel export is not implemented yet.');
    }

    public function render()
    {
        // ✅✅✅ تم تعديل كل جمل select هنا لإضافة department ✅✅✅
        $internalFpQuery = DB::table('focal_points')
            ->join('internal_tenders', 'focal_points.internal_tender_id', '=', 'internal_tenders.id')
            ->select('focal_points.id', 'focal_points.name', 'focal_points.phone', 'focal_points.email', 'focal_points.department', 'focal_points.created_at', 'internal_tenders.client_name', 'internal_tenders.client_type', DB::raw("'Internal Tender' as tender_type_label"));

        $eTenderFpQuery = DB::table('focal_point_e_s')
            ->join('e_tenders', 'focal_point_e_s.e_tender_id', '=', 'e_tenders.id')
            ->select('focal_point_e_s.id', 'focal_point_e_s.name', 'focal_point_e_s.phone', 'focal_point_e_s.email', 'focal_point_e_s.department', 'focal_point_e_s.created_at', 'e_tenders.client_name', 'e_tenders.client_type', DB::raw("'E-Tender' as tender_type_label"));

        $otherFpQuery = DB::table('focal_points_o')
            ->join('other_tenders', 'focal_points_o.other_tender_id', '=', 'other_tenders.id')
            ->select('focal_points_o.id', 'focal_points_o.name', 'focal_points_o.phone', 'focal_points_o.email', 'focal_points_o.department', 'focal_points_o.created_at', 'other_tenders.client_name', 'other_tenders.client_type', DB::raw("'Other Tender' as tender_type_label"));

        $focalPointsQuery = $internalFpQuery->union($eTenderFpQuery)->union($otherFpQuery);
        $queryBuilder = DB::query()->fromSub($focalPointsQuery, 'focal_points_sub');

        $queryBuilder->when($this->search, function ($query) {
            $query->where(function ($subQuery) {
                $subQuery->where('name', 'like', '%' . $this->search . '%')
                         ->orWhere('email', 'like', '%' . $this->search . '%')
                         ->orWhere('phone', 'like', '%' . $this->search . '%')
                         ->orWhere('client_name', 'like', '%' . $this->search . '%');
            });
        })
        ->when($this->clientFilter, fn($q) => $q->where('client_name', $this->clientFilter))
        ->when($this->clientTypeFilter, fn($q) => $q->where('client_type', $this->clientTypeFilter));

        $clients = (clone $queryBuilder)->pluck('client_name')->unique()->filter()->sort();
        $clientTypes = (clone $queryBuilder)->pluck('client_type')->unique()->filter()->sort();

        $focalPoints = $queryBuilder->orderBy($this->sortBy, $this->sortDir)->paginate(10);

        return view('livewire.contact-list.focal-points-list', [
            'focalPoints' => $focalPoints,
            'clients' => $clients,
            'clientTypes' => $clientTypes,
        ]);
    }
}
