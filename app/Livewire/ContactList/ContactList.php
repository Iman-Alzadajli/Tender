<?php

namespace App\Livewire\ContactList;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use App\Models\ETender\ETender;
use App\Models\InternalTender\InternalTender;
use App\Models\OtherTenderPlatform\OtherTender;
use App\Models\InternalTender\FocalPoint;
use App\Models\ETender\FocalPointE;
use App\Models\OtherTenderPlatform\FocalPointO;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Barryvdh\DomPDF\Facade\Pdf;

#[Layout('layouts.app')]
class ContactList extends Component
{
    use WithPagination, AuthorizesRequests;

    protected $paginationTheme = 'bootstrap';

    // --- خصائص الفلترة والبحث ---
    public string $search = '';
    public string $searchClientType = '';
    public string $searchTenderType = '';
    public bool $showFilters = false; // ✅✅✅ تمت الإضافة هنا ✅✅✅

    // --- خصائص الفرز ---
    public string $sortBy = 'name';
    public string $sortDir = 'ASC';

    // --- خصائص النافذة المنبثقة ---
    public bool $showFocalPointModal = false;
    public ?int $tenderIdForFocalPoint = null;
    public ?string $tenderTypeForFocalPoint = null;
    public string $fp_name = '';
    public string $fp_phone = '';
    public string $fp_email = '';
    public string $fp_department = '';
    public string $fp_other_info = '';

    protected $listeners = ['prepareFocalPointModal'];

    protected function rules()
    {
        return [
            'fp_name' => 'required|string|max:255',
            'fp_phone' => 'required|regex:/^(?:[9720+])[0-9]{7,12}$/',
            'fp_email' => 'required|email|max:255',
            'fp_department' => 'required|string|max:255',
            'fp_other_info' => 'nullable|string',
        ];
    }

    public function updating($property)
    {
        if (in_array($property, ['search', 'searchClientType', 'searchTenderType'])) {
            $this->resetPage();
        }
    }

    public function setSortBy($sortByField)
    {
        if ($this->sortBy === $sortByField) {
            $this->sortDir = ($this->sortDir === "ASC") ? 'DESC' : "ASC";
            return;
        }
        $this->sortBy = $sortByField;
        $this->sortDir = 'ASC';
    }

    public function loadTenderDetails($tenderType, $tenderId)
    {
        $modelClass = match ($tenderType) {
            'internal_tender' => InternalTender::class,
            'e_tender' => ETender::class,
            'other_tender' => OtherTender::class,
            default => null,
        };

        if (!$modelClass) return [];

        $tender = $modelClass::with('focalPoints')->find($tenderId);

        return [
            'focalPoints' => $tender ? $tender->focalPoints->toArray() : [],
            'partnership' => ($tender && $tender->has_third_party) ? $tender->toArray() : null,
        ];
    }

    public function prepareFocalPointModal($tenderType, $tenderId)
    {
        $this->resetFocalPointForm();
        $this->tenderIdForFocalPoint = $tenderId;
        $this->tenderTypeForFocalPoint = match ($tenderType) {
            'internal_tender' => InternalTender::class,
            'e_tender' => ETender::class,
            'other_tender' => OtherTender::class,
            default => null,
        };
        $this->showFocalPointModal = true;
    }

    public function resetFocalPointForm()
    {
        $this->reset(['fp_name', 'fp_phone', 'fp_email', 'fp_department', 'fp_other_info', 'tenderIdForFocalPoint', 'tenderTypeForFocalPoint']);
        $this->resetValidation();
    }

