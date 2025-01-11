<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'admin_id',
        'user_id'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Links employee to their corresponding user id in user table
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship to Admin
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function assignedTasks()
    {
        return $this->hasMany(AssignedTask::class);
    }

}
