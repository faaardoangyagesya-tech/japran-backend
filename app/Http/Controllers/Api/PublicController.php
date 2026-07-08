<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Game;
use App\Models\Announcement;

class PublicController extends Controller
{
    public function games()
    {
        return response()->json(Game::all());
    }

    public function accounts()
    {
        $query = Account::with('game')->where('stock_status', 'available');

        if (request('game_id')) {
            $query->where('game_id', request('game_id'));
        }

        return response()->json($query->orderBy('created_at', 'desc')->paginate(20));
    }

    public function accountDetail($code)
    {
        $account = Account::with('game')->where('code', $code)->firstOrFail();
        return response()->json($account);
    }

    public function bestSellers()
    {
        return response()->json(
            Account::with('game')
                ->where('stock_status', 'available')
                ->orderBy('sold_count', 'desc')
                ->take(5)
                ->get()
        );
    }

    public function mostExpensive()
    {
        return response()->json(
            Account::with('game')
                ->where('stock_status', 'available')
                ->orderBy('price', 'desc')
                ->take(5)
                ->get()
        );
    }

    public function announcements()
    {
        return response()->json(
            Announcement::with('creator:id,username')
                ->where('is_active', true)
                ->orderBy('created_at', 'desc')
                ->get()
        );
    }
}
