<?php

namespace App\Livewire\OtherTenderPlatform;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\OtherTenderPlatform\OtherTender as Tender;
use App\Models\TenderNote;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

#[Layout('layouts.app')]
class OtherTenderPlatform extends Component
{
    use WithPagination, AuthorizesRequests;

    // ... (خصائص الواجهة الرئيسية)
    public string $search = '';
    public string $quarterFilter = '';
    public string $yearFilter = '';
    public string $statusFilter = '';
    public string $assignedFilter = '';
    public string $clientFilter = '';
    public bool $showFilters = false;

    // ... (خصائص النافذة المنبثقة)
    public bool $showModal = false;
    public string $mode = 'add';
    public ?Tender $currentTender;

    // ... (خصائص نموذج المناقصة)
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
    public ?string $quarter = '';
    public array $focalPoints = [];
    public $users = [];

    // ... (خصائص الحالات الديناميكية)
    public ?string $reason_of_cancel = '';
    public ?string $reason_of_recall = '';
    public ?float $submitted_price = null;
    public ?float $awarded_price = null;

    // --- ✅ قسم الملاحظات ---
    public $notes = [];
    public string $newNoteContent = '';
    public ?int $editingNoteId = null;
    public string $editingNoteContent = '';

    public string $sortBy = 'date_of_submission';
    public string $sortDir = 'DESC';

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
            'focalPoints' => 'sometimes|array|min:1',
            'focalPoints.*.name' => 'required|string|max:255',
            'focalPoints.*.phone' => ['required', 'regex:/^(?:[9720+])[0-9]{7,12}$/'],
            'focalPoints.*.email' => 'required|email|max:255',
            'focalPoints.*.department' => 'required|string|max:255',
            'focalPoints.*.other_info' => 'nullable|string',
            'reason_of_cancel' => ['nullable', 'string', Rule::requiredIf($this->status === 'Cancel')],
            'reason_of_recall' => ['nullable', 'string', Rule::requiredIf($this->status === 'Recall')],
            'submitted_price' => ['nullable', 'numeric', 'min:0', Rule::requiredIf($this->status === 'Under Evaluation')],
            'awarded_price' => ['nullable', 'numeric', 'min:0', Rule::requiredIf($this->status === 'Awarded to Others (loss)')],
        ];
    }

    protected $messages = [
        'focalPoints.*.phone.regex' => 'The phone number must be a valid Omani number.',
        'focalPoints.*.email.email' => 'The email must be a valid email address.',
    ];

    public $focalPointError = '';

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
        unset($this->focalPoints[$index]);
        $this->focalPoints = array_values($this->focalPoints);
    }

    public function prepareModal(string $mode, ?int $tenderId = null): void
    {
        $this->resetValidation();
        $this->resetForm();
        $this->mode = $mode;

        if ($tenderId) {
            $this->currentTender = Tender::with('focalPoints', 'notes.user')->findOrFail($tenderId);
            $this->fillForm($this->currentTender);
            $this->notes = $this->currentTender->notes;
        }

        $this->showModal = true;
    }

    public function setSortBy($sortByField)
    {
        if ($this->sortBy === $sortByField) {
            $this->sortDir = ($this->sortDir === "ASC") ? 'DESC' : "ASC";
            return;
        }
        $this->sortBy = $sortByField;
        $this->sortDir = 'DESC';
    }

    public function resetForm(): void
    {
        $this->reset();
        $this->status = 'Recall';
        $this->has_third_party = false;
        $this->focalPoints = [['name' => '', 'phone' => '', 'email' => '', 'department' => '', 'other_info' => '']];
    }

    public function fillForm(Tender $tender): void
    {
        $this->fill($tender->only([
            'name', 'number', 'client_type', 'client_name', 'assigned_to', 'reviewed_by',
            'submission_by', 'has_third_party', 'follow_up_channel', 'follow_up_notes',
            'status', 'reason_of_cancel', 'submitted_price', 'awarded_price', 'reason_of_recall', 'quarter'
        ]));

        $this->date_of_purchase = $tender->date_of_purchase?->format('Y-m-d');
        $this->date_of_submission = $tender->date_of_submission?->format('Y-m-d');
        $this->last_date_of_clarification = $tender->last_date_of_clarification?->format('Y-m-d');
        $this->date_of_submission_after_review = $tender->date_of_submission_after_review?->format('Y-m-d');
        $this->last_follow_up_date = $tender->last_follow_up_date?->format('Y-m-d');
        $this->focalPoints = $tender->focalPoints->toArray();
    }

    public function save(): void
    {
        $validatedData = $this->validate();
        $tenderData = collect($validatedData)->except(['focalPoints', 'notes'])->toArray();

        if ($this->status !== 'Cancel') $tenderData['reason_of_cancel'] = null;
        if ($this->status !== 'Recall') $tenderData['reason_of_recall'] = null;
        if ($this->status !== 'Under Evaluation') $tenderData['submitted_price'] = null;
        if ($this->status !== 'Awarded to Others (loss)') $tenderData['awarded_price'] = null;

        if ($this->mode === 'add') {
            $tender = Tender::create($tenderData);
            if (!empty($validatedData['focalPoints'])) {
                $tender->focalPoints()->createMany($validatedData['focalPoints']);
            }
            session()->flash('message', 'Tender added successfully.');
        } elseif ($this->mode === 'edit' && $this->currentTender) {
            $this->currentTender->update($tenderData);
            $this->currentTender->focalPoints()->delete();
            if (!empty($validatedData['focalPoints'])) {
                $this->currentTender->focalPoints()->createMany($validatedData['focalPoints']);
            }
            session()->flash('message', 'Tender updated successfully.');
        }

        $this->showModal = false;
    }

    // --- ✅ دوال الملاحظات ---
    public function addNote()
    {
        $this->validate(['newNoteContent' => 'required|string']);
        if ($this->currentTender) {
            $this->currentTender->notes()->create(['user_id' => Auth::id(), 'content' => $this->newNoteContent]);
            $this->newNoteContent = '';
            $this->notes = $this->currentTender->notes()->with('user')->get();
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
        $this->notes = $this->currentTender->notes()->with('user')->get();
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
        $this->notes = $this->currentTender->notes()->with('user')->get();
    }

    public function deleteTender(int $tenderId): void
    {
        Tender::find($tenderId)?->delete();
        session()->flash('message', 'Tender deleted successfully.');
    }

    public function updating($property): void
    {
        if (in_array($property, ["search", "quarterFilter", "yearFilter", "statusFilter", "assignedFilter", "clientFilter"])) {
            $this->resetPage();
        }
    }

    // ... (exportPdf and exportSimpleExcel) ...

    public function render()
    {
        $query = Tender::query();
        if ($this->search) {
            $query->where(function ($q) {
                $columns = ['name', 'client_type', 'assigned_to', 'status', 'number', 'date_of_submission'];
                foreach ($columns as $col) {
                    $q->orWhere($col, 'like', "%{$this->search}%");
                }
            });
        }

        if ($this->quarterFilter) $query->whereRaw('QUARTER(date_of_submission) = ?', [substr($this->quarterFilter, 1)]);
        if ($this->statusFilter) $query->where('status', $this->statusFilter);
        if ($this->assignedFilter) $query->where('assigned_to', $this->assignedFilter);
        if ($this->clientFilter) $query->where("client_type", "like", "%{$this->clientFilter}%");
        if ($this->yearFilter) $query->whereYear("date_of_submission", $this->yearFilter);

        $tenders = $query->orderBy($this->sortBy, $this->sortDir)->paginate(5);
        $uniqueClients = Tender::select("client_type")->whereNotNull("client_type")->distinct()->pluck("client_type");
        $uniqueAssignees = Tender::select("assigned_to")->whereNotNull("assigned_to")->distinct()->pluck("assigned_to");
        $uniqueYears = Tender::selectRaw('YEAR(date_of_submission) as year')->distinct()->orderBy('year', 'desc')->pluck('year');

        return view("livewire.othertenderplatform.other-tender-platform", [
            "tenders" => $tenders,
            "uniqueClients" => $uniqueClients,
            "uniqueAssignees" => $uniqueAssignees,
            "uniqueYears" => $uniqueYears,
        ]);
    }
}
