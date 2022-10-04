<?php

namespace App\Models;

use App\Core\Traits\SpatieLogsActivity;
use Illuminate\Database\Eloquent\Model;

class Billing extends Model
{
    use SpatieLogsActivity;

    protected $table = 'billing';

    protected $fillable = [
        'user_id',
        'cardName',
        'choice',
        'customer_id',
        'cardNumber',
        'expirationDate',
        'cvv',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
