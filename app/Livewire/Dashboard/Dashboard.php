<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\ETender\ETender;
use App\Models\InternalTender\InternalTender;
use App\Models\OtherTenderPlatform\OtherTender;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class Dashboard extends Component
{
    // --- خصائص عرض البيانات الرئيسية ---
    // هذه الخصائص لم تعد ضرورية لأننا سنمرر البيانات مباشرة في render
    // public $statusCounts = [];
    // public $urgentTenders = [];
    // public $tenderQuantitiesJson = '[]';
    // public $clientTypesJson = '[]';

    // --- خصائص النافذة المنبثقة (Modal) والفورم ---
    public $showingTenderModal = false;
    public $isEditMode = false;
    public ?int $tenderId = null;
    public ?string $tenderModelClass = null;

    // --- خصائص الفورم الكاملة (تبقى كما هي) ---
    public string $name = '';
    public string $number = '';
    public string $client_type = '';
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
    public string $status = 'Pending';
    public string $reason_of_decline = '';
    public array $focalPoints = [];

    // --- قواعد التحقق (تبقى كما هي) ---
    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'number' => 'required|string|max:255',
            'client_type' => 'required|string|max:255',
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
            'status' => 'required|string|in:Pending,Declined,Closed,Open,Under Evaluation',
            'reason_of_decline' => Rule::requiredIf($this->status === 'Declined'),
            'focalPoints' => 'sometimes|array',
            'focalPoints.*.name' => 'required|string|max:255',
            'focalPoints.*.phone' => 'required|string|max:255',
            'focalPoints.*.email' => 'required|email|max:255',
            'focalPoints.*.department' => 'required|string|max:255',
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
        // ✅✅✅ التصحيح الرئيسي هنا: إضافة علامة الدولار '$'
        $this->tenderId = $id;
        $this->tenderModelClass = $this->getModelClassForType($type);
        
        if ($this->tenderModelClass) {
            $tender = $this->tenderModelClass::with('focalPoints')->findOrFail($id);
            
            $this->fill([
                'name' => $tender->name,
                'number' => $tender->number,
                'client_type' => $tender->client_type,
                'date_of_purchase' => $tender->date_of_purchase?->format('Y-m-d'),
                'assigned_to' => $tender->assigned_to,
                'date_of_submission' => $tender->date_of_submission?->format('Y-m-d'),
                'reviewed_by' => $tender->reviewed_by,
                'date_of_submission_ba' => $tender->date_of_submission_ba?->format('Y-m-d'),
                'date_of_submission_after_review' => $tender->date_of_submission_after_review?->format('Y-m-d'),
                'has_third_party' => $tender->has_third_party,
                'last_follow_up_date' => $tender->last_follow_up_date?->format('Y-m-d'),
                'follow_up_channel' => $tender->follow_up_channel,
                'follow_up_notes' => $tender->follow_up_notes,
                'status' => $tender->status,
                'reason_of_decline' => $tender->reason_of_decline,
                'focalPoints' => $tender->focalPoints->toArray(),
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
            $tenderData = collect($validatedData)->except('focalPoints')->toArray();
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

    // --- دوال مساعدة للـ Focal Points (تبقى كما هي) ---
    public function addFocalPoint(): void { /* ... */ }
    public function removeFocalPoint(int $index): void { /* ... */ }

    // ==========================================================
    // |                الحل النهائي والدائم هنا                 |
    // ==========================================================
    public function render()
    {
        // --- جلب البيانات في كل مرة ---
        $columns = ['id', 'name', 'status', 'date_of_submission', 'client_type', 'number', 'assigned_to', DB::raw("'e_tender' as tender_type")];
        $eTendersQuery = ETender::select($columns);
        $columns[7] = DB::raw("'internal_tender' as tender_type");
        $internalTendersQuery = InternalTender::select($columns);
        $columns[7] = DB::raw("'other_tender' as tender_type");
        $otherTendersQuery = OtherTender::select($columns);
        $allTenders = $eTendersQuery->unionAll($internalTendersQuery)->unionAll($otherTendersQuery)->get();
        
        $allTenders->transform(function ($tender) {
            if ($tender->status) {
                $status = strtolower(trim($tender->status));
                if ($status === 'close') $status = 'closed';
                $tender->status = str_replace(' ', '_', $status);
            }
            return $tender;
        });

        $activeStatuses = ['open', 'pending', 'under_evaluation'];
        $urgentTenders = $allTenders->whereIn('status', $activeStatuses)
                                    ->whereNotNull('date_of_submission')
                                    ->filter(fn($t) => Carbon::parse($t->date_of_submission)->between(Carbon::today(), Carbon::today()->addDays(3)))
                                    ->sortBy('date_of_submission');
        
        $tendersByQuarter = $allTenders->whereNotNull('date_of_submission')
                                       ->groupBy(fn($t) => "Q" . Carbon::parse($t->date_of_submission)->quarter)
                                       ->map->count();
        $tenderQuantities = ['Q1' => $tendersByQuarter->get('Q1', 0), 'Q2' => $tendersByQuarter->get('Q2', 0), 'Q3' => $tendersByQuarter->get('Q3', 0), 'Q4' => $tendersByQuarter->get('Q4', 0)];

        // --- تمرير البيانات مباشرة إلى الواجهة ---
        return view('livewire.dashboard.dashboard', [
            'statusCounts' => $allTenders->countBy('status'),
            'urgentTenders' => $urgentTenders,
            'tenderQuantitiesJson' => json_encode($tenderQuantities),
            'clientTypesJson' => json_encode($allTenders->whereNotNull('client_type')->countBy('client_type')),
        ]);
    }
}
