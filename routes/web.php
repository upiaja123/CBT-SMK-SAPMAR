<?php
Route::get('/buat-storage', function () {
    Artisan::call('storage:link');
    return "Folder gambar berhasil dihubungkan!";
});

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Master Data Routes
    Route::prefix('master')->name('master.')->group(function () {
        Route::resource('academic-years', \App\Http\Controllers\Master\AcademicYearController::class)->except(['create', 'edit', 'show']);
        Route::resource('majors', \App\Http\Controllers\Master\MajorController::class)->except(['create', 'edit', 'show']);
        Route::resource('classes', \App\Http\Controllers\Master\SchoolClassController::class)->except(['create', 'edit', 'show']);
        Route::resource('subjects', \App\Http\Controllers\Master\SubjectController::class)->except(['create', 'edit', 'show']);
    });

    // Users (Teachers & Students) Routes
    Route::prefix('users')->name('users.')->group(function () {
        Route::resource('staff', \App\Http\Controllers\User\StaffController::class)->except(['create', 'edit', 'show']);
        Route::resource('teachers', \App\Http\Controllers\User\TeacherController::class)->except(['create', 'edit', 'show']);

        Route::get('students/import', [\App\Http\Controllers\User\StudentImportController::class, 'show'])->name('students.import.show');
        Route::post('students/import', [\App\Http\Controllers\User\StudentImportController::class, 'store'])->name('students.import.store');
        Route::resource('students', \App\Http\Controllers\User\StudentController::class)->except(['create', 'edit', 'show']);
    });

    // Audit Logs
    Route::get('/audit-logs', [\App\Http\Controllers\AuditLogController::class, 'index'])->name('audit-logs.index');

    // Student search API (lightweight, for live-search dropdowns)
    Route::get('/api/students/search', function (\Illuminate\Http\Request $request) {
        $q = trim($request->input('q', ''));
        if (strlen($q) < 2)
            return response()->json([]);
        $students = \App\Models\Student::with(['user', 'schoolClass'])
            ->whereHas('user', fn($query) => $query->where('name', 'like', "%{$q}%"))
            ->limit(15)->get();
        return response()->json($students->map(fn($s) => [
            'id' => $s->id,
            'name' => $s->user->name,
            'class_name' => $s->schoolClass->name ?? '-',
        ]));
    })->name('api.students.search');

    // Phase 1: Question Bank & Exams
    Route::resource('question_banks', \App\Http\Controllers\QuestionBankController::class);

    // Questions are nested inside question_banks for creation, but can be updated/deleted independently
    Route::get('question_banks/{question_bank}/questions/create', [\App\Http\Controllers\QuestionController::class, 'create'])->name('questions.create');
    Route::post('question_banks/{question_bank}/questions', [\App\Http\Controllers\QuestionController::class, 'store'])->name('questions.store');
    Route::get('questions/{question}/edit', [\App\Http\Controllers\QuestionController::class, 'edit'])->name('questions.edit');
    Route::put('questions/{question}', [\App\Http\Controllers\QuestionController::class, 'update'])->name('questions.update');
    Route::delete('questions/{question}', [\App\Http\Controllers\QuestionController::class, 'destroy'])->name('questions.destroy');
    Route::post('questions/{question}/publish', [\App\Http\Controllers\QuestionController::class, 'publish'])->name('questions.publish');
    Route::post('questions/{question}/draft', [\App\Http\Controllers\QuestionController::class, 'draft'])->name('questions.draft');
    Route::get('questions/{question}/preview', [\App\Http\Controllers\QuestionController::class, 'preview'])->name('questions.preview');
    Route::get('questions/{question}/history', [\App\Http\Controllers\QuestionController::class, 'history'])->name('questions.history');

    // Exams
    Route::resource('exams', \App\Http\Controllers\ExamBuilderController::class);
    Route::put('exams/{exam}/participants', [\App\Http\Controllers\ExamBuilderController::class, 'updateParticipants'])->name('exams.updateParticipants');
    Route::put('exams/{exam}/questions', [\App\Http\Controllers\ExamBuilderController::class, 'updateQuestions'])->name('exams.updateQuestions');
    Route::post('exams/{exam}/publish', [\App\Http\Controllers\ExamBuilderController::class, 'publish'])->name('exams.publish');
    Route::post('exams/{exam}/pause', [\App\Http\Controllers\ExamBuilderController::class, 'pause'])->name('exams.pause');
    Route::post('exams/{exam}/unpause', [\App\Http\Controllers\ExamBuilderController::class, 'unpause'])->name('exams.unpause');
    Route::post('exams/{exam}/susulan', [\App\Http\Controllers\ExamBuilderController::class, 'updateSusulan'])->name('exams.susulan');
    Route::post('exams/{exam}/ekstra', [\App\Http\Controllers\ExamBuilderController::class, 'addEkstra'])->name('exams.add_ekstra');
    Route::delete('exams/{exam}/participants/{participant}', [\App\Http\Controllers\ExamBuilderController::class, 'removeParticipant'])->name('exams.remove_participant');

    // Phase 2.4C: Manual Essay Grading
    Route::get('exams/{exam}/grading', [\App\Http\Controllers\ManualGradingController::class, 'index'])->name('exams.grading.index');
    Route::get('exams/{exam}/grading/{attempt}', [\App\Http\Controllers\ManualGradingController::class, 'show'])->name('exams.grading.show');
    Route::put('exams/{exam}/grading/{attempt}/answers/{participantAnswer}', [\App\Http\Controllers\ManualGradingController::class, 'update'])->name('exams.grading.update');

    // Media (Securely served)
    Route::post('media', [\App\Http\Controllers\MediaController::class, 'store'])->name('media.store');
    Route::get('media/{media}', [\App\Http\Controllers\MediaController::class, 'show'])->name('media.show');
    Route::delete('media/{media}', [\App\Http\Controllers\MediaController::class, 'destroy'])->name('media.destroy');

    // Phase 2.1 & 2.2: Exam Attempt & Session
    Route::post('exams/{exam}/attempts', [\App\Http\Controllers\ExamAttemptController::class, 'store'])->name('exams.attempts.store');
    Route::get('exams/{exam}/attempts/{attempt}', [\App\Http\Controllers\ExamAttemptController::class, 'show'])->name('exams.attempts.show');
    Route::get('exams/{exam}/attempts/{attempt}/session', [\App\Http\Controllers\ExamAttemptController::class, 'session'])->name('exams.attempts.session');

    // Phase 2.3: Answer Persistence
    Route::post('exams/{exam}/attempts/{attempt}/answers', [\App\Http\Controllers\ExamAttemptController::class, 'saveAnswer'])->name('exams.attempts.answers.store');
    Route::post('exams/{exam}/attempts/{attempt}/submit', [\App\Http\Controllers\ExamAttemptController::class, 'submit'])->name('exams.attempts.submit');

    // Phase 2.4D: Result & Finalization
    Route::get('reports', [\App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
    Route::get('exams/{exam}/attempts/{attempt}/result', [\App\Http\Controllers\ExamAttemptController::class, 'result'])->name('exams.attempts.result');
    Route::get('exams/{exam}/results', [\App\Http\Controllers\ExamResultController::class, 'index'])->name('exams.results.index');
    Route::post('exams/{exam}/results/publish', [\App\Http\Controllers\ExamResultController::class, 'publishResults'])->name('exams.results.publish');

    // Phase 4.1: Analytics
    Route::get('analytics', [\App\Http\Controllers\AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('analytics/exams/{exam}', [\App\Http\Controllers\AnalyticsController::class, 'exam'])->name('analytics.exam');

    // Phase 4.2: Export Reporting
    Route::get('exports/exams/{exam}/excel', [\App\Http\Controllers\ExportController::class, 'exportExamExcel'])->name('exports.exams.excel');
    Route::get('exports/exams/{exam}/pdf', [\App\Http\Controllers\ExportController::class, 'exportExamPdf'])->name('exports.exams.pdf');
    Route::get('exports/exams/{exam}/classes/{schoolClass}/excel', [\App\Http\Controllers\ExportController::class, 'exportClassExcel'])->name('exports.classes.excel');
    Route::get('exports/exams/{exam}/classes/{schoolClass}/pdf', [\App\Http\Controllers\ExportController::class, 'exportClassPdf'])->name('exports.classes.pdf');

    // Phase 5: Printables (Cards, Attendance, Minutes)
    Route::get('prints/classes/{class}/cards', [\App\Http\Controllers\PrintController::class, 'printCards'])->name('prints.cards');
    Route::get('prints/exams/{exam}/attendance', [\App\Http\Controllers\PrintController::class, 'printAttendance'])->name('prints.attendance');
    Route::get('prints/exams/{exam}/berita-acara', [\App\Http\Controllers\PrintController::class, 'printBeritaAcara'])->name('prints.berita-acara');

    // Phase 3.1: Presence & Integrity Foundation
    Route::post('exams/{exam}/attempts/{attempt}/heartbeat', [\App\Http\Controllers\ExamAttemptController::class, 'heartbeat'])->name('exams.attempts.heartbeat');
    Route::post('exams/{exam}/attempts/{attempt}/integrity-events', [\App\Http\Controllers\ExamAttemptController::class, 'storeIntegrityEvent'])->name('exams.attempts.integrity-events.store');
    Route::get('exams/{exam}/monitoring', [\App\Http\Controllers\ExamMonitoringController::class, 'index'])->name('exams.monitoring.index');

    // Phase 3.3A: Proctor Assignment
    Route::post('exams/{exam}/proctors', [\App\Http\Controllers\ExamProctorController::class, 'store'])->name('exams.proctors.store');
    Route::delete('exams/{exam}/proctors/{proctorId}', [\App\Http\Controllers\ExamProctorController::class, 'destroy'])->name('exams.proctors.destroy');

    // Phase 3.3B: Proctor Control Actions
    Route::post('exams/{exam}/attempts/{attempt}/extra-time', [\App\Http\Controllers\ExamProctorControlController::class, 'addExtraTime'])->name('exams.attempts.extra-time');
    Route::post('exams/{exam}/attempts/{attempt}/lock', [\App\Http\Controllers\ExamProctorControlController::class, 'lock'])->name('exams.attempts.lock');
    Route::post('exams/{exam}/attempts/{attempt}/unlock', [\App\Http\Controllers\ExamProctorControlController::class, 'unlock'])->name('exams.attempts.unlock');
    Route::post('exams/{exam}/attempts/{attempt}/force-submit', [\App\Http\Controllers\ExamProctorControlController::class, 'forceSubmit'])->name('exams.attempts.force-submit');
    // Phase 3.4: Integrity Incident & Review
    Route::get('/exams/{exam}/attempts/{attempt}/review', [\App\Http\Controllers\IntegrityReviewController::class, 'show'])->name('exams.attempts.review');
    Route::get('/exams/{exam}/attempts/{attempt}/integrity-events', [\App\Http\Controllers\IntegrityReviewController::class, 'events'])->name('exams.attempts.review.events');
    Route::post('/exams/{exam}/attempts/{attempt}/review/state', [\App\Http\Controllers\IntegrityReviewController::class, 'updateState'])->name('exams.attempts.review.state');
    Route::post('/exams/{exam}/attempts/{attempt}/review/notes', [\App\Http\Controllers\IntegrityReviewController::class, 'storeNote'])->name('exams.attempts.review.notes');

    // System Administration
    Route::prefix('system')->name('system.')->group(function () {
        Route::get('/', [\App\Http\Controllers\SystemAdminController::class, 'index'])->name('index');
        Route::post('/maintenance', [\App\Http\Controllers\SystemAdminController::class, 'toggleMaintenance'])->name('maintenance.toggle');
        Route::post('/backup', [\App\Http\Controllers\SystemAdminController::class, 'runBackup'])->name('backup.run');
        Route::get('/backup/download', [\App\Http\Controllers\SystemAdminController::class, 'downloadBackup'])->name('backup.download');
    });
});

require __DIR__ . '/auth.php';
