<?php

namespace App\Filament\Resources\Settings\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Brand identity'))
                    ->description(__('Control the logo, name, and visual identity used across the admin panel and public pages.'))
                    ->schema([
                        TextInput::make('company_name')
                            ->label(__('Company name'))
                            ->required()
                            ->maxLength(255)
                            ->default('Antigravity Car Rental'),
                        FileUpload::make('logo')
                            ->label(__('Logo'))
                            ->image()
                            ->disk('public')
                            ->directory('settings')
                            ->visibility('public'),
                        FileUpload::make('favicon')
                            ->label(__('Favicon'))
                            ->image()
                            ->disk('public')
                            ->directory('settings')
                            ->visibility('public'),
                        ColorPicker::make('primary_color')
                            ->label(__('Primary color'))
                            ->default('#f59e0b'),
                    ])
                    ->columns(2),
                Section::make(__('Contact details'))
                    ->schema([
                        TextInput::make('phone')
                            ->label(__('Phone'))
                            ->tel()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label(__('Email address'))
                            ->email()
                            ->maxLength(255),
                        TextInput::make('website')
                            ->label(__('Website'))
                            ->url()
                            ->maxLength(255),
                        TextInput::make('whatsapp_number')
                            ->label(__('WhatsApp number'))
                            ->tel()
                            ->maxLength(255),
                        Textarea::make('address')
                            ->label(__('Address'))
                            ->columnSpanFull(),
                        TextInput::make('city')
                            ->label(__('City'))
                            ->maxLength(255),
                        TextInput::make('country')
                            ->label(__('Country'))
                            ->maxLength(255),
                    ])
                    ->columns(2),
                Section::make(__('Rental defaults'))
                    ->schema([
                        TextInput::make('currency')
                            ->label(__('Currency'))
                            ->required()
                            ->maxLength(10)
                            ->default('DZD'),
                        TextInput::make('tax_rate')
                            ->label(__('Tax rate (%)'))
                            ->required()
                            ->numeric()
                            ->default(0),
                        TextInput::make('default_deposit')
                            ->label(__('Default deposit'))
                            ->numeric()
                            ->default(0),
                        TextInput::make('late_fee_per_day')
                            ->label(__('Late fee per day'))
                            ->numeric()
                            ->default(0),
                        TextInput::make('minimum_rental_days')
                            ->label(__('Minimum rental days'))
                            ->numeric()
                            ->default(1)
                            ->minValue(1),
                    ])
                    ->columns(3),
                Section::make(__('Documents and public copy'))
                    ->schema([
                        TextInput::make('booking_prefix')
                            ->label(__('Booking prefix'))
                            ->required()
                            ->maxLength(20)
                            ->default('BKG'),
                        TextInput::make('receipt_prefix')
                            ->label(__('Receipt prefix'))
                            ->required()
                            ->maxLength(20)
                            ->default('REC'),
                        TextInput::make('contract_prefix')
                            ->label(__('Contract prefix'))
                            ->required()
                            ->maxLength(20)
                            ->default('CTR'),
                        TextInput::make('public_hero_title')
                            ->label(__('Public hero title'))
                            ->columnSpanFull()
                            ->maxLength(255),
                        Textarea::make('public_hero_subtitle')
                            ->label(__('Public hero subtitle'))
                            ->columnSpanFull(),
                        Textarea::make('receipt_footer')
                            ->label(__('Receipt footer'))
                            ->columnSpanFull(),
                        Textarea::make('contract_terms_template')
                            ->label(__('Contract terms template'))
                            ->columnSpanFull(),
                    ])
                    ->columns(3),
            ]);
    }
}
