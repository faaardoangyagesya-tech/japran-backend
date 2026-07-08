<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $fillable = ['name', 'icon'];

    public function accounts()
    {
        return $this->hasMany(Account::class);
    }
}
