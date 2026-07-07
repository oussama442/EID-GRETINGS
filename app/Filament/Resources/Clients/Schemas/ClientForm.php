<?php

namespace App\Filament\Resources\Clients\Schemas;

use App\Support\BranchAccess;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class ClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('branch_id')
                    ->label(__('Branch'))
                    ->relationship('branch', 'name', fn (Builder $query): Builder => BranchAccess::scope($query, 'id'))
                    ->searchable()
                    ->preload()
                    ->default(fn (): ?int => BranchAccess::branchId())
                    ->disabled(fn (): bool => BranchAccess::isRestricted())
                    ->dehydrated(),
                TextInput::make('full_name')
                    ->label(__('Full name'))
                    ->required(),
                TextInput::make('phone')
                    ->label(__('Phone'))
                    ->tel(),
                TextInput::make('email')
                    ->label(__('Email address'))
                    ->email(),
                TextInput::make('national_id_number')
                    ->label(__('National ID number')),
                TextInput::make('driver_license_number')
                    ->label(__('Driver license number')),
                DatePicker::make('driver_license_expiry')
                    ->label(__('Driver license expiry')),
                TextInput::make('id_document_photo')
                    ->label(__('ID document photo')),
                TextInput::make('license_photo')
                    ->label(__('License photo')),
                Textarea::make('address')
                    ->label(__('Address'))
                    ->columnSpanFull(),
                DatePicker::make('date_of_birth')
                    ->label(__('Date of birth')),
                Textarea::make('notes')
                    ->label(__('Notes'))
                    ->columnSpanFull(),
                Toggle::make('is_blacklisted')
                    ->label(__('Blacklisted'))
                    ->required(),
                Textarea::make('blacklist_reason')
                    ->label(__('Blacklist reason'))
                    ->columnSpanFull(),
            ]);
    }
}
