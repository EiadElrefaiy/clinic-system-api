<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // إجمالي عدد الأطباء (قيمة ثابتة كما في الصورة)
        $totalDoctors = Appointment::count();
    
        // إجمالي عدد المرضى في قاعدة البيانات
        $totalPatients = Patient::count();
    
        // الشهر الحالي
        $currentMonth = now()->month;
    
        // جميع بيانات المرضى في الشهر الحالي مع التقسيم إلى صفحات (10 لكل صفحة)
        $currentMonthPatients = Patient::whereMonth('created_at', $currentMonth)->paginate(10);

        // جميع بيانات المواعيد في الشهر الحالي مع التقسيم إلى صفحات (10 لكل صفحة)
        $currentMonthAppointments = Appointment::whereMonth('created_at', $currentMonth)->paginate(10);
    
        // حساب عدد المرضى شهريًا
        $patientsByMonth = Patient::selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();
    
        // حساب عدد المواعيد شهريًا
        $appointmentsByMonth = Appointment::selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();
    
        // تحويل البيانات إلى مصفوفة كاملة لكل الشهور (من 1 إلى 12)
        $months = range(1, 12);
        $patientsData = array_map(fn($m) => $patientsByMonth[$m] ?? 0, $months);
        $appointmentsData = array_map(fn($m) => $appointmentsByMonth[$m] ?? 0, $months);
    
        // إجمالي المرضى لكل شهر (Patient Total)
        $patientTotal = [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            'data' => $patientsData,
        ];
    
        // إجمالي المواعيد لكل شهر (Appointments Total)
        $appointmentsTotal = [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            'data' => $appointmentsData,
        ];
    
        return response()->json([
            'status' => true,
            'totalDoctors' => $totalDoctors,
            'totalPatients' => $totalPatients,
            'patientTotal' => $patientTotal,
            'appointmentsTotal' => $appointmentsTotal,
            'currentMonthPatients' => $currentMonthPatients, // بيانات المرضى في الشهر الحالي
            'currentMonthAppointments' => $currentMonthAppointments, // بيانات المواعيد في الشهر الحالي
        ], 200);
    }    
}
