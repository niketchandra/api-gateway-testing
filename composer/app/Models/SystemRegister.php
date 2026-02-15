<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemRegister extends Model
{
    use HasFactory;

    protected $table = 'system_register';

    protected $fillable = [
        'pat_token_id',
        'user_id',
        'org_id',
        'system_name',
        'os_type',
        'ip_address',
        'tags',
        'metadata',
    ];
}
