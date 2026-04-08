<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use SoftDeletes; // PDF requirement: Soft delete task

    protected $fillable = [
        'title',
        'description',
        'status',
        'user_id',
        'updated_by'
    ];

    // Audit tracking: Task kar (User reference)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
