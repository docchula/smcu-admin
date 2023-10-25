<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Personnel extends Model
{
    protected $fillable = ['name', 'name_en', 'position', 'position_en', 'year', 'sequence', 'supervisor', 'email', 'department_id'];
    protected $hidden = ['created_at', 'updated_at'];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class)->select('id', 'name');
    }
}
