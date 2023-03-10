<?php

namespace App\Mail;

use Carbon\Carbon;
use App\Enum\Status;
use App\Models\Todo;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class DailyReminderMailer extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(protected User $user, protected Carbon $start)
    {
        //
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $user_id = $this->user->id;
        $end = $this->start->copy()->endOfDay();
        $todos = Todo::where('status', Status::INCOMPLETE)
            ->whereBetween('deadline', [$this->start, $end])
            ->whereHas('todoList', function($q) use ($user_id) {
                $q->where('user_id', $user_id);
            })
            ->get();

        return $this->markdown('emails.daily-reminder', ['user' => $this->user, 'todos' => $todos])
                    ->subject('Your upcoming deadlines!')
                    ->to($this->user->email);
    }
}
