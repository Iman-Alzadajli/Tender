<?php

namespace App\Livewire\ContactList;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use App\Models\InternalTender\FocalPoint as InternalTenderFocalPoint;
use App\Models\ETender\FocalPointE as ETenderFocalPoint;
use App\Models\OtherTenderPlatform\FocalPointO as OtherTenderFocalPoint;

use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SimpleExport;


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
    public string $original_phone = '';
    public string $original_email = '';

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

    public function editFocalPoint($id, $type, $phone = null, $email = null)
    {
        // ✅ استخدام phone و email للبحث عن سجل فعلي بدلاً من الاعتماد على ID فقط
        $normalizedType = $this->normalizeType($type);

        $modelClass = $this->getFocalPointModelClass($normalizedType);

        if (!$modelClass) {
            session()->flash('error', 'Invalid focal point type.');
            return;
        }

        // ✅ البحث عن أول سجل يطابق phone و email في الجدول المحدد
        $focalPoint = $modelClass::where('phone', $phone)
            ->where('email', $email)
            ->with('tender')
            ->first();

        if (!$focalPoint) {
            session()->flash('error', 'Focal point not found.');
            return;
        }

        $this->editingFocalPoint = $focalPoint;
        $this->editingFocalPointId = $focalPoint->id;
        $this->editingFocalPointType = $normalizedType;
        $this->fp_name = $focalPoint->name;
        $this->fp_phone = $focalPoint->phone;
        $this->fp_email = $focalPoint->email;
        $this->fp_department = $focalPoint->department;
        $this->fp_other_info = $focalPoint->other_info;

        // حفظ القيم الأصلية لتحديد السجلات المكررة
        $this->original_phone = $focalPoint->phone;
        $this->original_email = $focalPoint->email;

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

        try {
            DB::transaction(function () {
                // تحديث السجلات المكررة في جميع الجداول
                $this->updateDuplicateFocalPoints();
            });

            session()->flash('message', 'Focal Point updated successfully.');
            $this->closeModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Error updating focal point: ' . $e->getMessage());
        }
    }

    private function updateDuplicateFocalPoints()
    {
        $types = ['internal_tender', 'e_tender', 'other_tender'];
        $updatedCount = 0;

        foreach ($types as $type) {
            $modelClass = $this->getFocalPointModelClass($type);

            if (!$modelClass) continue;

            // البحث عن جميع السجلات المكررة بناءً على phone و email الأصليين
            $duplicateFocalPoints = $modelClass::where('phone', $this->original_phone)
                ->where('email', $this->original_email)
                ->get();

            foreach ($duplicateFocalPoints as $focalPoint) {
                $focalPoint->update([
                    'name' => $this->fp_name,
                    'phone' => $this->fp_phone,
                    'email' => $this->fp_email,
                    'department' => $this->fp_department,
                    'other_info' => $this->fp_other_info,
                ]);
                $updatedCount++;
            }
        }

        return $updatedCount;
    }

    public function confirmDelete($id, $type, $phone = null, $email = null)
    {
        // ✅ استخدام phone و email للبحث عن سجل فعلي
        $normalizedType = $this->normalizeType($type);

        $modelClass = $this->getFocalPointModelClass($normalizedType);

        if (!$modelClass) {
            session()->flash('error', 'Invalid focal point type.');
            return;
        }

        // ✅ البحث عن أول سجل يطابق phone و email في الجدول المحدد
        $focalPoint = $modelClass::where('phone', $phone)
            ->where('email', $email)
            ->first();

        if (!$focalPoint) {
            session()->flash('error', 'Focal point not found.');
            return;
        }

        $this->deletingFocalPointId = $focalPoint->id;
        $this->deletingFocalPointType = $normalizedType;
        $this->deletingFocalPointName = $focalPoint->name;
        $this->showDeleteModal = true;
    }

    public function deleteFocalPoint()
    {
        if (!$this->deletingFocalPointType) {
            session()->flash('error', 'No focal point type specified.');
            $this->showDeleteModal = false;
            return;
        }

        $modelClass = $this->getFocalPointModelClass($this->deletingFocalPointType);

        if (!$modelClass) {
            session()->flash('error', 'Invalid focal point type.');
            $this->showDeleteModal = false;
            return;
        }

        $focalPoint = $modelClass::find($this->deletingFocalPointId);

        if ($focalPoint) {
            $focalPoint->delete();
            session()->flash('message', 'Focal Point deleted successfully.');
        } else {
            session()->flash('error', 'Focal point not found.');
        }

        $this->showDeleteModal = false;
        $this->reset(['deletingFocalPointId', 'deletingFocalPointType', 'deletingFocalPointName']);
    }

    public function closeModal()
    {
        $this->showEditModal = false;
        $this->reset([
            'fp_name',
            'fp_phone',
            'fp_email',
            'fp_department',
            'fp_other_info',
            'editingFocalPoint',
            'editingFocalPointId',
            'editingFocalPointType',
            'original_phone',
            'original_email'
        ]);
        $this->resetValidation();
    }





    /**
     * ✅ دالة تطبيع نوع الـ type
     */
    private function normalizeType($type)
    {
        $type = strtolower(trim($type));

        if (str_contains($type, 'internal') || $type === 'internal_tender') {
            return 'internal_tender';
        } elseif (str_contains($type, 'e-') || $type === 'e_tender') {
            return 'e_tender';
        } elseif (str_contains($type, 'other') || $type === 'other_tender') {
            return 'other_tender';
        }

        return $type;
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

    // public function exportPdf()
    // {
    //     session()->flash('error', 'PDF export is not implemented yet.');
    // }

    public function exportPdf()
    {
        try {
            // استخدم getExportData() بدل getFocalPointsForRender()
            $data = $this->getExportData();

            $pdf = Pdf::loadView('livewire.exportfiles.focalpoints-pdf', [
                'focalPoints' => $data
            ])->setPaper('a4', 'landscape');

            return response()->streamDownload(
                fn() => print($pdf->output()),
                'FocalPoints-Report-' . date('Y-m-d') . '.pdf'
            );
        } catch (\Exception $e) {
            session()->flash('error', 'Error generating PDF: ' . $e->getMessage());
        }
    }

    public function exportExcel()
    {
        try {
            // استخدم getExportData() بدل getFocalPointsForRender()
            $data = $this->getExportData();

            $view = view('livewire.exportfiles.focalpoints-excel', [
                'focalPoints' => $data
            ])->render();

            $filename = 'FocalPoints-Report-' . date('Y-m-d') . '.xls';

            return response()->streamDownload(
                fn() => print($view),
                $filename
            );
        } catch (\Exception $e) {
            session()->flash('error', 'Error generating Excel: ' . $e->getMessage());
        }
    }

    private function getExportData()
    {
        // ✅ استعلامات لكل نوع مع إضافة client_type
        $internalFpQuery = DB::table('focal_points')
            ->join('internal_tenders', 'focal_points.internal_tender_id', '=', 'internal_tenders.id')
            ->select(
                'focal_points.id',
                'focal_points.name',
                'focal_points.phone',
                'focal_points.email',
                'focal_points.department',
                'focal_points.created_at',
                'internal_tenders.client_name',
                'internal_tenders.client_type',
                DB::raw("'Internal Tender' as tender_type_label")
            );

        $eTenderFpQuery = DB::table('focal_point_e_s')
            ->join('e_tenders', 'focal_point_e_s.e_tender_id', '=', 'e_tenders.id')
            ->select(
                'focal_point_e_s.id',
                'focal_point_e_s.name',
                'focal_point_e_s.phone',
                'focal_point_e_s.email',
                'focal_point_e_s.department',
                'focal_point_e_s.created_at',
                'e_tenders.client_name',
                'e_tenders.client_type',
                DB::raw("'E-Tender' as tender_type_label")
            );

        $otherFpQuery = DB::table('focal_points_o')
            ->join('other_tenders', 'focal_points_o.other_tender_id', '=', 'other_tenders.id')
            ->select(
                'focal_points_o.id',
                'focal_points_o.name',
                'focal_points_o.phone',
                'focal_points_o.email',
                'focal_points_o.department',
                'focal_points_o.created_at',
                'other_tenders.client_name',
                'other_tenders.client_type',
                DB::raw("'Other Tender' as tender_type_label")
            );

        // دمج جميع الاستعلامات
        $focalPointsQuery = $internalFpQuery->union($eTenderFpQuery)->union($otherFpQuery);

        // ✅ تجميع السجلات المكررة بناءً على phone و email
        $groupedQuery = DB::query()->fromSub($focalPointsQuery, 'focal_points_sub')
            ->select(
                DB::raw('MIN(id) as id'),
                DB::raw('MIN(name) as name'),
                'phone',
                'email',
                DB::raw('MIN(department) as department'),
                DB::raw('MIN(created_at) as created_at'),
                DB::raw('MIN(client_name) as client_name'),
                DB::raw('MIN(client_type) as client_type'),
                DB::raw('MIN(tender_type_label) as tender_type_label')
            )
            ->groupBy('phone', 'email');

        // تطبيق الفلترة والبحث
        $queryBuilder = DB::query()->fromSub($groupedQuery, 'grouped_focal_points');

        $queryBuilder->when($this->search, function ($query) {
            $query->where(function ($subQuery) {
                $subQuery->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%')
                    ->orWhere('phone', 'like', '%' . $this->search . '%')
                    ->orWhere('client_name', 'like', '%' . $this->search . '%')
                    ->orWhere('client_type', 'like', '%' . $this->search . '%');
            });
        })
            ->when($this->clientFilter, fn($q) => $q->where('client_name', $this->clientFilter))
            ->when($this->clientTypeFilter, fn($q) => $q->where('client_type', $this->clientTypeFilter));

        return $queryBuilder->orderBy($this->sortBy, $this->sortDir)->get();
    }









    public function render()
    {
        // ✅ استعلامات لكل نوع مع إضافة client_type
        $internalFpQuery = DB::table('focal_points')
            ->join('internal_tenders', 'focal_points.internal_tender_id', '=', 'internal_tenders.id')
            ->select(
                'focal_points.id',
                'focal_points.name',
                'focal_points.phone',
                'focal_points.email',
                'focal_points.department',
                'focal_points.created_at',
                'internal_tenders.client_name',
                'internal_tenders.client_type',
                DB::raw("'Internal Tender' as tender_type_label")
            );

        $eTenderFpQuery = DB::table('focal_point_e_s')
            ->join('e_tenders', 'focal_point_e_s.e_tender_id', '=', 'e_tenders.id')
            ->select(
                'focal_point_e_s.id',
                'focal_point_e_s.name',
                'focal_point_e_s.phone',
                'focal_point_e_s.email',
                'focal_point_e_s.department',
                'focal_point_e_s.created_at',
                'e_tenders.client_name',
                'e_tenders.client_type',
                DB::raw("'E-Tender' as tender_type_label")
            );

        $otherFpQuery = DB::table('focal_points_o')
            ->join('other_tenders', 'focal_points_o.other_tender_id', '=', 'other_tenders.id')
            ->select(
                'focal_points_o.id',
                'focal_points_o.name',
                'focal_points_o.phone',
                'focal_points_o.email',
                'focal_points_o.department',
                'focal_points_o.created_at',
                'other_tenders.client_name',
                'other_tenders.client_type',
                DB::raw("'Other Tender' as tender_type_label")
            );

        // دمج جميع الاستعلامات
        $focalPointsQuery = $internalFpQuery->union($eTenderFpQuery)->union($otherFpQuery);

        // ✅ تجميع السجلات المكررة بناءً على phone و email
        $groupedQuery = DB::query()->fromSub($focalPointsQuery, 'focal_points_sub')
            ->select(
                DB::raw('MIN(id) as id'),
                DB::raw('MIN(name) as name'),
                'phone',
                'email',
                DB::raw('MIN(department) as department'),
                DB::raw('MIN(created_at) as created_at'),
                DB::raw('MIN(client_name) as client_name'),
                DB::raw('MIN(client_type) as client_type'),
                DB::raw('MIN(tender_type_label) as tender_type_label')
            )
            ->groupBy('phone', 'email');

        // تطبيق الفلترة والبحث
        $queryBuilder = DB::query()->fromSub($groupedQuery, 'grouped_focal_points');

        $queryBuilder->when($this->search, function ($query) {
            $query->where(function ($subQuery) {
                $subQuery->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%')
                    ->orWhere('phone', 'like', '%' . $this->search . '%')
                    ->orWhere('client_name', 'like', '%' . $this->search . '%')
                    ->orWhere('client_type', 'like', '%' . $this->search . '%');
            });
        })
            ->when($this->clientFilter, fn($q) => $q->where('client_name', $this->clientFilter))
            ->when($this->clientTypeFilter, fn($q) => $q->where('client_type', $this->clientTypeFilter));

        // الحصول على قيم الفلترة
        $clients = (clone $queryBuilder)->pluck('client_name')->unique()->filter()->sort();
        $clientTypes = (clone $queryBuilder)->pluck('client_type')->unique()->filter()->sort();

        // التطبيق النهائي مع الترتيب
        $focalPoints = $queryBuilder->orderBy($this->sortBy, $this->sortDir)->paginate(10);

        return view('livewire.contact-list.focal-points-list', [
            'focalPoints' => $focalPoints,
            'clients' => $clients,
            'clientTypes' => $clientTypes,
        ]);
    }
}
