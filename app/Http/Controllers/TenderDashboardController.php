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
        // 1. تحديد الأعمدة المشتركة والمطلوبة بشكل صريح لتجنب أي مشاكل
        $columns = [
            'id',
            'name',
            'status',
            'date_of_submission', // تم تغيير اسم العمود ليتوافق مع طلبك
            'client_type',
            // نضيف نوع المناقصة كعمود ثابت لتحديد مصدرها وروابطها
            DB::raw("'e_tender' as tender_type")
        ];

        // 2. بناء الاستعلامات مع تحديد الأعمدة بشكل صريح
        $eTendersQuery = ETender::select($columns);

        // نغير العمود الثابت لكل جدول
        $columns[5] = DB::raw("'internal_tender' as tender_type");
        $internalTendersQuery = InternalTender::select($columns);

        $columns[5] = DB::raw("'other_tender' as tender_type");
        $otherTendersQuery = OtherTender::select($columns);

        // 3. دمج الاستعلامات المحددة الأعمدة
        $allTendersQuery = $eTendersQuery
            ->unionAll($internalTendersQuery)
            ->unionAll($otherTendersQuery);

        // 4. جلب كل البيانات مرة واحدة فقط
        $allTenders = $allTendersQuery->get();

        // --- حساب بيانات البطاقات (Status Cards) ---
        $statusCounts = $allTenders->countBy('status');

        // --- جلب المناقصات العاجلة (Urgent Tenders) ---
        $today = Carbon::today();
        $threeDaysFromNow = Carbon::today()->addDays(3);

        $urgentTenders = $allTenders
            ->whereNotNull('date_of_submission')
            ->filter(function ($tender) use ($today, $threeDaysFromNow) {
                $submissionDate = Carbon::parse($tender->date_of_submission);
                return $submissionDate->between($today, $threeDaysFromNow);
            })
            ->sortBy('date_of_submission');

        // --- حساب بيانات الرسم البياني (Charts) ---
        // أ. الرسم البياني الشريطي: بناءً على date_of_submission
        $tendersByQuarter = $allTenders
            ->whereNotNull('date_of_submission')
            ->groupBy(function ($tender) {
                // نستخدم try-catch لتجنب الأخطاء إذا كان التاريخ غير صالح
                try {
                    return "Q" . Carbon::parse($tender->date_of_submission)->quarter;
                } catch (\Exception $e) {
                    return 'Invalid Date';
                }
            });
        
        // نزيل أي تواريخ غير صالحة
        $tendersByQuarter->forget('Invalid Date');
        $tendersByQuarter = $tendersByQuarter->map->count();


        $tenderQuantities = [
            'Q1' => $tendersByQuarter->get('Q1', 0),
            'Q2' => $tendersByQuarter->get('Q2', 0),
            'Q3' => $tendersByQuarter->get('Q3', 0),
            'Q4' => $tendersByQuarter->get('Q4', 0),
        ];

        // ب. الرسم البياني الدائري: أنواع العملاء
        $clientTypes = $allTenders->whereNotNull('client_type')->countBy('client_type');

        // --- تمرير كل البيانات إلى العرض (View) ---
        return view('dashboard', [
            'statusCounts' => $statusCounts,
            'urgentTenders' => $urgentTenders,
            'tenderQuantitiesJson' => json_encode($tenderQuantities),
            'clientTypesJson' => json_encode($clientTypes),
        ]);
    }
}
