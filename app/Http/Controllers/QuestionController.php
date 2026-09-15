<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\QuestionBank;
use App\Models\QuestionVersion;
use App\Models\QuestionOption;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class QuestionController extends Controller
{
    public function create(QuestionBank $questionBank): View
    {
        $this->authorize('create', Question::class);
        $this->authorize('view', $questionBank);
        
        return view('questions.form', compact('questionBank'));
    }

    public function edit(Question $question): View
    {
        $this->authorize('update', $question);
        
        // Eager load the current version and its options
        $question->load('currentVersion.options');
        $questionBank = $question->questionBank;
        
        return view('questions.form', compact('question', 'questionBank'));
    }

    public function preview(Question $question): View
    {
        $this->authorize('view', $question);
        $question->load('currentVersion.options');
        return view('questions.preview', compact('question'));
    }

    public function history(Question $question): View
    {
        $this->authorize('view', $question);
        $question->load('versions.options');
        return view('questions.history', compact('question'));
    }

    public function store(Request $request, QuestionBank $questionBank)
    {
        $this->authorize('create', Question::class);
        $this->authorize('view', $questionBank);

        $validated = $request->validate([
            'type' => 'required|in:multiple_choice,complex_multiple_choice,true_false,matching,short_answer,essay',
            'cognitive_level' => 'nullable|string',
            'difficulty' => 'nullable|string',
            'topic' => 'nullable|string',
            'competency' => 'nullable|string',
            'content' => 'required|string',
            'options' => 'nullable|array',
            'options.*.content' => 'nullable|string',
            'options.*.is_correct' => 'boolean',
            'options.*.order' => 'integer',
            'options.*.weight' => 'numeric',
            'scoring_metadata' => 'nullable|array',
        ]);

        $question = DB::transaction(function () use ($validated, $questionBank, $request) {
            $question = Question::create([
                'question_bank_id' => $questionBank->id,
                'status' => 'DRAFT',
                'created_by' => auth()->id(),
            ]);

            $version = QuestionVersion::create([
                'question_id' => $question->id,
                'version' => 1,
                'type' => $validated['type'],
                'cognitive_level' => $validated['cognitive_level'] ?? null,
                'difficulty' => $validated['difficulty'] ?? null,
                'topic' => $validated['topic'] ?? null,
                'competency' => $validated['competency'] ?? null,
                'content' => $validated['content'],
                'scoring_metadata' => $validated['scoring_metadata'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $question->update(['current_version_id' => $version->id]);

            if (isset($validated['options'])) {
                foreach ($validated['options'] as $option) {
                    QuestionOption::create([
                        'question_version_id' => $version->id,
                        'content' => $option['content'] ?? null,
                        'is_correct' => $option['is_correct'] ?? false,
                        'order' => $option['order'] ?? 0,
                        'weight' => $option['weight'] ?? 0,
                    ]);
                }
            }

            if (!empty($request->media_ids)) {
                \App\Models\Media::whereIn('id', $request->media_ids)
                    ->update([
                        'mediable_id' => $version->id,
                        'mediable_type' => QuestionVersion::class,
                    ]);
            }

            return $question;
        });

        AuditLog::record('question.create', $question, null, $question->toArray(), 'success', 'Soal baru dibuat');

        return response()->json(['message' => 'Soal berhasil dibuat.', 'question' => $question]);
    }

    public function update(Request $request, Question $question)
    {
        $this->authorize('update', $question);

        $validated = $request->validate([
            'type' => 'required|in:multiple_choice,complex_multiple_choice,true_false,matching,short_answer,essay',
            'cognitive_level' => 'nullable|string',
            'difficulty' => 'nullable|string',
            'topic' => 'nullable|string',
            'competency' => 'nullable|string',
            'content' => 'required|string',
            'options' => 'nullable|array',
            'options.*.content' => 'nullable|string',
            'options.*.is_correct' => 'boolean',
            'options.*.order' => 'integer',
            'options.*.weight' => 'numeric',
            'scoring_metadata' => 'nullable|array',
        ]);

        $newVersion = DB::transaction(function () use ($validated, $question, $request) {
            $currentVersion = $question->currentVersion;
            $nextVersionNumber = $currentVersion ? $currentVersion->version + 1 : 1;

            $version = QuestionVersion::create([
                'question_id' => $question->id,
                'version' => $nextVersionNumber,
                'type' => $validated['type'],
                'cognitive_level' => $validated['cognitive_level'] ?? null,
                'difficulty' => $validated['difficulty'] ?? null,
                'topic' => $validated['topic'] ?? null,
                'competency' => $validated['competency'] ?? null,
                'content' => $validated['content'],
                'scoring_metadata' => $validated['scoring_metadata'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $question->update(['current_version_id' => $version->id]);

            if (isset($validated['options'])) {
                foreach ($validated['options'] as $option) {
                    QuestionOption::create([
                        'question_version_id' => $version->id,
                        'content' => $option['content'] ?? null,
                        'is_correct' => $option['is_correct'] ?? false,
                        'order' => $option['order'] ?? 0,
                        'weight' => $option['weight'] ?? 0,
                    ]);
                }
            }

            // Bind new uploaded media to the new version
            if (!empty($request->media_ids)) {
                \App\Models\Media::whereIn('id', $request->media_ids)
                    ->update([
                        'mediable_id' => $version->id,
                        'mediable_type' => QuestionVersion::class,
                    ]);
            }

            // Also copy existing media from previous version to the new version
            if ($currentVersion) {
                foreach ($currentVersion->media as $oldMedia) {
                    // Only copy if it wasn't deleted (for now we just duplicate the media record to bind to new version,
                    // but wait, media file is physical. Duplicating the record is safe).
                    // Or we just re-bind the old media if it's not a strict history snapshot,
                    // but history snapshot requires old version to keep its media!
                    // So we must duplicate the Media record.
                    $newMedia = $oldMedia->replicate();
                    $newMedia->mediable_id = $version->id;
                    $newMedia->created_at = now();
                    $newMedia->updated_at = now();
                    $newMedia->save();
                }
            }

            return $version;
        });

        AuditLog::record('question.update', $question, null, $newVersion->toArray(), 'success', 'Versi soal diperbarui');

        return response()->json(['message' => 'Soal berhasil diperbarui.', 'version' => $newVersion]);
    }

    public function publish(Question $question)
    {
        $this->authorize('publish', $question);

        $oldStatus = $question->status;
        $question->update(['status' => 'PUBLISHED']);

        AuditLog::record('question.publish', $question, ['status' => $oldStatus], ['status' => 'PUBLISHED'], 'success', 'Soal di-publish');

        return back()->with('success', 'Soal berhasil di-publish.');
    }

    public function draft(Question $question)
    {
        $this->authorize('draft', $question);

        $oldStatus = $question->status;
        $question->update(['status' => 'DRAFT']);

        AuditLog::record('question.draft', $question, ['status' => $oldStatus], ['status' => 'DRAFT'], 'success', 'Status soal dikembalikan ke draft');

        return back()->with('success', 'Status soal berhasil dikembalikan ke Draft.');
    }

    public function destroy(Request $request, Question $question)
    {
        $this->authorize('delete', $question);

        $question->update(['status' => 'ARCHIVED']);

        AuditLog::record('question.archive', $question, null, ['status' => 'ARCHIVED'], 'success', 'Soal diarsipkan');

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Soal berhasil diarsipkan.']);
        }

        return back()->with('success', 'Soal berhasil diarsipkan.');
    }
}
