<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Idea extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];

    public function files()
    {
        return $this->hasMany(IdeaFile::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)
            ->whereHas('user', function ($query) {
                $query->where('is_disable', 0);
            });
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }
    public function unlikes()
    {
        return $this->hasMany(UnLike::class);
    }

    public function report()
    {
        return $this->hasMany(IdeaReport::class);
    }
}
