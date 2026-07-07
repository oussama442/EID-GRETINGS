<?php

namespace App\Filament\Resources\Clients\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ClientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')
                    ->label(__('Full name'))
                    ->searchable(),
                TextColumn::make('branch.name')
                    ->label(__('Branch'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->label(__('Phone'))
                    ->searchable(),
                TextColumn::make('email')
                    ->label(__('Email address'))
                    ->searchable(),
                TextColumn::make('national_id_number')
                    ->label(__('National ID number'))
                    ->searchable(),
                TextColumn::make('driver_license_number')
                    ->label(__('Driver license number'))
                    ->searchable(),
                TextColumn::make('driver_license_expiry')
                    ->label(__('Driver license expiry'))
                    ->date()
                    ->sortable(),
                TextColumn::make('date_of_birth')
                    ->label(__('Date of birth'))
                    ->date()
                    ->sortable(),
                IconColumn::make('is_blacklisted')
                    ->label(__('Blacklisted'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label(__('Created at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('Updated at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                \Filament\Actions\ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
