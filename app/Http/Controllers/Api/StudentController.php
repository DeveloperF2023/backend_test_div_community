<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'classroom_id' => 'required|exists:classrooms,id',
        ]);

        if ($request->hasFile('profile_picture')) {
            $imagePath = $request->file('profile_picture')->store('profile_pictures', 'public');
            $validated['profile_picture'] = $imagePath;
        }

        $student = Student::create($validated);

        return response()->json($student, 201);
    }
    public function index()
    {
        $students = Student::with('classroom:id,name')->get();

        $students = $students->map(function ($student) {
            return [
                'id' => $student->id,
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
                'profile_picture' => $student->profile_picture,
                'classroom_id' => $student->classroom_id,
                'classroom_name' => $student->classroom ? $student->classroom->name : null,
                'gender' => $student->gender,
                'birth_date' => $student->birth_date,
                'cne' => $student->cne,
                'level' => $student->level,
                'section' => $student->section,
                'city' => $student->city,
                'phone' => $student->phone,
                'created_at' => $student->created_at,
                'updated_at' => $student->updated_at,
            ];
        });

        return response()->json($students);
    }
    public function filter(Request $request)
    {
        $query = Student::query();

        // Filter by gender
        if ($request->has('gender')) {
            $query->where('gender', $request->gender);
        }

        // Filter by level
        if ($request->has('level')) {
            $query->where('level', $request->level);
        }

        // Filter by attendance status
        if ($request->has('attendance_status')) {
            $query->whereHas('attendances', function ($q) use ($request) {
                $q->where('status', $request->attendance_status);

                if ($request->has('from_date')) {
                    $q->whereDate('date', '>=', $request->from_date);
                }

                if ($request->has('to_date')) {
                    $q->whereDate('date', '<=', $request->to_date);
                }
            });
        }

        // Paginate or get results
        $students = $query->with('classroom')->get();

        return response()->json($students);
    }
    public function getByGender($gender)
    {
        if (!in_array($gender, ['male', 'female'])) {
            return response()->json(['error' => 'Invalid gender value.'], 422);
        }

        $students = Student::where('gender', $gender)->with('classroom')->get();

        return response()->json($students);
    }
    public function show($id)
    {
        $student = Student::with('classroom')->find($id);

        if (!$student) {
            return response()->json([
                'message' => 'Student not found',
            ], 404);
        }

        $formattedStudent = [
            'id' => $student->id,
            'first_name' => $student->first_name,
            'last_name' => $student->last_name,
            'profile_picture' => $student->profile_picture,
            'classroom_id' => $student->classroom_id,
            'classroom_name' => $student->classroom ? $student->classroom->name : null,
            'gender' => $student->gender,
            'birth_date' => $student->birth_date,
            'cne' => $student->cne,
            'level' => $student->level,
            'section' => $student->section,
            'city' => $student->city,
            'phone' => $student->phone,
            'created_at' => $student->created_at,
            'updated_at' => $student->updated_at,
        ];

        return response()->json($formattedStudent);
    }
}
