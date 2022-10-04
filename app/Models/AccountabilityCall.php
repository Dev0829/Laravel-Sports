<?php

namespace App\Models;

use App\Core\Traits\SpatieLogsActivity;
use Illuminate\Database\Eloquent\Model;

class AccountabilityCall extends Model
{
    use SpatieLogsActivity;

    protected $table = 'accountability_call';

    protected $fillable = [
        'name',
        'business_name',
        'comments',
        'status',
        'created_at',
        'updated_at',
    ];   
}
