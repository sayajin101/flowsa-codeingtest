<?php

namespace App\Models;

use App\Enum\Status;
use App\Models\Todo;
use App\Traits\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TodoList extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'title',
        'description',
    ];

    protected $casts = [
        'status' => Status::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function todos()
    {
        return $this->hasMany(Todo::class, 'list_id', 'id');
    }
}
