<?php

namespace App\Livewire\ContactList;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Partnership;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.app')]
class PartnershipsList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // --- خصائص الفلترة والبحث ---
    public string $search = '';
    public string $clientFilter = '';
    public string $clientTypeFilter = '';
    public string $tenderTypeFilter = '';
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

    // --- خصائص نافذة الحذف ---
    public bool $showDeleteModal = false;
    public ?int $deletingPartnershipId = null;
    public ?string $deletingPartnershipName = '';

    // ✅✅✅ الكود الناقص يبدأ من هنا ✅✅✅
    // --- خصائص نافذة الإنشاء ---
    public bool $showAddModal = false;
    public ?int $selectedTenderId = null;
    public ?string $selectedTenderType = null;
    // ✅✅✅ نهاية الكود الناقص ✅✅✅

    public function updating($property)
    {
        if (in_array($property, ['search', 'clientFilter', 'clientTypeFilter', 'tenderTypeFilter'])) {
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

    public function editPartnership(Partnership $partnership)
    {
        $this->editingPartnership = $partnership;
        $this->p_company_name = $partnership->company_name;
        $this->p_person_name = $partnership->person_name;
        $this->p_phone = $partnership->phone;
        $this->p_email = $partnership->email;
        $this->p_details = $partnership->details;
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

        $existingPartner = Partnership::where('phone', $this->p_phone)
            ->where('email', $this->p_email)
            ->where('id', '!=', $this->editingPartnership->id)
            ->with('partnerable')
            ->first();

        if ($existingPartner) {
            $currentClientName = $this->editingPartnership->partnerable->client_name;
            $existingClientName = $existingPartner->partnerable->client_name;

            if ($currentClientName === $existingClientName) {
                $this->addError('p_email', 'This contact (phone & email) is already registered for this client under a different tender.');
                return;
            }
        }

        $this->editingPartnership->update($validatedData);
        session()->flash('message', 'Partnership updated successfully.');
        $this->closeModal();
    }

    public function confirmDelete($id)
    {
        $partnership = Partnership::find($id);
        if (!$partnership) return;
        $this->deletingPartnershipId = $id;
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
        $this->showAddModal = false; // ✅ تأكد من إغلاق نافذة الإنشاء أيضاً
        $this->reset(['editingPartnership', 'p_company_name', 'p_person_name', 'p_phone', 'p_email', 'p_details', 'selectedTenderId', 'selectedTenderType']);
        $this->resetValidation();
    }

    // ✅✅✅ الكود الناقص يبدأ من هنا ✅✅✅
    public function openAddModal()
    {
        $this->reset(['p_company_name', 'p_person_name', 'p_phone', 'p_email', 'p_details', 'selectedTenderId', 'selectedTenderType']);
        $this->resetValidation();
        $this->showAddModal = true;
    }

    public function storePartnership()
    {
        $validatedData = $this->validate([
            'selectedTenderId' => 'required|integer',
            'selectedTenderType' => 'required|string',
            'p_company_name' => 'required|string|max:255',
            'p_person_name' => 'required|string|max:255',
            'p_phone' => 'required|regex:/^(?:[9720+])[0-9]{7,12}$/',
            'p_email' => 'required|email|max:255',
            'p_details' => 'nullable|string',
        ]);

        $existingPartner = Partnership::where('phone', $this->p_phone)
            ->where('email', $this->p_email)
            ->with('partnerable')
            ->first();

        if ($existingPartner) {
            $tenderModel = $this->selectedTenderType;
            $tender = $tenderModel::find($this->selectedTenderId);
            if (!$tender) {
                session()->flash('error', 'Selected tender not found.');
                return;
            }
            $currentClientName = $tender->client_name;
            $existingClientName = $existingPartner->partnerable->client_name;

            if ($currentClientName === $existingClientName) {
                $this->addError('p_email', 'This contact (phone & email) is already registered for this client.');
                return;
            }
        }

        $tender = ($tender) ?? app($this->selectedTenderType)->find($this->selectedTenderId);
        $tender->partnerships()->create([
            'company_name' => $this->p_company_name,
            'person_name' => $this->p_person_name,
            'phone' => $this->p_phone,
            'email' => $this->p_email,
            'details' => $this->p_details,
        ]);

        session()->flash('message', 'Partnership added successfully.');
        $this->closeModal(); // استخدم closeModal لإغلاق النافذة وتصفير الحقول
    }
    // ✅✅✅ نهاية الكود الناقص ✅✅✅

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
        $partnershipsQuery = Partnership::query()->select('partnerships.*')->with(['partnerable']);

        $partnershipsQuery->leftJoin('internal_tenders', function ($join) {
            $join->on('partnerships.partnerable_id', '=', 'internal_tenders.id')->where('partnerships.partnerable_type', '=', 'App\Models\InternalTender\InternalTender');
        })
            ->leftJoin('e_tenders', function ($join) {
                $join->on('partnerships.partnerable_id', '=', 'e_tenders.id')->where('partnerships.partnerable_type', '=', 'App\Models\ETender\ETender');
            })
            ->leftJoin('other_tenders', function ($join) {
                $join->on('partnerships.partnerable_id', '=', 'other_tenders.id')->where('partnerships.partnerable_type', '=', 'App\Models\OtherTenderPlatform\OtherTender');
            });

        $partnershipsQuery->addSelect(
            DB::raw('COALESCE(internal_tenders.client_name, e_tenders.client_name, other_tenders.client_name) as client_name'),
            DB::raw('COALESCE(internal_tenders.client_type, e_tenders.client_type, other_tenders.client_type) as client_type')
        );

        $partnershipsQuery->when($this->clientFilter, fn($q) => $q->where(DB::raw('COALESCE(internal_tenders.client_name, e_tenders.client_name, other_tenders.client_name)'), $this->clientFilter))
            ->when($this->clientTypeFilter, fn($q) => $q->where(DB::raw('COALESCE(internal_tenders.client_type, e_tenders.client_type, other_tenders.client_type)'), $this->clientTypeFilter))
            ->when($this->tenderTypeFilter, fn($q) => $q->where('partnerable_type', $this->tenderTypeFilter));

        $partnershipsQuery->when($this->search, function ($query) {
            $query->where(function ($subQuery) {
                $subQuery->where('partnerships.company_name', 'like', '%' . $this->search . '%')
                    ->orWhere('partnerships.person_name', 'like', '%' . $this->search . '%')
                    ->orWhere('partnerships.email', 'like', '%' . $this->search . '%')
                    ->orWhere('partnerships.phone', 'like', '%' . $this->search . '%')
                    ->orWhere(DB::raw('COALESCE(internal_tenders.client_name, e_tenders.client_name, other_tenders.client_name)'), 'like', '%' . $this->search . '%');
            });
        });

        $clients = Partnership::with('partnerable')->get()->pluck('partnerable.client_name')->unique()->filter()->sort();
        $clientTypes = Partnership::with('partnerable')->get()->pluck('partnerable.client_type')->unique()->filter()->sort();

        $partnerships = $partnershipsQuery->orderBy($this->sortBy, $this->sortDir)->paginate(10);

        return view('livewire.contact-list.partnerships-list', [
            'partnerships' => $partnerships,
            'clients' => $clients,
            'clientTypes' => $clientTypes,
        ]);
    }
}
