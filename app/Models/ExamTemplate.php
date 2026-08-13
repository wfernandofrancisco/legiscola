<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamTemplate extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'class_id',
        'title',
        'instructions',
    ];

    public function turma(): BelongsTo
    {
        return $this->belongsTo(Turma::class, 'class_id');
    }

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'exam_template_question')
            ->withPivot(['tenant_id', 'position'])
            ->withTimestamps()
            ->orderBy('exam_template_question.position');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ExamAttachment::class);
    }
}
