<?php

namespace App\Filament\Resources\Branches\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BranchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('Name'))
                    ->required(),
                TextInput::make('address')
                    ->label(__('Address')),
                TextInput::make('city')
                    ->label(__('City')),
                TextInput::make('phone')
                    ->label(__('Phone'))
                    ->tel(),
            ]);
    }
}
