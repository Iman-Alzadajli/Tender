<?php

namespace App\Providers;

// 1. استيراد الكلاسات الضرورية
use App\Models\User;
use App\Models\TenderNote;
use App\Policies\TenderNotePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // هذا يربط مودل TenderNote مع الكلاس TenderNotePolicy
        TenderNote::class => TenderNotePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // ✅✅✅ هذا هو الكود الجديد الذي أضفناه ✅✅✅
        Gate::define('view-history', function (User $user, TenderNote $note) {
            // اسمح للمستخدم برؤية الهيستوري في حالتين:
            // 1. إذا كان هو المالك الأصلي للملاحظة.
            // 2. إذا كان يمتلك صلاحية 'notes.view-history'.
            return $user->id === $note->user_id || $user->can('notes.view-history');
        });
        // ✅✅✅ نهاية الكود الجديد ✅✅✅

        // هذه الدالة يتم تنفيذها قبل أي تحقق آخر للصلاحيات
        Gate::before(function (User $user, string $ability) {
            // استبدل 'Super-Admin' باسم الدور الفعلي إذا كان مختلفاً
            if ($user->hasRole('Super-Admin')) {
                return true; // امنح الإذن فوراً وتجاوز كل القواعد الأخرى
            }
        });
    }
}
