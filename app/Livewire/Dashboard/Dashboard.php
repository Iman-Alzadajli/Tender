<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\ETender\ETender;
use App\Models\InternalTender\InternalTender;
use App\Models\OtherTenderPlatform\OtherTender;
use App\Models\TenderNote;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Dashboard extends Component
{
    use AuthorizesRequests;

    // --- خصائص النافذة المنبثقة (Modal) والفورم ---
    public $showingTenderModal = false;
    public $isEditMode = false;
    public ?int $tenderId = null;
    public ?string $tenderModelClass = null;
    public $currentTender;
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
    public string $focalPointError = '';

    // خصائص الشراكة
    public ?string $partnership_company = '';
    public ?string $partnership_person = '';
    public ?string $partnership_phone = '';
    public ?string $partnership_email = '';
    public ?string $partnership_details = '';

    // خصائص الحالات الديناميكية
    public ?string $reason_of_cancel = '';
    public ?string $reason_of_recall = '';
    public ?float $submitted_price = null;
    public ?float $awarded_price = null;

    // قسم الملاحظات
    public $notes = [];
    public string $newNoteContent = '';
    public ?int $editingNoteId = null;
    public string $editingNoteContent = '';

    public function mount()
    {
        $this->users = User::orderBy('name')->get(['id', 'name']);
    }

    protected function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'number' => 'required|string|max:255',
            'client_type' => 'required|string|max:255',
            'client_name' => 'required|string|max:255',
            'date_of_purchase' => 'required|date',
            'assigned_to' => 'required|string|max:255',
            'date_of_submission' => 'required|date',
            'reviewed_by' => 'required|string|max:255',
            'last_date_of_clarification' => 'required|date',
            'submission_by' => 'required|string|max:255',
            'date_of_submission_after_review' => 'required|date',
            'has_third_party' => 'required|boolean',
            'last_follow_up_date' => 'required|date',
            'follow_up_channel' => 'required|string',
            'follow_up_notes' => 'nullable|string',
            'status' => 'required|string|in:Recall,Awarded to Company (win),BuildProposal,Under Evaluation,Awarded to Others (loss),Cancel',
            'focalPoints' => 'required|array|min:1',
            'focalPoints.*.name' => 'required|string|max:255',
            'focalPoints.*.phone' => ['required', 'regex:/^(?:[9720+])[0-9]{7,12}$/'],
            'focalPoints.*.email' => 'required|email|max:255',
            'focalPoints.*.department' => 'required|string|max:255',
            'focalPoints.*.other_info' => 'nullable|string',
            'reason_of_cancel' => ['nullable', 'string', Rule::requiredIf($this->status === 'Cancel')],
            'reason_of_recall' => ['nullable', 'string', Rule::requiredIf($this->status === 'Recall')],
            'submitted_price' => ['nullable', 'numeric', 'min:0', Rule::requiredIf($this->status === 'Under Evaluation')],
            'awarded_price' => ['nullable', 'numeric', 'min:0', Rule::requiredIf($this->status === 'Awarded to Others (loss)')],
            'partnership_company' => ['nullable', 'string', 'max:255', Rule::requiredIf($this->has_third_party)],
            'partnership_person'  => ['nullable', 'string', 'max:255', Rule::requiredIf($this->has_third_party)],
            'partnership_phone'   => ['nullable', 'string', 'max:255','regex:/^(?:[9720+])[0-9]{7,12}$/', Rule::requiredIf($this->has_third_party)],
            'partnership_email'   => ['nullable', 'email', 'max:255', Rule::requiredIf($this->has_third_party)],
            'partnership_details' => 'nullable|string',
            'newNoteContent' => 'nullable|string',
        ];

        if ($this->isEditMode) { // ✅ استخدام isEditMode بدلاً من mode
            $rules['editingNoteContent'] = 'nullable|string';
        }

        return $rules;
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function updatedHasThirdParty($value)
    {
        if (!$value) {
            $this->reset([
                'partnership_company',
                'partnership_person',
                'partnership_phone',
                'partnership_email',
                'partnership_details'
            ]);
        }
    }

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
        $this->resetForm();
        $this->tenderId = $id;
        $this->tenderModelClass = $this->getModelClassForType($type);

        if ($this->tenderModelClass) {
            $this->currentTender = $this->tenderModelClass::with(['focalPoints', 'notes' => fn($q) => $q->with('user')->latest()])->findOrFail($id);
            $this->fillForm($this->currentTender);
            $this->notes = $this->currentTender->notes;
        }

        $this->isEditMode = $editMode;
        $this->showingTenderModal = true;
    }

    public function resetForm(): void
    {
        $this->resetExcept('users');
        $this->has_third_party = false;
        $this->focalPoints = [['name' => '', 'phone' => '', 'email' => '', 'department' => '', 'other_info' => '']];
    }

    public function fillForm($tender): void
    {
        $this->fill($tender->only([
            'name',
            'number',
            'client_type',
            'client_name',
            'assigned_to',
            'reviewed_by',
            'submission_by',
            'has_third_party',
            'follow_up_channel',
            'follow_up_notes',
            'status',
            'reason_of_cancel',
            'submitted_price',
            'awarded_price',
            'reason_of_recall',
            'partnership_company',
            'partnership_person',
            'partnership_phone',
            'partnership_email',
            'partnership_details'
        ]));

        $this->date_of_purchase = $tender->date_of_purchase?->format('Y-m-d');
        $this->date_of_submission = $tender->date_of_submission?->format('Y-m-d');
        $this->last_date_of_clarification = $tender->last_date_of_clarification?->format('Y-m-d');
        $this->date_of_submission_after_review = $tender->date_of_submission_after_review?->format('Y-m-d');
        $this->last_follow_up_date = $tender->last_follow_up_date?->format('Y-m-d');
        $this->focalPoints = $tender->focalPoints->toArray();
    }

    public function saveTender()
    {
        $validatedData = $this->validate();

        if ($this->currentTender) {
            $tenderData = collect($validatedData)->except(['focalPoints', 'notes', 'newNoteContent', 'editingNoteContent'])->toArray();

            if ($this->status !== 'Cancel') $tenderData['reason_of_cancel'] = null;
            if ($this->status !== 'Recall') $tenderData['reason_of_recall'] = null;
            if ($this->status !== 'Under Evaluation') $tenderData['submitted_price'] = null;
            if ($this->status !== 'Awarded to Others (loss)') $tenderData['awarded_price'] = null;
            if (!$this->has_third_party) {
                $tenderData['partnership_company'] = null;
                $tenderData['partnership_person'] = null;
                $tenderData['partnership_phone'] = null;
                $tenderData['partnership_email'] = null;
                $tenderData['partnership_details'] = null;
            }

            $this->currentTender->update($tenderData);

            $this->currentTender->focalPoints()->delete();
            if (!empty($validatedData['focalPoints'])) {
                $this->currentTender->focalPoints()->createMany($validatedData['focalPoints']);
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

    public function addFocalPoint(): void
    {
        if (count($this->focalPoints) >= 5) {
            $this->focalPointError = 'You cannot add more than 5 focal points.';
            return;
        }
        $this->focalPointError = '';
        $this->focalPoints[] = ['name' => '', 'phone' => '', 'email' => '', 'department' => '', 'other_info' => ''];
    }

    public function removeFocalPoint(int $index): void
    {
        if (isset($this->focalPoints[$index])) {
            unset($this->focalPoints[$index]);
            $this->focalPoints = array_values($this->focalPoints);
        }
    }

    private function refreshNotes()
    {
        $this->notes = $this->currentTender->notes()->with('user')->latest()->get();
    }

    public function addNote()
    {
        $this->validate(['newNoteContent' => 'required|string']);
        if ($this->currentTender) {
            $this->currentTender->notes()->create(['user_id' => Auth::id(), 'content' => $this->newNoteContent]);
            $this->newNoteContent = '';
            $this->refreshNotes();
        }
    }

    public function editNote(int $noteId)
    {
        $note = TenderNote::findOrFail($noteId);
        $this->authorize('update', $note);
        $this->editingNoteId = $note->id;
        $this->editingNoteContent = $note->content;
    }

    public function updateNote(int $noteId)
    {
        $note = TenderNote::findOrFail($noteId);
        $this->authorize('update', $note);
        $this->validate(['editingNoteContent' => 'required|string']);
        $note->update(['content' => $this->editingNoteContent]);
        $this->cancelEdit();
        $this->refreshNotes();
    }

    public function cancelEdit()
    {
        $this->editingNoteId = null;
        $this->editingNoteContent = '';
    }

    public function deleteNote(int $noteId)
    {
        $note = TenderNote::findOrFail($noteId);
        $this->authorize('delete', $note);
        $note->delete();
        $this->refreshNotes();
    }

    public function render()
    {
        $columns = ['id', 'name', 'status', 'date_of_submission', 'client_type', 'client_name', 'number', 'assigned_to'];
        $eTendersQuery = ETender::select(array_merge($columns, [DB::raw("'e_tender' as tender_type")]));
        $internalTendersQuery = InternalTender::select(array_merge($columns, [DB::raw("'internal_tender' as tender_type")]));
        $otherTendersQuery = OtherTender::select(array_merge($columns, [DB::raw("'other_tender' as tender_type")]));
        $allTenders = $eTendersQuery->unionAll($internalTendersQuery)->unionAll($otherTendersQuery)->get();
        $activeStatuses = ['Recall', 'Under Evaluation', 'Awarded to Company (win)', 'BuildProposal'];
        $urgentTenders = $allTenders->whereIn('status', $activeStatuses)
            ->whereNotNull('date_of_submission')
            ->filter(fn($t) => Carbon::parse($t->date_of_submission)->between(Carbon::today(), Carbon::today()->addDays(3)))
            ->sortBy('date_of_submission');
        $statusCounts = $allTenders->countBy(fn($tender) => str_replace([' ', '(', ')'], ['_', '', ''], strtolower($tender->status)));
        $tendersByQuarter = $allTenders->whereNotNull('date_of_submission')->groupBy(fn($t) => "Q" . Carbon::parse($t->date_of_submission)->quarter)->map->count();
        $tenderQuantities = ['Q1' => $tendersByQuarter->get('Q1', 0), 'Q2' => $tendersByQuarter->get('Q2', 0), 'Q3' => $tendersByQuarter->get('Q3', 0), 'Q4' => $tendersByQuarter->get('Q4', 0)];
        return view('livewire.dashboard.dashboard', [
            'statusCounts' => $statusCounts,
            'urgentTenders' => $urgentTenders,
            'tenderQuantitiesJson' => json_encode($tenderQuantities),
            'clientTypesJson' => json_encode($allTenders->whereNotNull('client_type')->countBy('client_type')),
        ]);
    }
}
