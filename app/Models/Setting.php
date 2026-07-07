<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'logo',
        'favicon',
        'primary_color',
        'currency',
        'tax_rate',
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
        'contract_terms_template',
    ];

    protected $casts = [
        'tax_rate' => 'decimal:2',
        'default_deposit' => 'decimal:2',
        'late_fee_per_day' => 'decimal:2',
        'minimum_rental_days' => 'integer',
    ];

    public static function defaults(): array
    {
        return [
            'company_name' => 'Antigravity Car Rental',
            'currency' => 'DZD',
            'tax_rate' => 19,
            'primary_color' => '#f59e0b',
            'default_deposit' => 0,
            'late_fee_per_day' => 0,
            'minimum_rental_days' => 1,
            'booking_prefix' => 'BKG',
            'receipt_prefix' => 'REC',
            'contract_prefix' => 'CTR',
        ];
    }

    public static function current(): self
    {
        return self::query()->firstOrCreate([], self::defaults());
    }
}
