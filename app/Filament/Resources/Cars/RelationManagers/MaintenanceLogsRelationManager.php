<?php

namespace App\Filament\Resources\Cars\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class MaintenanceLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'maintenanceLogs';

    public static function getTitle($ownerRecord, string $pageClass): string
    {
        return __('Maintenance logs');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\DatePicker::make('date')
                    ->label(__('Date'))
                    ->required()
                    ->default(now()),
                \Filament\Forms\Components\Select::make('type')
                    ->label(__('Maintenance type'))
                    ->options([
                        'oil_change' => __('Oil change'),
                        'tire_replacement' => __('Tire replacement'),
                        'repair' => __('Repair'),
                        'inspection' => __('Inspection'),
                        'cleaning' => __('Cleaning'),
                        'other' => __('Other'),
                    ])
                    ->required(),
                \Filament\Forms\Components\TextInput::make('cost')
                    ->label(__('Cost'))
                    ->numeric()
                    ->prefix('DZD')
                    ->required(),
                \Filament\Forms\Components\TextInput::make('odometer')
                    ->label(__('Odometer (mileage)'))
                    ->numeric()
                    ->suffix('km'),
                \Filament\Forms\Components\TextInput::make('performed_by')
                    ->label(__('Performed by'))
                    ->maxLength(255),
                \Filament\Forms\Components\Textarea::make('description')
                    ->label(__('Description'))
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('type')
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('date')
                    ->label(__('Date'))
                    ->date()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('type')
                    ->label(__('Maintenance type'))
                    ->badge()
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('cost')
                    ->label(__('Cost'))
                    ->money('DZD')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('performed_by')
                    ->label(__('Performed by'))
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('odometer')
                    ->label(__('Odometer'))
                    ->numeric()
                    ->suffix(' km')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('description')
                    ->label(__('Description'))
                    ->limit(50)
                    ->searchable(),
            ])
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
