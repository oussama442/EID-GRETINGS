<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Support\BranchAccess;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('Name'))
                    ->required(),
                TextInput::make('email')
                    ->label(__('Email address'))
                    ->email()
                    ->required(),
                TextInput::make('phone')
                    ->label(__('Phone'))
                    ->tel(),
                DateTimePicker::make('email_verified_at')
                    ->label(__('Email verified at')),
                TextInput::make('password')
                    ->label(__('Password'))
                    ->password()
                    ->revealable()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->dehydrateStateUsing(fn (string $state): string => Hash::make($state)),
                Select::make('role')
                    ->label(__('Role'))
                    ->options([
                        'Super Admin' => __('Super Admin'),
                        'Manager' => __('Manager'),
                        'Agent' => __('Agent'),
                    ])
                    ->required()
                    ->default('Agent'),
                Select::make('branch_id')
                    ->label(__('Branch'))
                    ->relationship('branch', 'name', fn (Builder $query): Builder => BranchAccess::scope($query, 'id'))
                    ->searchable()
                    ->preload()
                    ->default(fn (): ?int => BranchAccess::branchId())
                    ->disabled(fn (): bool => BranchAccess::isRestricted())
                    ->dehydrated(),
                TextInput::make('photo')
                    ->label(__('Photo')),
                Toggle::make('active')
                    ->label(__('Active'))
                    ->required(),
            ]);
    }
}
