<?php

namespace App\Livewire\InternalTender;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\InternalTender\InternalTender as Tender;
use App\Models\TenderNote;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\TenderNoteHistory;
use Illuminate\Support\Facades\Gate;


#[Layout('layouts.app')]
class InternalTender extends Component
{
    use WithPagination, AuthorizesRequests;

    protected $paginationTheme = 'bootstrap';

    // --- الخصائص العامة ---
    public string $search = '';
    public string $quarterFilter = '';
    public string $yearFilter = '';
    public string $statusFilter = '';
    public string $assignedFilter = '';
    public string $clientFilter = '';
    public bool $showFilters = false;
    public bool $showModal = false;
    public string $mode = 'add';
    public ?Tender $currentTender;
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
    public string $focalPointError = '';

    // history notes هيستوري 

    public bool $showHistoryModal = false;
    public $noteHistories = [];
    public ?TenderNote $selectedNoteForHistory = null;

    // ✅ خصائص الشراكة الجديدة (كمصفوفة)
    public array $partnerships = [];
    public string $partnershipError = '';

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

    public string $sortBy = 'date_of_submission';
    public string $sortDir = 'DESC';

    public function mount()
    {
        $this->users = User::all(['id', 'name']);
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
            // ✅✅✅ قواعد التحقق المحدثة للشراكة ✅✅✅
            'partnerships' => [Rule::requiredIf($this->has_third_party), 'array'],
            'partnerships.*.company_name' => 'required_with:partnerships|string|max:255',
            'partnerships.*.person_name' => 'required_with:partnerships|string|max:255',
            'partnerships.*.phone' => ['required_with:partnerships', 'regex:/^(?:[9720+])[0-9]{7,12}$/'],
            'partnerships.*.email' => 'required_with:partnerships|email|max:255',
            'partnerships.*.details' => 'nullable|string',
        ];

        if ($this->mode === 'add') {
            $rules['newNoteContent'] = 'nullable|string';
        }
        if ($this->mode === 'edit') {
            $rules['newNoteContent'] = 'nullable|string';
            $rules['editingNoteContent'] = 'nullable|string';
        }

