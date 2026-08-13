<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseClassAnnouncementDelivery extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'course_class_announcement_id',
        'enrollment_id',
        'student_id',
        'channel',
        'destination',
        'status',
        'error_message',
    ];

    public function announcement(): BelongsTo
    {
        return $this->belongsTo(CourseClassAnnouncement::class, 'course_class_announcement_id');
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
