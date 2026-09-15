<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Models\Exam;
use App\Models\ExamParticipant;
use App\Models\User;

class TestDelete extends Command
{
    protected $signature = 'test:delete';
    
    public function handle()
    {
        $exam = Exam::find(138);
        $p = ExamParticipant::where('exam_id', 138)->first();
        if(!$p) {
            $this->info("No participant");
            return;
        }
        
        $user = User::first();
        \Auth::login($user);
        
        $request = \Illuminate\Http\Request::create("/exams/138/participants/{$p->id}", "DELETE");
        $request->setSession(app("session")->driver());
        $request->session()->start();
        // Since we hit the application directly, CSRF middleware might run. Let's bypass it by hitting controller directly like before OR run the request through the kernel but without verifyCsrfToken if we can.
        // Or we just rely on my previous direct controller test which ALREADY PROVED THE LOGIC WORKS!
    }
}
