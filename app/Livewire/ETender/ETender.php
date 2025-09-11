<?php

namespace App\Livewire\ETender;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\ETender\ETender as Tender;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\User;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.app')]
class ETender extends Component
{
    use WithPagination; //ترقيم


    protected $paginationTheme = 'bootstrap'; //ستايل الترقيم 


    // خصائص الواجهة الرئيسية
    public string $search = '';
    public string $quarterFilter = '';
    public string $statusFilter = '';
    public string $assignedFilter = '';
    public string $clientFilter = '';
    public bool $showFilter = false;

    // خصائص النافذة المنبثقة
    public bool $showModal = false;
    public string $mode = 'add';
    public ?Tender $currentTender;

    // خصائص نموذج المناقصة
    public string $name = '';
    public string $number = '';
    public string $client_type = '';
    public ?string $client_name = '';
    public string $date_of_purchase = '';
    public string $assigned_to = '';
    public string $date_of_submission = '';
    public string $reviewed_by = '';
    public string $date_of_submission_ba = '';


    public string $date_of_submission_after_review = '';
    public bool $has_third_party = false;
    public string $last_follow_up_date = '';
    public string $follow_up_channel = '';
    public string $follow_up_notes = '';
    public string $status = 'Recall';
    public string $reason_of_cancel = '';
    public string $quarter = '';
    public array $focalPoints = []; // for focalpoint (Person) 
    public $users = []; // for assigned to (user)


    public string $sortBy = 'date_of_submission';
    public string $sortDir = 'DESC';

    //اظهار اسماء يوسر في اساين تو 

