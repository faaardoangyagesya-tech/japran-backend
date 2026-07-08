<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Game;
use App\Models\Admin;
use App\Models\Announcement;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // ─── Accounts ───
    public function allAccounts()
    {
        return response()->json(
            Account::with('game')->orderBy('created_at', 'desc')->paginate(50)
        );
    }

    public function storeAccount(Request $request)
    {
        $request->validate([
            'game_id' => 'required|exists:games,id',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
        ]);

        $account = Account::create([
            'code' => Account::generateCode(),
            'game_id' => $request->game_id,
            'name' => $request->name,
            'price' => $request->price,
            'stock_status' => 'available',
            'sold_count' => 0,
        ]);

        return response()->json($account->load('game'), 201);
    }

    public function updateAccount(Request $request, $id)
    {
        $account = Account::findOrFail($id);

        $request->validate([
            'game_id' => 'exists:games,id',
            'name' => 'string|max:255',
            'price' => 'numeric|min:0',
            'stock_status' => 'in:available,sold',
        ]);

        $account->update($request->only(['game_id', 'name', 'price', 'stock_status']));

        return response()->json($account->load('game'));
    }

    public function deleteAccount($id)
    {
        $account = Account::findOrFail($id);
        $account->delete();
        return response()->json(['message' => 'Deleted']);
    }

    public function generateCodes(Request $request)
    {
        $request->validate([
            'game_id' => 'required|exists:games,id',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'count' => 'required|integer|min:1|max:100',
        ]);

        $accounts = [];
        for ($i = 0; $i < $request->count; $i++) {
            $accounts[] = Account::create([
                'code' => Account::generateCode(),
                'game_id' => $request->game_id,
                'name' => $request->name,
                'price' => $request->price,
                'stock_status' => 'available',
                'sold_count' => 0,
            ]);
        }

        return response()->json(['accounts' => $accounts, 'count' => count($accounts)], 201);
    }

    // ─── Games ───
    public function allGames()
    {
        return response()->json(Game::all());
    }

    public function storeGame(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100']);
        return response()->json(Game::create($request->all()), 201);
    }

    public function updateGame(Request $request, $id)
    {
        $game = Game::findOrFail($id);
        $game->update($request->only(['name', 'icon']));
        return response()->json($game);
    }

    public function deleteGame($id)
    {
        Game::findOrFail($id)->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ─── Orders ───
    public function allOrders()
    {
        return response()->json(
            Order::with(['account', 'recorder:id,username'])
                ->orderBy('created_at', 'desc')
                ->paginate(50)
        );
    }

    public function storeOrder(Request $request)
    {
        $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'buyer_contact' => 'required|string|max:100',
        ]);

        $account = Account::findOrFail($request->account_id);
        $account->update(['stock_status' => 'sold']);
        $account->increment('sold_count');

        $order = Order::create([
            'account_id' => $request->account_id,
            'buyer_contact' => $request->buyer_contact,
            'recorded_by' => $request->user()->id,
        ]);

        return response()->json($order->load(['account', 'recorder:id,username']), 201);
    }

    // ─── Announcements ───
    public function allAnnouncements()
    {
        return response()->json(
            Announcement::with('creator:id,username')
                ->orderBy('created_at', 'desc')
                ->get()
        );
    }

    public function storeAnnouncement(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $announcement = Announcement::create([
            'title' => $request->title,
            'content' => $request->content,
            'created_by' => $request->user()->id,
            'is_active' => true,
        ]);

        return response()->json($announcement->load('creator:id,username'), 201);
    }

    public function updateAnnouncement(Request $request, $id)
    {
        $announcement = Announcement::findOrFail($id);
        $announcement->update($request->only(['title', 'content', 'is_active']));
        return response()->json($announcement->load('creator:id,username'));
    }

    public function deleteAnnouncement($id)
    {
        Announcement::findOrFail($id)->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ─── Admins ───
    public function allAdmins()
    {
        return response()->json(Admin::select('id', 'username', 'role', 'created_at')->get());
    }

    public function storeAdmin(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:100|unique:admins',
            'password' => 'required|string|min:6',
            'role' => 'required|in:superadmin,admin',
        ]);

        $admin = Admin::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return response()->json(['id' => $admin->id, 'username' => $admin->username, 'role' => $admin->role, 'created_at' => $admin->created_at], 201);
    }

    public function deleteAdmin($id)
    {
        $admin = Admin::findOrFail($id);
        if ($admin->role === 'superadmin') {
            $superadminCount = Admin::where('role', 'superadmin')->count();
            if ($superadminCount <= 1) {
                return response()->json(['message' => 'Cannot delete the last superadmin'], 400);
            }
        }
        $admin->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ─── Dashboard Stats ───
    public function stats()
    {
        return response()->json([
            'total_accounts' => Account::count(),
            'available' => Account::where('stock_status', 'available')->count(),
            'sold' => Account::where('stock_status', 'sold')->count(),
            'total_orders' => Order::count(),
            'total_games' => Game::count(),
            'total_admins' => Admin::count(),
        ]);
    }
}
