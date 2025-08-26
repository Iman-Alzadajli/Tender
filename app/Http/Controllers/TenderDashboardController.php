<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ETender\ETender;
use App\Models\InternalTender\InternalTender;
use App\Models\OtherTenderPlatform\OtherTender;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TenderDashboardController extends Controller
{
    public function index()
    {
        // 1. تحديد الأعمدة وجلب البيانات
        $columns = ['id', 'name', 'status', 'date_of_submission', 'client_type', DB::raw("'e_tender' as tender_type")];
        $eTendersQuery = ETender::select($columns);
        $columns[5] = DB::raw("'internal_tender' as tender_type");
        $internalTendersQuery = InternalTender::select($columns);
        $columns[5] = DB::raw("'other_tender' as tender_type");
        $otherTendersQuery = OtherTender::select($columns);

        $allTenders = $eTendersQuery
            ->unionAll($internalTendersQuery)
            ->unionAll($otherTendersQuery)
            ->get();

        // 2. تنظيف وتوحيد أسماء الحالات (للاستخدام في كل الأقسام)
        $allTenders->transform(function ($tender) {
            if ($tender->status) {
                $cleanedStatus = strtolower(trim($tender->status));
                $tender->status = str_replace(' ', '_', $cleanedStatus);
            }
            return $tender;
        });

        // --- القسم الأول: حساب بيانات البطاقات (Status Cards) ---
        // هذا الجزء يستخدم "كل" البيانات المجمعة والنظيفة لحساب الإحصائيات
        // لا يوجد أي فلترة هنا، وهذا هو المطلوب.
        $statusCounts = $allTenders->countBy('status');


        // --- القسم الثاني: فلترة المناقصات العاجلة (Urgent Tenders) ---
        // هذا الجزء يستخدم نسخة "مفلترة" من البيانات
        $today = Carbon::today();
        $threeDaysFromNow = Carbon::today()->addDays(3);
        $activeStatuses = ['open', 'pending', 'under_evaluation']; // الفلتر المطلوب

        $urgentTenders = $allTenders
            ->whereIn('status', $activeStatuses) // الشرط الأول: الحالة نشطة
            ->whereNotNull('date_of_submission')
            ->filter(function ($tender) use ($today, $threeDaysFromNow) {
                // الشرط الثاني: التاريخ خلال 3 أيام
                try {
                    return Carbon::parse($tender->date_of_submission)->between($today, $threeDaysFromNow);
                } catch (\Exception $e) {
                    return false;
                }
            })
            ->sortBy('date_of_submission');


        // --- القسم الثالث: الرسوم البيانية (تستخدم كل البيانات) ---
        $tendersByQuarter = $allTenders
            ->whereNotNull('date_of_submission')
            ->groupBy(function ($tender) {
                try {
                    return "Q" . Carbon::parse($tender->date_of_submission)->quarter;
                } catch (\Exception $e) {
                    return 'Invalid Date';
                }
            });
        
        $tendersByQuarter->forget('Invalid Date');
        $tendersByQuarter = $tendersByQuarter->map->count();

        $tenderQuantities = [
            'Q1' => $tendersByQuarter->get('Q1', 0),
            'Q2' => $tendersByQuarter->get('Q2', 0),
            'Q3' => $tendersByQuarter->get('Q3', 0),
            'Q4' => $tendersByQuarter->get('Q4', 0),
        ];

        $clientTypes = $allTenders->whereNotNull('client_type')->countBy('client_type');

        // --- تمرير البيانات للعرض ---
        return view('dashboard', [
            'statusCounts' => $statusCounts,
            'urgentTenders' => $urgentTenders,
            'tenderQuantitiesJson' => json_encode($tenderQuantities),
            'clientTypesJson' => json_encode($clientTypes),
        ]);
    }
}