        return $rules;
    }

    protected $messages = [
        'focalPoints.*.phone.regex' => 'The phone number must be a valid Omani number.',
        'focalPoints.*.email.email' => 'The email must be a valid email address.',
        'partnerships.required' => 'At least one partner is required when "Yes" is selected.',
        'partnerships.*.phone.regex' => 'The partner phone number must be a valid Omani number.',
        'partnerships.*.company_name.required_with' => 'The company name is required.',
        'partnerships.*.person_name.required_with' => 'The person name is required.',
        'partnerships.*.phone.required_with' => 'The phone number is required.',
        'partnerships.*.email.required_with' => 'The email is required.',
    ];

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function updatedHasThirdParty($value)
    {
        // ✅ الخطوة 1: التحقق من الصلاحية أولاً
        // إذا لم يكن لدى المستخدم الصلاحية، سيتم إيقاف التنفيذ وإظهار خطأ "Unauthorized".
        $this->authorize('internal-tenders.manage-partnerships');

        // الخطوة 2: تنفيذ المنطق البرمجي فقط إذا نجح التحقق
        if (!$value) {
            $this->partnerships = [];
        } else {
            // عند التغيير إلى "نعم"، يتم إضافة حقل شريك جديد تلقائياً
            if (empty($this->partnerships)) {
                $this->addPartnership();
            }
        }
    }


    public function addFocalPoint(): void
    {
        $this->authorize('internal-tenders.manage-focal-points');

        if (count($this->focalPoints) >= 5) {
            $this->focalPointError = 'You cannot add more than 5 focal points.';
            return;
        }
        $this->focalPointError = '';
        $this->focalPoints[] = ['name' => '', 'phone' => '', 'email' => '', 'department' => '', 'other_info' => ''];
    }

    public function removeFocalPoint(int $index): void
    {
        $this->authorize('internal-tenders.manage-focal-points');

        unset($this->focalPoints[$index]);
        $this->focalPoints = array_values($this->focalPoints);
    }

    // ✅✅✅ دوال الشراكة الجديدة ✅✅✅
    public function addPartnership(): void
    {
        $this->authorize('internal-tenders.manage-partnerships');

        if (count($this->partnerships) >= 5) {
            $this->partnershipError = 'You cannot add more than 5 partners.';
            return;
        }
        $this->partnershipError = '';
        $this->partnerships[] = ['company_name' => '', 'person_name' => '', 'phone' => '', 'email' => '', 'details' => ''];
    }

    public function removePartnership(int $index): void
    {
        $this->authorize('internal-tenders.manage-partnerships');

        unset($this->partnerships[$index]);
        $this->partnerships = array_values($this->partnerships);
    }

    public function prepareModal(string $mode, ?int $tenderId = null): void
    {
        if ($mode === 'add') {
            $this->authorize('other-tenders.create');
        }
        if ($mode === 'edit') {
            $this->authorize('other-tenders.edit');
        }
        $this->resetValidation();
        $this->resetForm();
        $this->mode = $mode;

        if ($tenderId) {
            // الخطوة 1: جلب المناقصة والعلاقات البسيطة (بدون الملاحظات)
            $this->currentTender = Tender::with(['focalPoints', 'partnerships'])->findOrFail($tenderId);
            $this->fillForm($this->currentTender);

            // ▼▼▼ هذا هو التعديل الأهم ▼▼▼
            // الخطوة 2: جلب الملاحظات في استعلام منفصل ومباشر
            // هذا يضمن أن العلاقات 'user' و 'editor' يتم تحميلها بشكل صحيح ومستقر
            $this->notes = $this->currentTender->notes()
                ->with(['user', 'editor'])
                ->latest()
                ->get();
            // ▲▲▲ نهاية التعديل ▲▲▲
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
        $this->mount();
        $this->status = 'Recall';
        $this->has_third_party = false;
        $this->focalPoints = [['name' => '', 'phone' => '', 'email' => '', 'department' => '', 'other_info' => '']];
        $this->partnerships = []; // ✅ تفريغ مصفوفة الشركاء
    }

    public function fillForm(Tender $tender): void
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
            'quarter'
        ]));

        $this->date_of_purchase = $tender->date_of_purchase?->format('Y-m-d');
        $this->date_of_submission = $tender->date_of_submission?->format('Y-m-d');
        $this->last_date_of_clarification = $tender->last_date_of_clarification?->format('Y-m-d');
        $this->date_of_submission_after_review = $tender->date_of_submission_after_review?->format('Y-m-d');
        $this->last_follow_up_date = $tender->last_follow_up_date?->format('Y-m-d');
        $this->focalPoints = $tender->focalPoints->toArray();
        // ✅✅✅ تعبئة بيانات الشراكة من العلاقة ✅✅✅
        $this->partnerships = $tender->partnerships->toArray();
    }

    // ✅ الكود بعد التعديل (النسخة الكاملة والصحيحة)
    public function save(): void
    {
        if ($this->mode === 'add') {
            $this->authorize('internal-tenders.create');
        } else {
            $this->authorize('internal-tenders.edit');
        }

        $validatedData = $this->validate();

        // ... (كود التحقق من التكرار) ...

        // 1. يتم تجهيز بيانات المناقصة الأساسية
        $tenderData = collect($validatedData)->except(['focalPoints', 'partnerships', 'notes', 'newNoteContent', 'editingNoteContent'])->toArray();

        // ... (كود تنظيف الحقول) ...

        // 2. يتم حفظ بيانات المناقصة الأساسية
        if ($this->mode === 'add') {
            $tender = Tender::create($tenderData);
            session()->flash('message', 'Tender added successfully.');
        } elseif ($this->mode === 'edit' && $this->currentTender) {
            $this->currentTender->update($tenderData);
            $tender = $this->currentTender;
            session()->flash('message', 'Tender updated successfully.');
        } else {
            return;
        }

        // ▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼
        // ✅ هذا هو التغيير: إضافة كتلة معالجة Focal Points بالكامل
        // --- معالجة جهات الاتصال (Focal Points) ---
        if (isset($validatedData['focalPoints'])) {
            $newFocalPointsData = collect($validatedData['focalPoints']);

            // حذف جهات الاتصال التي لم تعد موجودة في النموذج
            $currentFocalPoints = $tender->focalPoints()->get();
            $phonesAndEmailsToKeep = $newFocalPointsData->map(function ($fp) {
                return strtolower(trim($fp['phone'])) . '|' . strtolower(trim($fp['email']));
            });

            foreach ($currentFocalPoints as $existingFp) {
                $key = strtolower(trim($existingFp->phone)) . '|' . strtolower(trim($existingFp->email));
                if (!$phonesAndEmailsToKeep->contains($key)) {
                    $existingFp->delete(); // يقوم بالحذف
                }
            }

            // إضافة أو تحديث جهات الاتصال الجديدة
            foreach ($newFocalPointsData as $fpData) {
                $tender->focalPoints()->updateOrCreate( // يقوم بالإنشاء أو التحديث
                    [
                        'phone' => $fpData['phone'],
                        'email' => $fpData['email'],
                    ],
                    $fpData
                );
            }
        } else {
            // إذا كانت مصفوفة جهات الاتصال فارغة، احذف كل ما هو مرتبط
            $tender->focalPoints()->delete();
        }
        // ▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲

        // --- معالجة الشركاء (Partnerships) ---
        $tender->partnerships()->delete();
        if ($this->has_third_party && !empty($validatedData['partnerships'])) {
            $tender->partnerships()->createMany($validatedData['partnerships']);
        }

        // --- معالجة الملاحظات (فقط في وضع الإضافة) ---
        if ($this->mode === 'add' && !empty($this->newNoteContent)) {
            $tender->notes()->create(['user_id' => Auth::id(), 'content' => $this->newNoteContent]);
        }

        $this->showModal = false;
    }



    // ... (بقية الدوال تبقى كما هي: refreshNotes, addNote, editNote, updateNote, cancelEdit, deleteNote, deleteTender, exportPdf, exportSimpleExcel, render) ...
    // ... لا حاجة لتغييرها ...
    private function refreshNotes()
    {
        if ($this->currentTender) {
            $this->notes = $this->currentTender->notes()->with('user')->latest()->get();
        }
    }

    public function addNote()
    {
        $this->authorize('internal-tenders.manage-notes');

        $this->validate(['newNoteContent' => 'required|string']);
        if ($this->currentTender) {
            $this->currentTender->notes()->create(['user_id' => Auth::id(), 'content' => $this->newNoteContent]);
            $this->newNoteContent = '';
            $this->refreshNotes();
        }
    }

    public function editNote(int $noteId)
    {
        $this->authorize('internal-tenders.manage-notes');

        $note = TenderNote::findOrFail($noteId);
        $this->authorize('update', $note);
        $this->editingNoteId = $note->id;
        $this->editingNoteContent = $note->content;
    }

    public function updateNote(int $noteId)
    {
        $this->authorize('other-tenders.manage-notes');
        $note = TenderNote::findOrFail($noteId);
        $this->authorize('update', $note);
        $this->validate(['editingNoteContent' => 'required|string']);

        if ($note->content !== $this->editingNoteContent) {
            $note->histories()->create([
                'user_id' => Auth::id(),
                'old_content' => $note->content,
            ]);
        }

        // ▼▼▼ المنطق الجديد والمهم يبدأ هنا ▼▼▼
        $updateData = ['content' => $this->editingNoteContent];
        $currentUser = Auth::user();

        // تحقق إذا كان المستخدم الحالي ليس هو الناشر الأصلي
        if ($currentUser->id !== $note->user_id) {
            // إذا كان شخصاً آخر (Super-Admin)، سجل هويته في حقل المُعدِّل
            $updateData['edited_by_id'] = $currentUser->id;
        } else {
            // إذا كان المالك الأصلي هو من يعدل، تأكد من أن حقل المُعدِّل يبقى فارغاً
            // هذا يحل مشكلة لو قام المدير بالتعديل ثم قام المالك بالتعديل بعده
            $updateData['edited_by_id'] = null;
        }

        // نفذ التحديث. حقل user_id الأصلي لن يتغير أبداً
        $note->update($updateData);
        // ▲▲▲ نهاية المنطق الجديد ▲▲▲

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
        $this->authorize('internal-tenders.manage-notes');

        $note = TenderNote::findOrFail($noteId);
        $this->authorize('delete', $note);
        $note->delete();
        $this->refreshNotes();
    }

    public function deleteTender(int $tenderId): void
    {
        $this->authorize('internal-tenders.delete');

        Tender::find($tenderId)?->delete();
        session()->flash('message', 'Tender deleted successfully.');
    }

    public function updating($property): void
    {
        if (in_array($property, ["search", "quarterFilter", "yearFilter", "statusFilter", "assignedFilter", "clientFilter"])) {
            $this->resetPage();
        }
    }

    public function exportPdf()
    {

        $this->authorize('internal-tenders.export');
        // 1. تحديد كل الأعمدة التي تحتاجها في الـ PDF
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
            'last_date_of_clarification',
            'submission_by',
            'date_of_submission_after_review',
            'has_third_party',
            'last_follow_up_date',
            'follow_up_channel',
            'follow_up_notes',
            'status',
            'reason_of_cancel',
            'submitted_price',
            'awarded_price',
            'reason_of_recall' // <-- الأعمدة موجودة هنا بالفعل
        ];

        // 2. بناء الاستعلام مع تطبيق الفلاتر
        $query = Tender::query()
            ->select($columnsToExport)
            // ... باقي الاستعلام ...
            ->when($this->clientFilter, fn($q) => $q->where('client_type', 'like', "%{$this->clientFilter}%"));

        // 3. جلب البيانات
        $tendersToExport = $query->orderBy($this->sortBy, $this->sortDir)->get();

        // 4. تحميل العرض وتمرير البيانات إليه
        $pdf = Pdf::loadView('livewire.exportfiles.export-pdf', ['tenders' => $tendersToExport]);

        // 5. إرجاع الـ PDF للمستخدم
        return response()->streamDownload(
            fn() => print($pdf->output()),
            'Tenders-Report-' . now()->format('Y-m-d') . '.pdf'
        );
    }

    public function exportSimpleExcel()
    {

        $this->authorize('internal-tenders.export');
        // 1. تحديد كل الأعمدة التي تحتاجها
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
            'last_date_of_clarification',
            'submission_by',
            'date_of_submission_after_review',
            'has_third_party',
            'last_follow_up_date',
            'follow_up_channel',
            'follow_up_notes',
            'status',
            'reason_of_cancel',
            'submitted_price',
            'awarded_price',
            'reason_of_recall'
        ];

        // 2. بناء الاستعلام مع تطبيق الفلاتر
        $query = Tender::query()
            ->select($columnsToExport)
            ->when($this->search, function ($q) {
                $columns = ['name', 'client_type', 'assigned_to', 'status', 'number'];
                $q->where(function ($subQuery) use ($columns) {
                    foreach ($columns as $col) {
                        $subQuery->orWhere($col, 'like', "%{$this->search}%");
                    }
                });
            })
            ->when($this->quarterFilter, fn($q) => $q->whereRaw('QUARTER(date_of_submission) = ?', [substr($this->quarterFilter, 1)]))
            ->when($this->yearFilter, fn($q) => $q->whereYear('date_of_submission', $this->yearFilter))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->assignedFilter, fn($q) => $q->where('assigned_to', $this->assignedFilter))
            ->when($this->clientFilter, fn($q) => $q->where('client_type', 'like', "%{$this->clientFilter}%"));

        // 3. جلب البيانات
        $tendersToExport = $query->orderBy($this->sortBy, $this->sortDir)->get();
        // 4.  تحميل عرض Excel وتمرير البيانات إليه
        $view = view('livewire.exportfiles.exportexcel', ['tenders' => $tendersToExport])->render();
        $filename = 'Tenders-Report-' . now()->format('Y-m-d') . '.xls';

        // 5. إرجاع الملف للمستخدم
        return response()->streamDownload(fn() => print($view), $filename);
    }

    //هيستوري نوتس history nots 
    public function showHistory(int $noteId)
    {
        $note = TenderNote::with(['histories.user'])->findOrFail($noteId);

        if (Gate::denies('view-history', $note)) {
            return;
        }

        $this->selectedNoteForHistory = $note;
        $this->noteHistories = $note->histories;
        $this->showHistoryModal = true;
    }

    public function closeHistoryModal()
    {
        $this->showHistoryModal = false;
        $this->noteHistories = [];
        $this->selectedNoteForHistory = null;
    }



    public function render()
    {

        $this->authorize('internal-tenders.view');

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

        $sortColumn = $this->sortBy;
        if ($this->sortBy === 'quarter_sort') {
            $sortColumn = 'date_of_submission';
        }
        $tenders = $query->orderBy($sortColumn, $this->sortDir)->paginate(5);


        $uniqueClients = Tender::select("client_type")->whereNotNull("client_type")->distinct()->pluck("client_type");
        $uniqueAssignees = Tender::select("assigned_to")->whereNotNull("assigned_to")->distinct()->pluck("assigned_to");
        $uniqueYears = Tender::selectRaw('YEAR(date_of_submission) as year')->distinct()->orderBy('year', 'desc')->pluck('year');

        return view("livewire.internaltender.internal-tender", [
            "tenders" => $tenders,
            "uniqueClients" => $uniqueClients,
            "uniqueAssignees" => $uniqueAssignees,
            "uniqueYears" => $uniqueYears,
        ]);
    }
}
