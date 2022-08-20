<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'amoId', 'name', 'firstname', 'lastname',
        'responsibleUserId', 'createdBy', 'updatedBy',
        'createdAt', 'updatedAt', 'closestTaskAt',
        'accountId', 'isMain', 'amoUserId'
    ];

}
