<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = ['account_id', 'buyer_contact', 'recorded_by'];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function recorder()
    {
        return $this->belongsTo(Admin::class, 'recorded_by');
    }
}
