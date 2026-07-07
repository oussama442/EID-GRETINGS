<?php

namespace App\Filament\Resources\Clients;

use App\Filament\Resources\Clients\Pages\CreateClient;
use App\Filament\Resources\Clients\Pages\EditClient;
use App\Filament\Resources\Clients\Pages\ListClients;
use App\Filament\Resources\Clients\Schemas\ClientForm;
use App\Filament\Resources\Clients\Tables\ClientsTable;
use App\Models\Client;
use App\Support\BranchAccess;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'full_name';

    public static function getNavigationLabel(): string
    {
        return __('Clients');
    }

    public static function getModelLabel(): string
    {
        return __('Client');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Clients');
    }

    public static function form(Schema $schema): Schema
    {
        return ClientForm::configure($schema);
    }

    public static function infolist(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make(__('Client details'))
                    ->components([
                        \Filament\Infolists\Components\TextEntry::make('full_name')
                            ->label(__('Full name')),
                        \Filament\Infolists\Components\TextEntry::make('phone')
                            ->label(__('Phone')),
                        \Filament\Infolists\Components\TextEntry::make('email')
                            ->label(__('Email address')),
                        \Filament\Infolists\Components\TextEntry::make('national_id_number')
                            ->label(__('National ID number')),
                    ])->columns(4),
                \Filament\Schemas\Components\Section::make(__('Analytics'))
                    ->components([
                        \Filament\Infolists\Components\TextEntry::make('bookings_count')
                            ->state(fn ($record) => $record->bookings()->count())
                            ->label(__('Total rentals')),
                        \Filament\Infolists\Components\TextEntry::make('total_spent')
                            ->state(fn ($record) => number_format($record->bookings()->where('status', '!=', 'cancelled')->sum('total_amount'), 2) . ' DZD')
                            ->label(__('Total spent')),
                        \Filament\Infolists\Components\TextEntry::make('outstanding_balance')
                            ->state(function ($record) {
                                $totalBilled = $record->bookings()->where('status', '!=', 'cancelled')->sum('total_amount');
                                $totalPaid = $record->bookings()->with('payments')->get()->pluck('payments')->flatten()->sum('amount');
                                return number_format($totalBilled - $totalPaid, 2) . ' DZD';
                            })
                            ->label(__('Outstanding balance'))
                            ->color(fn ($state) => (float) str_replace([' DZD', ','], '', $state) > 0 ? 'danger' : 'success'),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return ClientsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (BranchAccess::isRestricted()) {
            $query->where(function (Builder $query): void {
                $query
                    ->where('branch_id', BranchAccess::branchId())
                    ->orWhereHas('bookings', fn (Builder $query): Builder => BranchAccess::scope($query));
            });
        }

        return $query;
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\Clients\RelationManagers\BookingsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClients::route('/'),
            'create' => CreateClient::route('/create'),
            'view' => \App\Filament\Resources\Clients\Pages\ViewClient::route('/{record}'),
            'edit' => EditClient::route('/{record}/edit'),
        ];
    }
}
