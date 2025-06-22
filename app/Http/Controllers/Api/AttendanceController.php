<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator as FacadesValidator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator as ValidationValidator;

class AttendanceController extends Controller
{
    // Get all attendances
    public function index()
    {
        return response()->json(Attendance::all());
    }

    // Create attendance
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'date' => 'required|date',
            'status' => 'required|in:present,absent,late',
        ]);

        $attendance = Attendance::create($validated);

        return response()->json($attendance, 201);
    }

    // Update attendance by id
    public function update(Request $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        $validated = $request->validate([
            'student_id' => 'sometimes|exists:students,id',
            'date' => 'sometimes|date',
            'status' => 'sometimes|in:present,absent,late',
        ]);

        try {
            $attendance->update($validated);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                return response()->json(['error' => 'Duplicate attendance for this student and date.'], 422);
            }
            throw $e;
        }

        return response()->json($attendance);
    }
    public function storeMultipleAttendance(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'records' => 'required|array',
            'records.*.student_id' => 'required|exists:students,id',
            'records.*.status' => ['required', Rule::in(['present', 'absent', 'late'])],
        ]);

        $responses = [];

        foreach ($validated['records'] as $record) {
            $attendance = Attendance::updateOrCreate(
                [
                    'student_id' => $record['student_id'],
                    'date' => $validated['date'],
                ],
                [
                    'status' => $record['status'],
                ]
            );

            $responses[] = $attendance;
        }

        return response()->json([
            'message' => 'Attendance recorded successfully.',
            'data' => $responses,
        ], 201);
    }
    public function getByDate($date)
    {
        $validator = FacadesValidator::make(['date' => $date], [
            'date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid date format',
                'errors' => $validator->errors()
            ], 422);
        }

        $attendances = Attendance::with('student')
            ->where('date', $date)
            ->get();

        return response()->json($attendances);
    }
    public function countByStatusForDate($date)
    {
        $validator = FacadesValidator::make(['date' => $date], [
            'date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid date format',
                'errors' => $validator->errors(),
            ], 422);
        }

        $counts = Attendance::where('date', $date)
            ->selectRaw("
            COALESCE(SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END), 0) as present,
        COALESCE(SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END), 0) as absent,
        COALESCE(SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END), 0) as late
        ")
            ->first();

        return response()->json([
            'present' => $counts->present,
            'absent' => $counts->absent,
            'late' => $counts->late,
        ]);
    }
    public function getMonthlyAttendanceStatusByStudent($studentId)
    {
        $now = Carbon::now();

        $attendances = Attendance::select('status', DB::raw('count(*) as total'))
            ->where('student_id', $studentId)
            ->whereMonth('date', $now->month)
            ->whereYear('date', $now->year)
            ->groupBy('status')
            ->get();

        $result = [
            'present' => "0",
            'absent' => "0",
            'late' => "0",
        ];

        foreach ($attendances as $attendance) {
            $result[$attendance->status] = (string) $attendance->total;
        }

        return response()->json($result);
    }
}