    public function addFocalPoint()
    {
        $this->validate();

        $focalPointModelClass = null;
        $foreignKeyColumn = null;

        if ($this->tenderTypeForFocalPoint === InternalTender::class) {
            $focalPointModelClass = FocalPoint::class;
            $foreignKeyColumn = 'internal_tender_id';
        } elseif ($this->tenderTypeForFocalPoint === ETender::class) {
            $focalPointModelClass = FocalPointE::class;
            $foreignKeyColumn = 'e_tender_id';
        } elseif ($this->tenderTypeForFocalPoint === OtherTender::class) {
            $focalPointModelClass = FocalPointO::class;
            $foreignKeyColumn = 'other_tender_id';
        }

        if (!$focalPointModelClass || !$this->tenderIdForFocalPoint || !$foreignKeyColumn) {
            $this->addError('fp_name', 'An unexpected error occurred. Tender type is not recognized.');
            return;
        }

        $existingFocalPoint = $focalPointModelClass::where($foreignKeyColumn, $this->tenderIdForFocalPoint)
            ->where(function ($query) {
                $query->where('phone', $this->fp_phone)
                      ->orWhere('email', $this->fp_email);
            })
            ->exists();

        if ($existingFocalPoint) {
            $this->addError('fp_phone', 'This contact (phone or email) already exists for this specific tender.');
            return;
        }

        $tender = ($this->tenderTypeForFocalPoint)::find($this->tenderIdForFocalPoint);
        if ($tender && $tender->focalPoints()->count() >= 5) {
            session()->flash('error', 'Cannot add more than 5 focal points to this tender.');
            $this->showFocalPointModal = false;
            return;
        }

        if ($tender) {
            $tender->focalPoints()->create([
                'name' => $this->fp_name,
                'phone' => $this->fp_phone,
                'email' => $this->fp_email,
                'department' => $this->fp_department,
                'other_info' => $this->fp_other_info,
            ]);

            session()->flash('message', 'Focal point added successfully.');
            $this->showFocalPointModal = false;
        }
    }

    private function getTendersQuery()
    {
        $cols = ['id', 'name', 'client_name', 'client_type', 'has_third_party', 'partnership_company', 'partnership_person', 'partnership_phone', 'partnership_email'];

        $internalQuery = InternalTender::select(array_merge($cols, [DB::raw("'internal_tender' as tender_type")]));
        $eTenderQuery = ETender::select(array_merge($cols, [DB::raw("'e_tender' as tender_type")]));
        $otherTenderQuery = OtherTender::select(array_merge($cols, [DB::raw("'other_tender' as tender_type")]));

        $tendersQuery = $internalQuery->unionAll($eTenderQuery)->unionAll($otherTenderQuery);

        return DB::table(DB::raw("({$tendersQuery->toSql()}) as tenders"))
            ->mergeBindings($tendersQuery->getQuery())
            ->when($this->search, function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('name', 'like', '%' . $this->search . '%')
                             ->orWhere('client_name', 'like', '%' . $this->search . '%')
                             ->orWhere('client_type', 'like', '%' . $this->search . '%')
                             ->orWhere('tender_type', 'like', '%' . str_replace(' ', '_', $this->search) . '%');
                });
            })
            ->when($this->searchClientType, function ($query) {
                $query->where('client_type', $this->searchClientType);
            })
            ->when($this->searchTenderType, function ($query) {
                $query->where('tender_type', $this->searchTenderType);
            });
    }

    public function exportPdf()
    {
        $tendersToExport = $this->getTendersQuery()->orderBy($this->sortBy, $this->sortDir)->get();
        $pdf = Pdf::loadView('livewire.contactlist.export-pdf', ['tenders' => $tendersToExport]);
        return response()->streamDownload(fn() => print($pdf->output()), 'ContactList-Report-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportExcel()
    {
        $tendersToExport = $this->getTendersQuery()->orderBy($this->sortBy, $this->sortDir)->get();
        $view = view('livewire.contactlist.export-excel', ['tenders' => $tendersToExport])->render();
        $filename = 'ContactList-Report-' . now()->format('Y-m-d') . '.xls';
        return response()->streamDownload(fn() => print($view), $filename);
    }

    public function render()
    {
        $tendersQuery = $this->getTendersQuery();
        
        $uniqueClientTypes = (clone $tendersQuery)->pluck('client_type')->unique()->filter()->sort();
        
        $tenders = $tendersQuery->orderBy($this->sortBy, $this->sortDir)->paginate(10);

        return view('livewire.contactlist.contact-list', [
            'tenders' => $tenders,
            'uniqueClientTypes' => $uniqueClientTypes,
        ]);
    }
}
