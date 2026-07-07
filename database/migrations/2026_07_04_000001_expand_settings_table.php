<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table): void {
            if (! Schema::hasColumn('settings', 'favicon')) {
                $table->string('favicon')->nullable()->after('logo');
            }

            if (! Schema::hasColumn('settings', 'primary_color')) {
                $table->string('primary_color')->default('#f59e0b')->after('favicon');
            }

            if (! Schema::hasColumn('settings', 'phone')) {
                $table->string('phone')->nullable()->after('tax_rate');
            }

            if (! Schema::hasColumn('settings', 'email')) {
                $table->string('email')->nullable()->after('phone');
            }

            if (! Schema::hasColumn('settings', 'website')) {
                $table->string('website')->nullable()->after('email');
            }

            if (! Schema::hasColumn('settings', 'whatsapp_number')) {
                $table->string('whatsapp_number')->nullable()->after('website');
            }

            if (! Schema::hasColumn('settings', 'address')) {
                $table->text('address')->nullable()->after('whatsapp_number');
            }

            if (! Schema::hasColumn('settings', 'city')) {
                $table->string('city')->nullable()->after('address');
            }

            if (! Schema::hasColumn('settings', 'country')) {
                $table->string('country')->nullable()->after('city');
            }

            if (! Schema::hasColumn('settings', 'default_deposit')) {
                $table->decimal('default_deposit', 12, 2)->default(0)->after('country');
            }

            if (! Schema::hasColumn('settings', 'late_fee_per_day')) {
                $table->decimal('late_fee_per_day', 12, 2)->default(0)->after('default_deposit');
            }

            if (! Schema::hasColumn('settings', 'minimum_rental_days')) {
                $table->unsignedSmallInteger('minimum_rental_days')->default(1)->after('late_fee_per_day');
            }

            if (! Schema::hasColumn('settings', 'booking_prefix')) {
                $table->string('booking_prefix')->default('BKG')->after('minimum_rental_days');
            }

            if (! Schema::hasColumn('settings', 'receipt_prefix')) {
                $table->string('receipt_prefix')->default('REC')->after('booking_prefix');
            }

            if (! Schema::hasColumn('settings', 'contract_prefix')) {
                $table->string('contract_prefix')->default('CTR')->after('receipt_prefix');
            }

            if (! Schema::hasColumn('settings', 'receipt_footer')) {
                $table->text('receipt_footer')->nullable()->after('contract_prefix');
            }

            if (! Schema::hasColumn('settings', 'public_hero_title')) {
                $table->string('public_hero_title')->nullable()->after('receipt_footer');
            }

            if (! Schema::hasColumn('settings', 'public_hero_subtitle')) {
                $table->text('public_hero_subtitle')->nullable()->after('public_hero_title');
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table): void {
            foreach ([
                'favicon',
                'primary_color',
                'phone',
                'email',
                'website',
                'whatsapp_number',
                'address',
                'city',
                'country',
                'default_deposit',
                'late_fee_per_day',
                'minimum_rental_days',
                'booking_prefix',
                'receipt_prefix',
                'contract_prefix',
                'receipt_footer',
                'public_hero_title',
                'public_hero_subtitle',
            ] as $column) {
                if (Schema::hasColumn('settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
