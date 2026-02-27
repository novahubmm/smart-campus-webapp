<?php

namespace App\Models;

use App\Models\Traits\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use HasFactory, HasUuidPrimaryKey, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function staffProfiles()
    {
        return $this->hasMany(StaffProfile::class);
    }

    public function teacherProfiles()
    {
        return $this->hasMany(TeacherProfile::class);
    }

    public function allMembers()
    {
        $staff = $this->staffProfiles()->where('status', 'active')->with('user')->get()->map(function ($profile) {
            return [
                'id' => $profile->id,
                'type' => 'staff',
                'name' => $profile->user->name,
                'email' => $profile->user->email,
                'employee_id' => $profile->employee_id,
                'position' => $profile->position,
                'profile' => $profile
            ];
        });

        $teachers = $this->teacherProfiles()->where('status', 'active')->with('user')->get()->map(function ($profile) {
            return [
                'id' => $profile->id,
                'type' => 'teacher',
                'name' => $profile->user->name,
                'email' => $profile->user->email,
                'employee_id' => $profile->employee_id,
                'position' => $profile->position,
                'profile' => $profile
            ];
        });

        return $staff->concat($teachers);
    }

    public function getMembersCountAttribute(): int
    {
        $staffCount = $this->attributes['staff_profiles_count'] ?? null;
        $teacherCount = $this->attributes['teacher_profiles_count'] ?? null;

        if ($staffCount !== null || $teacherCount !== null) {
            return (int) ($staffCount ?? 0) + (int) ($teacherCount ?? 0);
        }

        $staffTotal = $this->relationLoaded('staffProfiles')
            ? $this->staffProfiles->where('status', 'active')->count()
            : $this->staffProfiles()->where('status', 'active')->count();
        $teacherTotal = $this->relationLoaded('teacherProfiles')
            ? $this->teacherProfiles->where('status', 'active')->count()
            : $this->teacherProfiles()->where('status', 'active')->count();

        return $staffTotal + $teacherTotal;
    }

    public function getStaffCountAttribute()
    {
        return $this->getMembersCountAttribute();
    }

    public function allMembersPaginated($perPage = 10)
    {
        // Get staff with pagination
        $staffQuery = $this->staffProfiles()->where('status', 'active')->with('user');
        $teacherQuery = $this->teacherProfiles()->where('status', 'active')->with('user');
        
        // For simplicity, we'll combine and paginate manually
        // In a real-world scenario, you might want to use a more sophisticated approach
        $allMembers = collect();
        
        $staff = $staffQuery->get()->map(function ($profile) {
            return [
                'id' => $profile->id,
                'type' => 'staff',
                'name' => $profile->user->name,
                'email' => $profile->user->email,
                'employee_id' => $profile->employee_id,
                'position' => $profile->position,
                'profile' => $profile,
                'created_at' => $profile->created_at
            ];
        });

        $teachers = $teacherQuery->get()->map(function ($profile) {
            return [
                'id' => $profile->id,
                'type' => 'teacher',
                'name' => $profile->user->name,
                'email' => $profile->user->email,
                'employee_id' => $profile->employee_id,
                'position' => $profile->position,
                'profile' => $profile,
                'created_at' => $profile->created_at
            ];
        });

        $allMembers = $staff->concat($teachers)->sortBy('name');
        
        // Manual pagination
        $currentPage = request()->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        $items = $allMembers->slice($offset, $perPage)->values();
        
        return new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $allMembers->count(),
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'pageName' => 'page',
            ]
        );
    }
}
