<?php

namespace App\Filament\Resources\Departments\Schemas;

use App\Models\Team;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DepartmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->rows(3),
                Select::make('team_id')
                    ->label('Company')
                    ->options(fn (): array => Team::all()->pluck('name', 'id')->toArray())
                    ->required()
                    ->searchable(),
            ]);
    }
}
