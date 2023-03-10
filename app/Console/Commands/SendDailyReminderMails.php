<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Enum\Status;
use App\Models\Todo;
use Illuminate\Console\Command;
use App\Mail\DailyReminderMailer;
use Illuminate\Support\Facades\Mail;

class SendDailyReminderMails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:daily-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send users a daily report of tasks and deadlines.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $now = new Carbon();
        $start = $now->addDay()->startOfDay();
        $end = $now->copy()->endOfDay();

        $todos = Todo::where('status', Status::INCOMPLETE)
            ->whereBetween('deadline', [$start, $end])
            ->with('todoList')
            ->get();

        if ($todos->count() === 0) {
            return Command::SUCCESS;
        }

        $users = [];

        foreach ($todos as $todo) {
            $users[] = $todo->todoList->user;
        }

        $users_collection = collect($users)->unique();

        foreach ($users_collection as $user) {
            Mail::queue(new DailyReminderMailer($user, $start));
        }


        return Command::SUCCESS;
    }
}
