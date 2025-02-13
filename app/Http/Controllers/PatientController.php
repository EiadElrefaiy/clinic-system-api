<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Stancl\Tenancy\Facades\Tenancy;

class PatientController extends Controller
{
    // 📌 عرض المرضى الخاصين بالمستخدم الحالي
    public function index()
    {
        $user = Auth::user();

        return response()->json([
            'status' => true,
            'patients' => Patient::where('user_id', $user->id)->paginate(30)
        ], 200);
    }

    // 🆕 إنشاء مريض جديد وربطه بالمستخدم
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:patients',
            'phone' => 'required|string|max:20|unique:patients',
            'dob' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'address' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $patient = Patient::create($request->only([
            'name', 'email', 'phone', 'dob', 'gender', 'address'
        ]) + ['user_id' => Auth::id()]);

        return response()->json([
            'status' => true,
            'message' => 'Patient created successfully',
            'patient' => $patient
        ], 201);
    }

    // 🔍 عرض بيانات مريض معين بشرط يكون تابع للمستخدم
    public function show($id)
    {
        $patient = Patient::where('id', $id)
                          ->where('user_id', Auth::id())
                          ->firstOrFail();

        return response()->json([
            'status' => true,
            'patient' => $patient
        ], 200);
    }

    // ✏️ تحديث بيانات مريض بشرط يكون تابع للمستخدم
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:patients,email,' . $id,
            'phone' => 'required|string|max:20|unique:patients,phone,' . $id,
            'dob' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'address' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $patient = Patient::where('id', $id)
                          ->where('user_id', Auth::id())
                          ->firstOrFail();

        $patient->update($request->only([
            'name', 'email', 'phone', 'dob', 'gender', 'address'
        ]));

        return response()->json([
            'status' => true,
            'message' => 'Patient updated successfully',
            'patient' => $patient
        ], 200);
    }

    // 🗑️ حذف مريض بشرط يكون تابع للمستخدم
    public function destroy($id)
    {
        $patient = Patient::where('id', $id)
                          ->where('user_id', Auth::id())
                          ->firstOrFail();

        $patient->delete();

        return response()->json([
            'status' => true,
            'message' => 'Patient deleted successfully'
        ], 200);
    }
}
