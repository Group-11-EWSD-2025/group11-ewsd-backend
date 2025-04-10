<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    use HasFactory;
    protected $fillable = [
        'start_date',
        'end_date',
        'idea_submission_deadline',
        'final_closure_date',
    ];
    protected $guarded = [];
}