    public function mount()
    {
        $this->users = User::all(['id', 'name']);
    }


    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'number' => ['required', 'string', 'max:255'],
            'client_type' => 'required|string|max:255',
            'client_name' => 'required|string|max:255',
            'date_of_purchase' => 'required|date',
            'assigned_to' => 'required|string|max:255',
            'date_of_submission' => 'required|date',
            'reviewed_by' => 'required|string|max:255',
            'date_of_submission_ba' => 'required|date',
            'date_of_submission_after_review' => 'required|date',
            'has_third_party' => 'required|boolean',
            'last_follow_up_date' => 'required|date',
            'follow_up_channel' => 'required|string',
            'follow_up_notes' => 'nullable|string',
            'status' => 'required|string|in:Recall,Awarded to Company (win),BuildProposal,Under Evaluation,Awarded to Others (loss),Cancel',
            'reason_of_cancel' => Rule::requiredIf($this->status === 'Cancel'),
            'focalPoints' => 'required|array|min:1',
            'focalPoints.*.name' => 'required|string|max:255',
            'focalPoints.*.phone' => ['required', 'numeric', 'digits_between:8,25'],
            'focalPoints.*.email' => 'required|email|max:255',
            'focalPoints.*.department' => 'required|string|max:255',
            'focalPoints.*.other_info' => 'nullable|string',
            // 'focalPoints.*.phone' => ['required', 'numeric', 'regex:/^(\+968)?[79]\d{7}$/'],
            // 'focalPoints.*.email' => ['required', 'string', 'email', 'max:255'],
            // 'focalPoints.*.department' => 'required|string|max:255',
            // 'focalPoints.*.other_info' => 'nullable|string',
        ];
    }

    //phone wrong msg 
    protected $messages = [
        'focalPoints.*.phone.regex' => 'The phone number must be a valid Omani number.',
    ];

    //email wrong msg 
    protected $messagesemail = [
        'focalPoints.*.email.email' => 'The email must be a valid email address.',
    ];



    // public function addFocalPoint(): void
    // {
    //     $this->focalPoints[] = ['name' => '', 'phone' => '', 'email' => '', 'department' => '', 'other_info' => ''];
    // }

    public $focalPointError = ''; // خاصية لحفظ الرسالة

    public function addFocalPoint(): void
    {
        if (count($this->focalPoints) >= 5) {
            $this->focalPointError = 'You cannot add more than 5 focal points.';
            return;
        }

        // مسح الرسالة القديمة
        $this->focalPointError = '';

        $this->focalPoints[] = [
            'name' => '',
            'phone' => '',
            'email' => '',
            'department' => ''
        ];
    }

    public function removeFocalPoint(int $index): void
    {
        unset($this->focalPoints[$index]);
        $this->focalPoints = array_values($this->focalPoints);
    }

    public function prepareModal(string $mode, ?int $tenderId = null): void
    {
        $this->resetValidation(); // امسح فالديشن القديم 
        $this->resetForm(); // امسح فيلدس 
        $this->mode = $mode;

        if ($tenderId) { // في حالة موجودة بيانات فوكل و بيانات جدول في داتا اعرضهم 
            $this->currentTender = Tender::with('focalPoints')->findOrFail($tenderId);
            $this->fillForm($this->currentTender);
        }

        $this->showModal = true;
    }

    // لما تكون فاضية و قيم الافتراضية 

    public function resetForm(): void
    {
        $this->reset([
            'name',
            'number',
            'client_type',
            'client_name',
            'date_of_purchase',
            'assigned_to',
            'date_of_submission',
            'reviewed_by',
            'date_of_submission_ba',
            'date_of_submission_after_review',
            'has_third_party',
            'last_follow_up_date',
            'follow_up_channel',
            'follow_up_notes',
            'status',
            'reason_of_cancel',
            'quarter',
            'focalPoints',
            'currentTender'
        ]);
        $this->status = 'Recall';
        $this->has_third_party = false;
        $this->focalPoints = [['name' => '', 'phone' => '', 'email' => '', 'department' => '', 'other_info' => '']];
    }

    // تعبئة 
    public function fillForm(Tender $tender): void
    {
        $this->name = $tender->name;
        $this->number = $tender->number;
        $this->client_type = $tender->client_type;
        $this->client_name = $tender->client_name;
        $this->date_of_purchase = $tender->date_of_purchase?->format('Y-m-d');
        $this->assigned_to = $tender->assigned_to;
        $this->date_of_submission = $tender->date_of_submission?->format('Y-m-d');
        $this->reviewed_by = $tender->reviewed_by;
        $this->date_of_submission_ba = $tender->date_of_submission_ba?->format('Y-m-d');
        $this->date_of_submission_after_review = $tender->date_of_submission_after_review?->format('Y-m-d');
        $this->has_third_party = $tender->has_third_party;
        $this->last_follow_up_date = $tender->last_follow_up_date?->format('Y-m-d');
        $this->follow_up_channel = $tender->follow_up_channel;
        $this->follow_up_notes = $tender->follow_up_notes;
        $this->status = $tender->status;
        $this->reason_of_cancel = $tender->reason_of_cancel;
        $this->quarter = $tender->quarter;
        $this->focalPoints = $tender->focalPoints->toArray();
    }


    //ترتيب
    public function setSortBy($sortByField)
    {
        if ($this->sortBy === $sortByField) {
            $this->sortDir = ($this->sortDir === "ASC") ? 'DESC' : "ASC";
            return;
        }
        $this->sortBy = $sortByField;
        $this->sortDir = 'DESC';
    }





    // احفظ 

    public function save(): void
    {
        $validatedData = $this->validate();

        $dateFields = [
            'date_of_purchase',
            'date_of_submission_ba',
            'date_of_submission_after_review',
            'last_follow_up_date',
        ];

        foreach ($dateFields as $field) {
            if (empty($validatedData[$field])) {
                $validatedData[$field] = null;
            }
        }

        $tenderData = collect($validatedData)->except('focalPoints')->toArray();


        if ($this->mode === 'add') {
            $tender = Tender::create($tenderData);
            if (!empty($validatedData['focalPoints'])) {
                $tender->focalPoints()->createMany($validatedData['focalPoints']);
            }
            session()->flash('message', 'Tender added successfully.');
        } elseif ($this->mode === 'edit') {
            $this->currentTender->update($tenderData);
            $this->currentTender->focalPoints()->delete();
            if (!empty($validatedData['focalPoints'])) {
                $this->currentTender->focalPoints()->createMany($validatedData['focalPoints']);
            }
            session()->flash('message', 'Tender updated successfully.');
        }

        $this->showModal = false;
    }

    public function deleteTender(int $tenderId): void
    {
        Tender::find($tenderId)?->delete();
        session()->flash('message', 'Tender deleted successfully.');
    }

    public function updating($property): void
    {
        if (in_array($property, ['search', 'quarterFilter', 'statusFilter', 'assignedFilter', 'clientFilter'])) {
            $this->resetPage();
        }
    }

    //pdf

    public function exportPdf()
    {
        $query = Tender::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")->orWhere('client_type', 'like', "%{$this->search}%"))
            ->when($this->quarterFilter, fn($q) => $q->whereRaw('QUARTER(date_of_submission) = ?', [substr($this->quarterFilter, 1)]))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->assignedFilter, fn($q) => $q->where('assigned_to', $this->assignedFilter))
            ->when($this->clientFilter, fn($q) => $q->where('client_type', 'like', "%{$this->clientFilter}%"));

        $tendersToExport = $query->latest('date_of_purchase')->get();

        $pdf = Pdf::loadView('livewire.e-tender.etender-pdf', [
            'tenders' => $tendersToExport
        ]);


        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'Tenders-Report-' . now()->format('Y-m-d') . '.pdf');
    }

    //excel 

    public function exportSimpleExcel()
    {

        // نحدد كل الأعمدة التي نريدها في ملف Excel
        $columnsToExport = [
            'id',
            'name',
            'number',
            'client_type',
            'client_name',
            'assigned_to',
            'date_of_purchase',
            'date_of_submission',
            'reviewed_by',
            'date_of_submission_ba',
            'date_of_submission_after_review',
            'has_third_party',
            'last_follow_up_date',
            'follow_up_channel',
            'follow_up_notes',
            'status',
            'reason_of_cancel'
        ];

        //نطبق نفس الفلاتر الحالية في الواجهة
        $query = Tender::query() // استخدم Tender::class بدلاً من المسار الكامل
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")->orWhere('client_type', 'like', "%{$this->search}%"))
            ->when($this->quarterFilter, fn($q) => $q->whereRaw('QUARTER(date_of_submission) = ?', [substr($this->quarterFilter, 1)]))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->assignedFilter, fn($q) => $q->where('assigned_to', $this->assignedFilter))
            ->when($this->clientFilter, fn($q) => $q->where('client_type', 'like', "%{$this->clientFilter}%"));

        //  نجلب البيانات مع العلاقات (Focal Points) ونحدد الأعمدة
        $tendersToExport = $query->with('focalPoints')
            ->select($columnsToExport) //
            ->orderBy($this->sortBy, $this->sortDir)
            ->get();

        //  نعرض البيانات في ملف Blade
        $view = view('livewire.othertenderplatform.ExcelOther', [
            'tenders' => $tendersToExport
        ])->render();

        $filename = 'Tenders-Report-' . now()->format('Y-m-d') . '.xls';

        return response()->streamDownload(function () use ($view) {
            echo $view;
        }, $filename);
    }




    // للبحث 
    public function render()
    {
        $query = Tender::query();
        // if ($this->search) {
        //     $query->where(fn($q) => $q->where('name', 'like', "%{$this->search}%")->orWhere('client_type', 'like', "%{$this->search}%"));
        // }

        // if ($this->search) {
        //     $query->where(function ($q) {
        //         $q->where('name', 'like', "%{$this->search}%")
        //             ->orWhere('client_type', 'like', "%{$this->search}%")
        //             ->orWhere('assigned_to', 'like', "%{$this->search}%")
        //             ->orWhere('status', 'like', "%{$this->search}%")
        //             ->orWhere('number', 'like', "%{$this->search}%")
        //             ->orWhereDate('date_of_submission', $this->search);
        //     });
        // }

        if ($this->search) {
            $query->where(function ($q) {
                $columns = [
                    'name',
                    'client_type',
                    'assigned_to',
                    'status',
                    'number',
                    'date_of_submission',
                ];

                foreach ($columns as $col) {
                    $q->orWhere($col, 'like', "%{$this->search}%");
                }
            });
        }

        // if ($this->quarterFilter) {
        //     $query->whereRaw('QUARTER(date_of_submission) = ?', [substr($this->quarterFilter, 1)]);
        // }


        if ($this->quarterFilter) {
            if (in_array($this->quarterFilter, ['Q1', 'Q2', 'Q3', 'Q4'])) {
                $quarterNumber = substr($this->quarterFilter, 1);

                $query->where(DB::raw('QUARTER(date_of_submission)'), $quarterNumber);
            }
        }

        // if ($this->quarterFilter) {
        //     // نبحث عن تطابق تام مع "Q1, 2025"
        //     $query->whereHas('date_of_submission', function ($q) {
        //         $parts = explode(', ', $this->quarterFilter);
        //         $q->where(\Illuminate\Support\Facades\DB::raw('QUARTER(date_of_submission)'), substr($parts[0], 1))
        //             ->whereYear('date_of_submission', $parts[1]);
        //     });
        // }
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }
        if ($this->assignedFilter) {
            $query->where('assigned_to', $this->assignedFilter);
        }
        if ($this->clientFilter) {
            $query->where('client_type', 'like', "%{$this->clientFilter}%");
        }

        //$tenders = $query->latest('date_of_purchase')->paginate(5); //عدد الصفوف  في جدول 
        $tenders = $query->orderBy($this->sortBy, $this->sortDir)->paginate(5);
        $uniqueClients = Tender::select('client_type')->whereNotNull('client_type')->distinct()->pluck('client_type');
        $uniqueAssignees = Tender::select('assigned_to')->whereNotNull('assigned_to')->distinct()->pluck('assigned_to');

        $uniqueQuarters = Tender::whereNotNull('date_of_submission')
            ->select(\Illuminate\Support\Facades\DB::raw("CONCAT('Q', QUARTER(date_of_submission), ', ', YEAR(date_of_submission)) as quarter_year"))
            ->distinct()
            ->orderBy('quarter_year', 'desc')
            ->pluck('quarter_year');

        return view('livewire.e-tender.e-tender', [
            'tenders' => $tenders,
            'uniqueClients' => $uniqueClients,
            'uniqueAssignees' => $uniqueAssignees,
        ]);
    }
}
