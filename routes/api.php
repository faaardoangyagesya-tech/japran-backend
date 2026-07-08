<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Api\PublicController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminController;

// ─── Health Check ───
Route::get('/', function () {
    return response()->json(['status' => 'ok', 'app' => 'Japran API']);
});

// ─── Setup (reset and seed database, protected by setup key) ───
Route::get('/setup', function () {
    if (request('key') !== config('app.setup_key', 'japran-setup-2024')) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }
    try {
        DB::statement('DROP SCHEMA public CASCADE');
        DB::statement('CREATE SCHEMA public');
        DB::statement('GRANT ALL ON SCHEMA public TO public');
        DB::statement('GRANT ALL ON SCHEMA public TO neondb_owner');

        DB::statement("CREATE TABLE games (id BIGSERIAL PRIMARY KEY, name VARCHAR(100) NOT NULL, icon VARCHAR(255), created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
        DB::statement("CREATE TABLE accounts (id BIGSERIAL PRIMARY KEY, code VARCHAR(7) UNIQUE NOT NULL, game_id BIGINT NOT NULL REFERENCES games(id) ON DELETE CASCADE, name VARCHAR(255) NOT NULL, price DECIMAL(12,2) NOT NULL, stock_status VARCHAR(20) DEFAULT 'available', sold_count INTEGER DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
        DB::statement('CREATE INDEX idx_accounts_code ON accounts(code)');
        DB::statement('CREATE INDEX idx_accounts_stock_status ON accounts(stock_status)');
        DB::statement("CREATE TABLE admins (id BIGSERIAL PRIMARY KEY, username VARCHAR(100) UNIQUE NOT NULL, password VARCHAR(255) NOT NULL, role VARCHAR(20) DEFAULT 'admin', remember_token VARCHAR(100), created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
        DB::statement("CREATE TABLE announcements (id BIGSERIAL PRIMARY KEY, title VARCHAR(255) NOT NULL, content TEXT NOT NULL, created_by BIGINT NOT NULL REFERENCES admins(id) ON DELETE CASCADE, is_active BOOLEAN DEFAULT true, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
        DB::statement("CREATE TABLE orders (id BIGSERIAL PRIMARY KEY, account_id BIGINT NOT NULL REFERENCES accounts(id) ON DELETE CASCADE, buyer_contact VARCHAR(100) NOT NULL, recorded_by BIGINT NOT NULL REFERENCES admins(id) ON DELETE CASCADE, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
        DB::statement("CREATE TABLE personal_access_tokens (id BIGSERIAL PRIMARY KEY, tokenable_type VARCHAR(255) NOT NULL, tokenable_id BIGINT NOT NULL, name VARCHAR(255) NOT NULL, token VARCHAR(64) UNIQUE NOT NULL, abilities TEXT, last_used_at TIMESTAMP, expires_at TIMESTAMP, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
        DB::statement("CREATE INDEX idx_personal_access_tokens_tokenable ON personal_access_tokens(tokenable_type, tokenable_id)");
        DB::statement("CREATE INDEX idx_personal_access_tokens_token ON personal_access_tokens(token)");
        DB::statement("CREATE TABLE settings (id BIGSERIAL PRIMARY KEY, key VARCHAR(100) UNIQUE NOT NULL, value TEXT NOT NULL)");

        $hash = bcrypt('japranadmin123');
        DB::statement("INSERT INTO admins (username, password, role) VALUES ('superadmin', '{$hash}', 'superadmin')");
        DB::statement("INSERT INTO games (name) VALUES ('Free Fire')");
        DB::statement("INSERT INTO games (name) VALUES ('Mobile Legends')");
        DB::statement("INSERT INTO games (name) VALUES ('PUBG Mobile')");
        DB::statement("INSERT INTO settings (key, value) VALUES ('phone', '087740637895')");
        DB::statement("INSERT INTO settings (key, value) VALUES ('admin2', '087740637895')");
        DB::statement("INSERT INTO settings (key, value) VALUES ('group_url', 'https://chat.whatsapp.com/your-group')");
        DB::statement("INSERT INTO settings (key, value) VALUES ('channel_url', 'https://whatsapp.com/channel/your-channel')");
        DB::statement("INSERT INTO settings (key, value) VALUES ('instagram', 'https://instagram.com/your-ig')");
        DB::statement("INSERT INTO settings (key, value) VALUES ('tiktok', 'https://tiktok.com/@your-tiktok')");

        return response()->json(['message' => 'Database setup complete']);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

// ─── Public Routes ───
Route::get('/games', [PublicController::class, 'games']);
Route::get('/accounts', [PublicController::class, 'accounts']);
Route::get('/accounts/best-sellers', [PublicController::class, 'bestSellers']);
Route::get('/accounts/most-expensive', [PublicController::class, 'mostExpensive']);
Route::get('/accounts/{code}', [PublicController::class, 'accountDetail']);
Route::get('/announcements/active', [PublicController::class, 'announcements']);
Route::get('/settings', [PublicController::class, 'settings']);

// ─── Auth Routes ───
Route::post('/admin/login', [AuthController::class, 'login']);

// ─── Admin Protected Routes ───
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/admin/me', [AuthController::class, 'me']);
    Route::post('/admin/logout', [AuthController::class, 'logout']);

    Route::get('/admin/stats', [AdminController::class, 'stats']);

    Route::get('/admin/accounts', [AdminController::class, 'allAccounts']);
    Route::post('/admin/accounts', [AdminController::class, 'storeAccount']);
    Route::put('/admin/accounts/{id}', [AdminController::class, 'updateAccount']);
    Route::delete('/admin/accounts/{id}', [AdminController::class, 'deleteAccount']);
    Route::post('/admin/accounts/generate-codes', [AdminController::class, 'generateCodes']);

    Route::get('/admin/games', [AdminController::class, 'allGames']);
    Route::post('/admin/games', [AdminController::class, 'storeGame']);
    Route::put('/admin/games/{id}', [AdminController::class, 'updateGame']);
    Route::delete('/admin/games/{id}', [AdminController::class, 'deleteGame']);

    Route::get('/admin/orders', [AdminController::class, 'allOrders']);
    Route::post('/admin/orders', [AdminController::class, 'storeOrder']);

    Route::get('/admin/announcements', [AdminController::class, 'allAnnouncements']);
    Route::post('/admin/announcements', [AdminController::class, 'storeAnnouncement']);
    Route::put('/admin/announcements/{id}', [AdminController::class, 'updateAnnouncement']);
    Route::delete('/admin/announcements/{id}', [AdminController::class, 'deleteAnnouncement']);

    Route::get('/admin/admins', [AdminController::class, 'allAdmins']);
    Route::post('/admin/admins', [AdminController::class, 'storeAdmin']);
    Route::delete('/admin/admins/{id}', [AdminController::class, 'deleteAdmin']);

    Route::get('/admin/settings', [AdminController::class, 'getSettings']);
    Route::put('/admin/settings', [AdminController::class, 'updateSettings']);
});
