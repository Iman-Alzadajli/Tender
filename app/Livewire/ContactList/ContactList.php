<?php

namespace App\Livewire\ContactList;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use App\Models\ETender\ETender;
use App\Models\InternalTender\InternalTender;
use App\Models\OtherTenderPlatform\OtherTender;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;

#[Layout('layouts.app')]
class ContactList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // --- خصائص الفلترة والبحث ---
    public string $search = '';
    public string $searchClientType = '';
    public string $searchTenderType = '';
    public bool $showFilters = false;

    // --- خصائص الفرز ---
    public string $sortBy = 'name';
    public string $sortDir = 'ASC';

    // --- خصائص نافذة جهة الاتصال ---
    public bool $showFocalPointModal = false;
    public ?int $tenderIdForModal = null;
    public ?string $tenderTypeForModal = null;
    public string $fp_name = '';
    public string $fp_phone = '';
    public string $fp_email = '';
    public string $fp_department = '';
    public string $fp_other_info = '';

    // --- ✅ خصائص نافذة الشريك ---
    public bool $showPartnershipModal = false;
    public string $p_company_name = '';
    public string $p_person_name = '';
    public string $p_phone = '';
    public string $p_email = '';
    public string $p_details = '';

    protected function focalPointRules()
    {
        return [
            'fp_name' => 'required|string|max:255',
            'fp_phone' => 'required|regex:/^(?:[9720+])[0-9]{7,12}$/',
            'fp_email' => 'required|email|max:255',
            'fp_department' => 'required|string|max:255',
            'fp_other_info' => 'nullable|string',
        ];
    }

    protected function partnershipRules()
    {
        return [
            'p_company_name' => 'required|string|max:255',
            'p_person_name' => 'required|string|max:255',
            'p_phone' => ['required', 'regex:/^(?:[9720+])[0-9]{7,12}$/'],
            'p_email' => 'required|email|max:255',
            'p_details' => 'nullable|string',
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

    private function getModelClass($tenderType)
    {
        return match ($tenderType) {
            'internal_tender' => InternalTender::class,
            'e_tender' => ETender::class,
            'other_tender' => OtherTender::class,
            default => null,
        };
    }

    public function loadTenderDetails($tenderType, $tenderId)
    {
        $modelClass = $this->getModelClass($tenderType);
        if (!$modelClass) return [];

        $tender = $modelClass::with(['focalPoints', 'partnerships'])->find($tenderId);

        return [
            'focalPoints' => $tender ? $tender->focalPoints->toArray() : [],
            'partnerships' => $tender ? $tender->partnerships->toArray() : [],
        ];
    }

    public function prepareFocalPointModal($tenderType, $tenderId)
    {
        $this->resetValidation();
        $this->reset(['fp_name', 'fp_phone', 'fp_email', 'fp_department', 'fp_other_info']);
        $this->tenderIdForModal = $tenderId;
        $this->tenderTypeForModal = $tenderType;
        $this->showFocalPointModal = true;
    }

    public function addFocalPoint()
    {
        $this->validate($this->focalPointRules());
        $modelClass = $this->getModelClass($this->tenderTypeForModal);

        if (!$modelClass || !$this->tenderIdForModal) {
            session()->flash('error', 'An unexpected error occurred.');
            return;
        }

        $tender = $modelClass::find($this->tenderIdForModal);

        if ($tender->focalPoints()->count() >= 5) {
            session()->flash('error', 'Cannot add more than 5 focal points to this tender.');
            $this->showFocalPointModal = false;
            return;
        }

        $existing = $tender->focalPoints()
            ->where('phone', $this->fp_phone)
            ->where('email', $this->fp_email)
            ->exists();

        if ($existing) {
            $this->addError('fp_phone', 'This exact contact (phone and email) already exists for this tender.');
            return;
        }

        $tender->focalPoints()->create([
            'name' => $this->fp_name,
            'phone' => $this->fp_phone,
            'email' => $this->fp_email,
            'department' => $this->fp_department,
            'other_info' => $this->fp_other_info,
        ]);

        session()->flash('message', 'Focal point added successfully.');
        $this->showFocalPointModal = false;
        $this->dispatch('details-changed');
    }

    public function preparePartnershipModal($tenderType, $tenderId)
    {
        $this->resetValidation();
        $this->reset(['p_company_name', 'p_person_name', 'p_phone', 'p_email', 'p_details']);
        $this->tenderIdForModal = $tenderId;
        $this->tenderTypeForModal = $tenderType;
        $this->showPartnershipModal = true;
    }

    public function addPartnership()
    {
        $this->validate($this->partnershipRules());
        $modelClass = $this->getModelClass($this->tenderTypeForModal);

        if (!$modelClass || !$this->tenderIdForModal) {
            session()->flash('error', 'An unexpected error occurred.');
            return;
        }

        $tender = $modelClass::find($this->tenderIdForModal);

        $normalizedInput = strtolower(trim($this->p_company_name));
        $existing = $tender->partnerships()->get()->first(function ($partner) use ($normalizedInput) {
            return strtolower(trim($partner->company_name)) === $normalizedInput;
        });

        if ($existing) {
            $this->addError('p_company_name', 'A partner with this company name already exists for this tender.');
            return;
        }

        $tender->partnerships()->create([
            'company_name' => $this->p_company_name,
            'person_name' => $this->p_person_name,
            'phone' => $this->p_phone,
            'email' => $this->p_email,
            'details' => $this->p_details,
        ]);

        $tender->has_third_party = true;
        $tender->save();

        session()->flash('message', 'Partnership added successfully.');
        $this->showPartnershipModal = false;
        $this->dispatch('details-changed');
    }

    private function getTendersQuery()
    {
        $cols = ['id', 'name', 'client_name', 'client_type', 'has_third_party'];

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

    //pdf 
    public function exportPdf()
    {
        // 1. جلب جميع المناقصات مع علاقاتها بناءً على الفلاتر
        $tenderModels = [InternalTender::class, ETender::class, OtherTender::class];
        $allTenders = new Collection();

        foreach ($tenderModels as $model) {
            $query = $model::with(['focalPoints', 'partnerships'])
                ->when($this->search, function ($q) {
                    $q->where(function ($sub) {
                        $sub->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('client_name', 'like', '%' . $this->search . '%');
                    });
                })
                ->when($this->searchClientType, fn($q) => $q->where('client_type', $this->searchClientType));

            $tenders = $query->get()->each(function ($tender) use ($model) {
                $tender->tender_type = match ($model) {
                    InternalTender::class => 'internal_tender',
                    ETender::class => 'e_tender',
                    OtherTender::class => 'other_tender',
                };
            });

            $allTenders = $allTenders->merge($tenders);
        }

        if ($this->searchTenderType) {
            $allTenders = $allTenders->where('tender_type', $this->searchTenderType);
        }

        // 2. ✅ تحويل البيانات إلى "قائمة مسطحة" حسب طلبك
        $flatContacts = new Collection();
        foreach ($allTenders as $tender) {
            // إضافة جهات الاتصال (Focal Points)
            foreach ($tender->focalPoints as $fp) {
                $flatContacts->push((object)[
                    'tender_name' => $tender->name,
                    'client_name' => $tender->client_name,
                    'client_type' => $tender->client_type, // تم إضافته للاستخدام المستقبلي
                    'tender_type' => $tender->tender_type,
                    'contact_type' => 'Focal Point',
                    'contact_name' => $fp->name,         // اسم جهة الاتصال
                    'person_name' => $fp->name,          // اسم الشخص هو نفسه
                    'phone' => $fp->phone,
                    'email' => $fp->email,
                    'department' => $fp->department,
                    'details' => $fp->other_info,        // استخدام other_info كـ details
                ]);
            }
            // إضافة الشركاء (Partnerships)
            foreach ($tender->partnerships as $p) {
                $flatContacts->push((object)[
                    'tender_name' => $tender->name,
                    'client_name' => $tender->client_name,
                    'client_type' => $tender->client_type,
                    'tender_type' => $tender->tender_type,
                    'contact_type' => 'Partnership',
                    'contact_name' => $p->company_name,  // اسم الشركة
                    'person_name' => $p->person_name,   // اسم الشخص المسؤول
                    'phone' => $p->phone,
                    'email' => $p->email,
                    'department' => null,               // لا يوجد قسم للشركاء
                    'details' => $p->details,           // تفاصيل الشراكة
                ]);
            }
        }

        // 3. ترتيب القائمة النهائية (مثلاً حسب اسم المناقصة ثم نوع جهة الاتصال)
        $sortedContacts = $flatContacts->sortBy('tender_name')->values();

        // 4. تحميل العرض وتمرير البيانات (نمرر المناقصات مباشرة)
        $pdf = Pdf::loadView('livewire.exportfiles.exportcontact-pdf', ['contacts' => $sortedContacts]);

        return response()->streamDownload(
            fn() => print($pdf->output()),
            'Tenders-Contact-Report-' . now()->format('Y-m-d') . '.pdf'
        );
    }


    //excel 

    public function exportExcel()
    {
        // 1. جلب جميع المناقصات مع علاقاتها (نفس منطق دالة PDF)
        $tenderModels = [InternalTender::class, ETender::class, OtherTender::class];
        $allTenders = new Collection();

        foreach ($tenderModels as $model) {
            $query = $model::with(['focalPoints', 'partnerships'])
                ->when($this->search, function ($q) {
                    $q->where(function ($sub) {
                        $sub->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('client_name', 'like', '%' . $this->search . '%');
                    });
                })
                ->when($this->searchClientType, fn($q) => $q->where('client_type', $this->searchClientType));

            $tenders = $query->get()->each(function ($tender) use ($model) {
                $tender->tender_type = match ($model) {
                    InternalTender::class => 'internal_tender',
                    ETender::class => 'e_tender',
                    OtherTender::class => 'other_tender',
                };
            });

            $allTenders = $allTenders->merge($tenders);
        }

        if ($this->searchTenderType) {
            $allTenders = $allTenders->where('tender_type', $this->searchTenderType);
        }

        // 2. تحويل البيانات إلى "قائمة مسطحة" (نفس منطق دالة PDF)
        $flatContacts = new Collection();
        foreach ($allTenders as $tender) {
            foreach ($tender->focalPoints as $fp) {
                $flatContacts->push((object)[
                    'tender_name' => $tender->name,
                    'client_name' => $tender->client_name,
                    'tender_type' => $tender->tender_type,
                    'contact_type' => 'Focal Point',
                    'contact_name' => $fp->name,
                    'person_name' => $fp->name,
                    'phone' => $fp->phone,
                    'email' => $fp->email,
                    'department' => $fp->department,
                    'details' => $fp->other_info,
                ]);
            }
            foreach ($tender->partnerships as $p) {
                $flatContacts->push((object)[
                    'tender_name' => $tender->name,
                    'client_name' => $tender->client_name,
                    'tender_type' => $tender->tender_type,
                    'contact_type' => 'Partnership',
                    'contact_name' => $p->company_name,
                    'person_name' => $p->person_name,
                    'phone' => $p->phone,
                    'email' => $p->email,
                    'department' => null,
                    'details' => $p->details,
                ]);
            }
        }

        // 3. ترتيب القائمة النهائية
        $sortedContacts = $flatContacts->sortBy('tender_name')->values();

        // 4. ✅ تحميل عرض Excel وتمرير البيانات
        $view = view('livewire.exportfiles.exportexcel', ['contacts' => $sortedContacts])->render();
        $filename = 'Detailed-Contact-Report-' . now()->format('Y-m-d') . '.xls';

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
