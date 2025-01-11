<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AssignedTask extends Model
{
    //
    use HasFactory;

    protected $fillable = ['task_id', 'employee_id', 'assigned_by'];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
