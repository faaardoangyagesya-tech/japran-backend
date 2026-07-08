<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('DROP TABLE IF EXISTS orders CASCADE');
        DB::statement('DROP TABLE IF EXISTS announcements CASCADE');
        DB::statement('DROP TABLE IF EXISTS accounts CASCADE');
        DB::statement('DROP TABLE IF EXISTS games CASCADE');
        DB::statement('DROP TABLE IF EXISTS admins CASCADE');

        DB::statement("
            CREATE TABLE games (
                id BIGSERIAL PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                icon VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

        DB::statement("
            CREATE TABLE accounts (
                id BIGSERIAL PRIMARY KEY,
                code VARCHAR(7) UNIQUE NOT NULL,
                game_id BIGINT NOT NULL REFERENCES games(id) ON DELETE CASCADE,
                name VARCHAR(255) NOT NULL,
                price DECIMAL(12,2) NOT NULL,
                stock_status VARCHAR(20) DEFAULT 'available',
                sold_count INTEGER DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

        DB::statement('CREATE INDEX idx_accounts_code ON accounts(code)');
        DB::statement('CREATE INDEX idx_accounts_stock_status ON accounts(stock_status)');

        DB::statement("
            CREATE TABLE admins (
                id BIGSERIAL PRIMARY KEY,
                username VARCHAR(100) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                role VARCHAR(20) DEFAULT 'admin',
                remember_token VARCHAR(100),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

        DB::statement("
            CREATE TABLE announcements (
                id BIGSERIAL PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                content TEXT NOT NULL,
                created_by BIGINT NOT NULL REFERENCES admins(id) ON DELETE CASCADE,
                is_active BOOLEAN DEFAULT true,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

        DB::statement("
            CREATE TABLE orders (
                id BIGSERIAL PRIMARY KEY,
                account_id BIGINT NOT NULL REFERENCES accounts(id) ON DELETE CASCADE,
                buyer_contact VARCHAR(100) NOT NULL,
                recorded_by BIGINT NOT NULL REFERENCES admins(id) ON DELETE CASCADE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

        DB::statement("INSERT INTO admins (username, password, role) VALUES ('superadmin', '" . bcrypt('japranadmin123') . "', 'superadmin')");
        DB::statement("INSERT INTO games (name) VALUES ('Free Fire')");
        DB::statement("INSERT INTO games (name) VALUES ('Mobile Legends')");
        DB::statement("INSERT INTO games (name) VALUES ('PUBG Mobile')");
    }

    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS orders CASCADE');
        DB::statement('DROP TABLE IF EXISTS announcements CASCADE');
        DB::statement('DROP TABLE IF EXISTS accounts CASCADE');
        DB::statement('DROP TABLE IF EXISTS games CASCADE');
        DB::statement('DROP TABLE IF EXISTS admins CASCADE');
    }
};
