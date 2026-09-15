<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\User;
use App\Models\ProctorExamAssignment;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ExamProctorController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request, Exam $exam)
    {
        $this->authorize('assignProctor', $exam);

        $request->validate([
            'proctor_id' => 'required|exists:users,id'
        ]);

        $proctor = User::findOrFail($request->proctor_id);

        if (!$proctor->hasRole('proktor')) {
            return response()->json(['message' => 'User is not a proctor.'], 400);
        }

        $assignment = ProctorExamAssignment::updateOrCreate(
            ['proctor_id' => $proctor->id, 'exam_id' => $exam->id],
            ['active' => true]
        );

        if ($assignment->wasRecentlyCreated || $assignment->wasChanged('active')) {
            AuditLog::record(
                'exam_proctor_assigned',
                $exam,
                null,
                ['proctor_id' => $proctor->id, 'proctor_name' => $proctor->name],
                'success',
                "Assigned proctor {$proctor->name} to exam {$exam->title}"
            );
        }

        return response()->json(['message' => 'Proctor assigned successfully.']);
    }

    public function destroy(Request $request, Exam $exam, $proctorId)
    {
        $this->authorize('assignProctor', $exam);

        $assignment = ProctorExamAssignment::where('exam_id', $exam->id)
            ->where('proctor_id', $proctorId)
            ->firstOrFail();

        $assignment->delete();

        $proctor = User::find($proctorId);

        AuditLog::record(
            'exam_proctor_removed',
            $exam,
            ['proctor_id' => $proctorId, 'proctor_name' => $proctor?->name],
            null,
            'success',
            "Removed proctor " . ($proctor ? $proctor->name : "ID {$proctorId}") . " from exam {$exam->title}"
        );

        return response()->json(['message' => 'Proctor removed successfully.']);
    }
}
