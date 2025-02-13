<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AppointmentController extends Controller
{
    // 📌 عرض جميع المواعيد مع بيانات المريض
    public function index()
    {
        $user = Auth::user();
        return response()->json([
            'status' => true,
            'appointments' => Appointment::where('user_id', $user->id)
                                         ->with('patient')
                                         ->paginate(30)
        ], 200);
    }
    
    // 🆕 إنشاء موعد جديد
    public function store(Request $request)
    {
        $user = Auth::user();
    
        $validator = Validator::make($request->all(), [
            'patient_id' => 'required|exists:patients,id',
            'registration_date' => 'required|date',
            'appointment_date' => 'required|date|after_or_equal:registration_date',
            'time' => 'required|date_format:H:i',
            'reason_for_visit' => 'nullable|string',
            'visit_status' => 'required|in:first_time,re_app',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // ✅ تأكيد أن `patient_id` يخص المستخدم الحالي
        $patient = Patient::where('id', $request->patient_id)
                          ->where('user_id', $user->id)
                          ->first();
        if (!$patient) {
            return response()->json([
                'status' => false,
                'message' => 'Patient not found or does not belong to you'
            ], 403);
        }
    
        // 🏥 إنشاء الموعد وربطه بالمستخدم
        $appointment = Appointment::create([
            'user_id' => $user->id,
            'patient_id' => $request->patient_id,
            'registration_date' => $request->registration_date,
            'appointment_date' => $request->appointment_date,
            'time' => $request->time,
            'reason_for_visit' => $request->reason_for_visit,
            'visit_status' => $request->visit_status,
        ]);
    
        return response()->json([
            'status' => true,
            'message' => 'Appointment created successfully',
            'appointment' => $appointment
        ], 201);
    }
    
    // 🔍 عرض موعد معين حسب ID
    public function show($id)
    {
        try {
            $appointment = $this->findOrFailWithUserCheck($id);
            return response()->json([
                'status' => true,
                'appointment' => $appointment
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Appointment not found'
            ], 404);
        }
    }
    
    // ✏️ تحديث بيانات موعد معين
    public function update(Request $request, $id)
    {
        try {
            $appointment = $this->findOrFailWithUserCheck($id);
    
            $validator = Validator::make($request->all(), [
                'patient_id' => 'required|exists:patients,id',
                'registration_date' => 'required|date',
                'appointment_date' => 'required|date|after_or_equal:registration_date',
                'time' => 'required|date_format:H:i',
                'reason_for_visit' => 'nullable|string',
                'visit_status' => 'required|in:first_time,re_app',
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation Failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // ✅ تأكيد أن `patient_id` يخص المستخدم الحالي
            $user = Auth::user();
            $patient = Patient::where('id', $request->patient_id)
                              ->where('user_id', $user->id)
                              ->first();
            if (!$patient) {
                return response()->json([
                    'status' => false,
                    'message' => 'Patient not found or does not belong to you'
                ], 403);
            }
    
            // 🔄 تحديث بيانات الموعد
            $appointment->update($request->only([
                'patient_id',
                'registration_date',
                'appointment_date',
                'time',
                'reason_for_visit',
                'visit_status'
            ]));
    
            return response()->json([
                'status' => true,
                'message' => 'Appointment updated successfully',
                'appointment' => $appointment
            ], 200);
    
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Appointment not found'
            ], 404);
        }
    }
    
    // 🗑️ حذف موعد معين
    public function destroy($id)
    {
        try {
            $appointment = $this->findOrFailWithUserCheck($id);
            $appointment->delete();
    
            return response()->json([
                'status' => true,
                'message' => 'Appointment deleted successfully'
            ], 200);
            
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Appointment not found'
            ], 404);
        }
    }

    // ✅ دالة خاصة للبحث عن الموعد والتحقق من المستخدم
    private function findOrFailWithUserCheck($id)
    {
        return Appointment::where('id', $id)
                          ->where('user_id', Auth::id())
                          ->with('patient')
                          ->firstOrFail();
    }
}

