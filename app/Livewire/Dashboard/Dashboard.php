<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\ETender\ETender;
use App\Models\InternalTender\InternalTender;
use App\Models\OtherTenderPlatform\OtherTender;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\User;

class Dashboard extends Component
{
    // --- خصائص النافذة المنبثقة (Modal) والفورم ---
    public $showingTenderModal = false;
    public $isEditMode = false;
    public ?int $tenderId = null;
    public ?string $tenderModelClass = null;
    public $users;

    // --- خصائص الفورم الكاملة ---
    public string $name = '';
    public string $number = '';
    public string $client_type = '';
    public ?string $client_name = '';
    public ?string $date_of_purchase = '';
    public string $assigned_to = '';
    public ?string $date_of_submission = '';
    public string $reviewed_by = '';
    public ?string $last_date_of_clarification = '';
    public string $submission_by = '';
    public ?string $date_of_submission_after_review = '';
    public bool $has_third_party = false;
    public ?string $last_follow_up_date = '';
    public string $follow_up_channel = '';
    public ?string $follow_up_notes = '';
    public string $status = 'Recall';
    public array $focalPoints = [];


    public ?string $reason_of_cancel = '';
    public ?string $reason_of_recall = '';
    public ?float $submitted_price = null;
    public ?float $awarded_price = null;


    public function mount()
    {
        $this->users = User::orderBy('name')->get(['id', 'name']);
    }

