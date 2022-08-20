<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LossReason extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'amoId', 'name', 'sort', 'createdAt', 'updatedAt', 'amoUserId'
    ];
}
