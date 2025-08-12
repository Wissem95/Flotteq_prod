<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateMissingTablesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create missing tables with raw SQL to avoid transaction conflicts
        
        // 1. Create partners table
        if (!Schema::hasTable('partners')) {
            DB::statement('
                CREATE TABLE partners (
                    id BIGSERIAL PRIMARY KEY,
                    name VARCHAR(255) NOT NULL,
                    type VARCHAR(50) CHECK (type IN (\'garage\', \'controle_technique\', \'assurance\')) NOT NULL,
                    description TEXT,
                    email VARCHAR(255),
                    phone VARCHAR(255),
                    website VARCHAR(255),
                    address TEXT NOT NULL,
                    city VARCHAR(255) NOT NULL,
                    postal_code VARCHAR(255) NOT NULL,
                    country VARCHAR(255) DEFAULT \'France\',
                    latitude DECIMAL(10,8) NOT NULL,
                    longitude DECIMAL(11,8) NOT NULL,
                    services JSON,
                    pricing JSON,
                    availability JSON,
                    service_zone JSON,
                    rating DECIMAL(3,2) DEFAULT 0,
                    rating_count INTEGER DEFAULT 0,
                    is_active BOOLEAN DEFAULT true,
                    is_verified BOOLEAN DEFAULT false,
                    metadata JSON,
                    created_at TIMESTAMP,
                    updated_at TIMESTAMP
                )
            ');
            DB::statement('CREATE INDEX partners_type_active_verified_idx ON partners (type, is_active, is_verified)');
            DB::statement('CREATE INDEX partners_city_postal_idx ON partners (city, postal_code)');
            DB::statement('CREATE INDEX partners_lat_lng_idx ON partners (latitude, longitude)');
        }

        // 2. Create support_tickets table
        if (!Schema::hasTable('support_tickets')) {
            DB::statement('
                CREATE TABLE support_tickets (
                    id BIGSERIAL PRIMARY KEY,
                    tenant_id BIGINT NOT NULL,
                    user_id BIGINT NOT NULL,
                    agent_id BIGINT,
                    subject VARCHAR(255) NOT NULL,
                    description TEXT NOT NULL,
                    priority VARCHAR(50) CHECK (priority IN (\'low\', \'medium\', \'high\', \'urgent\')) DEFAULT \'medium\',
                    status VARCHAR(50) CHECK (status IN (\'open\', \'in_progress\', \'waiting_user\', \'resolved\', \'closed\')) DEFAULT \'open\',
                    category VARCHAR(50) CHECK (category IN (\'technical\', \'billing\', \'feature_request\', \'bug_report\', \'other\')) DEFAULT \'other\',
                    messages JSON,
                    metadata JSON,
                    internal_notes TEXT,
                    first_response_at TIMESTAMP,
                    resolved_at TIMESTAMP,
                    closed_at TIMESTAMP,
                    created_at TIMESTAMP,
                    updated_at TIMESTAMP
                )
            ');
            DB::statement('CREATE INDEX support_tickets_tenant_status_idx ON support_tickets (tenant_id, status)');
            DB::statement('CREATE INDEX support_tickets_agent_status_idx ON support_tickets (agent_id, status)');
            DB::statement('CREATE INDEX support_tickets_priority_created_idx ON support_tickets (priority, created_at)');
        }

        // 3. Create tenant_partner_relations table
        if (!Schema::hasTable('tenant_partner_relations')) {
            DB::statement('
                CREATE TABLE tenant_partner_relations (
                    id BIGSERIAL PRIMARY KEY,
                    tenant_id BIGINT NOT NULL,
                    partner_id BIGINT NOT NULL,
                    distance DECIMAL(8,2),
                    is_preferred BOOLEAN DEFAULT false,
                    tenant_rating DECIMAL(3,2),
                    tenant_comment TEXT,
                    booking_count INTEGER DEFAULT 0,
                    last_booking_at TIMESTAMP,
                    last_interaction_at TIMESTAMP,
                    custom_pricing JSON,
                    created_at TIMESTAMP,
                    updated_at TIMESTAMP,
                    UNIQUE(tenant_id, partner_id)
                )
            ');
        }

        // 4. Create vehicle_status_notifications table
        if (!Schema::hasTable('vehicle_status_notifications')) {
            DB::statement('
                CREATE TABLE vehicle_status_notifications (
                    id BIGSERIAL PRIMARY KEY,
                    vehicle_id BIGINT NOT NULL,
                    old_status VARCHAR(255),
                    new_status VARCHAR(255) NOT NULL,
                    changed_by BIGINT,
                    reason TEXT,
                    read_at TIMESTAMP,
                    created_at TIMESTAMP,
                    updated_at TIMESTAMP
                )
            ');
            DB::statement('CREATE INDEX vehicle_status_notifications_vehicle_idx ON vehicle_status_notifications (vehicle_id)');
        }

        // 5. Create etat_des_lieux table  
        if (!Schema::hasTable('etat_des_lieux')) {
            DB::statement('
                CREATE TABLE etat_des_lieux (
                    id BIGSERIAL PRIMARY KEY,
                    vehicle_id BIGINT NOT NULL,
                    user_id BIGINT NOT NULL,
                    type VARCHAR(50) CHECK (type IN (\'entry\', \'exit\')) NOT NULL,
                    mileage INTEGER,
                    fuel_level DECIMAL(5,2),
                    exterior_condition TEXT,
                    interior_condition TEXT,
                    damages JSON,
                    photos JSON,
                    notes TEXT,
                    status VARCHAR(50) CHECK (status IN (\'draft\', \'completed\', \'approved\')) DEFAULT \'draft\',
                    approved_by BIGINT,
                    approved_at TIMESTAMP,
                    created_at TIMESTAMP,
                    updated_at TIMESTAMP
                )
            ');
        }

        // 6. Create media table
        if (!Schema::hasTable('media')) {
            DB::statement('
                CREATE TABLE media (
                    id BIGSERIAL PRIMARY KEY,
                    model_type VARCHAR(255) NOT NULL,
                    model_id BIGINT NOT NULL,
                    uuid UUID,
                    collection_name VARCHAR(255) NOT NULL,
                    name VARCHAR(255) NOT NULL,
                    file_name VARCHAR(255) NOT NULL,
                    mime_type VARCHAR(255),
                    disk VARCHAR(255) NOT NULL,
                    conversions_disk VARCHAR(255),
                    size BIGINT NOT NULL,
                    manipulations JSON,
                    custom_properties JSON,
                    generated_conversions JSON,
                    responsive_images JSON,
                    order_column INTEGER,
                    created_at TIMESTAMP,
                    updated_at TIMESTAMP
                )
            ');
            DB::statement('CREATE INDEX media_model_type_model_id_idx ON media (model_type, model_id)');
        }

        // 7. Create invoices table (factures)
        if (!Schema::hasTable('invoices')) {
            DB::statement('
                CREATE TABLE invoices (
                    id BIGSERIAL PRIMARY KEY,
                    vehicle_id BIGINT NOT NULL,
                    invoice_number VARCHAR(255) NOT NULL,
                    supplier VARCHAR(255) NOT NULL,
                    amount DECIMAL(10,2) NOT NULL,
                    invoice_date DATE NOT NULL,
                    expense_type VARCHAR(50) CHECK (expense_type IN (\'fuel\', \'repair\', \'maintenance\', \'insurance\', \'technical_inspection\', \'other\')) NOT NULL,
                    description TEXT,
                    file_path VARCHAR(255),
                    status VARCHAR(50) CHECK (status IN (\'pending\', \'validated\', \'reimbursed\')) DEFAULT \'pending\',
                    created_at TIMESTAMP,
                    updated_at TIMESTAMP
                )
            ');
            DB::statement('CREATE INDEX invoices_vehicle_id_idx ON invoices (vehicle_id)');
            DB::statement('CREATE INDEX invoices_status_idx ON invoices (status)');
        }

        $this->command->info('Missing tables created successfully!');
    }
}