    // --- قواعد التحقق ---
    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'number' => ['required', 'string', 'max:255'],
            'client_type' => 'required|string|max:255',
            'client_name' => 'nullable|string|max:255',
            'date_of_purchase' => 'nullable|date',
            'assigned_to' => 'required|string|max:255',
            'date_of_submission' => 'nullable|date',
            'reviewed_by' => 'required|string|max:255',
            'last_date_of_clarification' => 'nullable|date',
            'submission_by' => 'required|string|max:255',
            'date_of_submission_after_review' => 'nullable|date',
            'has_third_party' => 'required|boolean',
            'last_follow_up_date' => 'nullable|date',
            'follow_up_channel' => 'required|string',
            'follow_up_notes' => 'nullable|string',
            'status' => 'required|string|in:Recall,Awarded to Company (win),BuildProposal,Under Evaluation,Awarded to Others (loss),Cancel',
            'focalPoints' => 'sometimes|array',
            'focalPoints.*.name' => 'required|string|max:255',
            'focalPoints.*.phone' => ['required', 'regex:/^(?:[9720+])[0-9]{7,12}$/'],
            'focalPoints.*.email' => 'required|email|max:255',
            'focalPoints.*.department' => 'required|string|max:255',

     
            'reason_of_cancel' => ['nullable', 'string', Rule::requiredIf($this->status === 'Cancel')],
            'reason_of_recall' => ['nullable', 'string', Rule::requiredIf($this->status === 'Recall')],
            'submitted_price' => ['nullable', 'numeric', 'min:0', Rule::requiredIf($this->status === 'Under Evaluation')],
            'awarded_price' => ['nullable', 'numeric', 'min:0', Rule::requiredIf($this->status === 'Awarded to Others (loss)')],
        ];
    }

    // --- دوال التعامل مع النافذة المنبثقة (Modal) ---
    private function getModelClassForType($type)
    {
        return match ($type) {
            'e_tender' => ETender::class,
            'internal_tender' => InternalTender::class,
            'other_tender' => OtherTender::class,
            default => null,
        };
    }

    public function showTender($type, $id, $editMode = false)
    {
        $this->resetValidation();
        $this->tenderId = $id;
        $this->tenderModelClass = $this->getModelClassForType($type);

        if ($this->tenderModelClass) {
            $tender = $this->tenderModelClass::with('focalPoints')->findOrFail($id);

            // استخدام دالة fill لتعبئة جميع الخصائص مرة واحدة
            $this->fill([
                'name' => $tender->name,
                'number' => $tender->number,
                'client_type' => $tender->client_type,
                'client_name' => $tender->client_name,
                'date_of_purchase' => $tender->date_of_purchase?->format('Y-m-d'),
                'assigned_to' => $tender->assigned_to,
                'date_of_submission' => $tender->date_of_submission?->format('Y-m-d'),
                'reviewed_by' => $tender->reviewed_by,
                'last_date_of_clarification' => $tender->last_date_of_clarification?->format('Y-m-d'),
                'submission_by' => $tender->submission_by,
                'date_of_submission_after_review' => $tender->date_of_submission_after_review?->format('Y-m-d'),
                'has_third_party' => $tender->has_third_party,
                'last_follow_up_date' => $tender->last_follow_up_date?->format('Y-m-d'),
                'follow_up_channel' => $tender->follow_up_channel,
                'follow_up_notes' => $tender->follow_up_notes,
                'status' => $tender->status,
                'focalPoints' => $tender->focalPoints->toArray(),
                'reason_of_cancel' => $tender->reason_of_cancel,
                'reason_of_recall' => $tender->reason_of_recall,
                'submitted_price' => $tender->submitted_price,
                'awarded_price' => $tender->awarded_price,
            ]);
        }

        $this->isEditMode = $editMode;
        $this->showingTenderModal = true;
    }

    public function saveTender()
    {
        $validatedData = $this->validate();

        if ($this->tenderModelClass && $this->tenderId) {
            $tender = $this->tenderModelClass::find($this->tenderId);

            // ---  التأكد من تفريغ الحقول غير المستخدمة قبل الحفظ ---
            $tenderData = collect($validatedData)->except('focalPoints')->toArray();
            if ($this->status !== 'Cancel') $tenderData['reason_of_cancel'] = null;
            if ($this->status !== 'Recall') $tenderData['reason_of_recall'] = null;
            if ($this->status !== 'Under Evaluation') $tenderData['submitted_price'] = null;
            if ($this->status !== 'Awarded to Others (loss)') $tenderData['awarded_price'] = null;

            $tender->update($tenderData);

            $tender->focalPoints()->delete();
            if (!empty($validatedData['focalPoints'])) {
                $tender->focalPoints()->createMany($validatedData['focalPoints']);
            }

            $this->showingTenderModal = false;
            session()->flash('message', 'Tender updated successfully.');
        }
    }

    public function deleteTender($type, $id)
    {
        $modelClass = $this->getModelClassForType($type);
        if ($modelClass) {
            $modelClass::find($id)->delete();
            session()->flash('message', 'Tender deleted successfully.');
        }
    }

    public $focalPointError = '';

    public function addFocalPoint(): void
    {
        if (count($this->focalPoints) >= 5) {
            $this->focalPointError = 'You cannot add more than 5 focal points.';
            return;
        }
        $this->focalPointError = '';
        $this->focalPoints[] = ['name' => '', 'phone' => '', 'email' => '', 'department' => ''];
    }

    public function removeFocalPoint(int $index): void
    {
        if (isset($this->focalPoints[$index])) {
            unset($this->focalPoints[$index]);
            $this->focalPoints = array_values($this->focalPoints);
        }
    }

    public function render()
    {
        // جلب الأعمدة المطلوبة فقط لتقليل استهلاك الذاكرة
        $columns = ['id', 'name', 'status', 'date_of_submission', 'client_type', 'client_name', 'number', 'assigned_to'];

        $eTendersQuery = ETender::select(array_merge($columns, [DB::raw("'e_tender' as tender_type")]));
        $internalTendersQuery = InternalTender::select(array_merge($columns, [DB::raw("'internal_tender' as tender_type")]));
        $otherTendersQuery = OtherTender::select(array_merge($columns, [DB::raw("'other_tender' as tender_type")]));

        $allTenders = $eTendersQuery->unionAll($internalTendersQuery)
            ->unionAll($otherTendersQuery)
            ->get();

        $activeStatuses = ['Recall', 'Under Evaluation', 'Awarded to Company (win)', 'BuildProposal'];
        $urgentTenders = $allTenders->whereIn('status', $activeStatuses)
            ->whereNotNull('date_of_submission')
            ->filter(fn($t) => Carbon::parse($t->date_of_submission)->between(Carbon::today(), Carbon::today()->addDays(3)))
            ->sortBy('date_of_submission');

        $statusCounts = $allTenders->countBy(function ($tender) {
            return str_replace([' ', '(', ')'], ['_', '', ''], strtolower($tender->status));
        });

        $tendersByQuarter = $allTenders->whereNotNull('date_of_submission')
            ->groupBy(fn($t) => "Q" . Carbon::parse($t->date_of_submission)->quarter)
            ->map->count();

        $tenderQuantities = [
            'Q1' => $tendersByQuarter->get('Q1', 0),
            'Q2' => $tendersByQuarter->get('Q2', 0),
            'Q3' => $tendersByQuarter->get('Q3', 0),
            'Q4' => $tendersByQuarter->get('Q4', 0)
        ];

        return view('livewire.dashboard.dashboard', [
            'statusCounts' => $statusCounts,
            'urgentTenders' => $urgentTenders,
            'tenderQuantitiesJson' => json_encode($tenderQuantities),
            'clientTypesJson' => json_encode($allTenders->whereNotNull('client_type')->countBy('client_type')),
        ]);
    }
}
