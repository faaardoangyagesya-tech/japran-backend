<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = ['code', 'game_id', 'name', 'price', 'stock_status', 'sold_count'];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public static function generateCode(): string
    {
        $lastCode = self::max('code');
        if (!$lastCode) {
            return '#000001';
        }
        $number = intval(ltrim($lastCode, '#')) + 1;
        return sprintf('#%06d', $number);
    }
}
