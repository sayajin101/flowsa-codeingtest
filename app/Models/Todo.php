<?php

namespace App\Models;

use Carbon\Carbon;
use App\Enum\Status;
use App\Enum\Priority;
use App\Models\TodoList;
use App\Traits\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Todo extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'title',
        'description',
        'priority',
        'status',
        'deadline'
    ];

    protected $casts = [
        'status' => Status::class,
        'priority' => Priority::class,
        'deadline' => 'datetime'
    ];

    protected $appends = [
        'overdue',
    ];

    public function todoList()
    {
        return $this->belongsTo(TodoList::class, 'list_id', 'id');
    }

    public function scopeStatus($query, string $argument)
    {
        return $query->where('status', $argument);
    }

    public function scopePriority($query, int $argument)
    {
        return $query->where('priority', $argument);
    }

    public function getOverdueAttribute()
    {
        if (!$this->deadline) {
            return false;
        }

        return $this->status === Status::INCOMPLETE && $this->deadline < new Carbon();
    }
}
