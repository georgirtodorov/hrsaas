<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Models\Department;
use App\Models\Team;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EmployeeRelationManager extends RelationManager
{
    protected static string $relationship = 'employee';

    protected static ?string $recordTitleAttribute = 'full_name';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('team_id')
                    ->label(__('Company'))
                    ->options(fn (): array => Team::all()->pluck('name', 'id')->toArray())
                    ->required()
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(fn (callable $set) => $set('department_id', null)),
                Select::make('department_id')
                    ->label(__('Department'))
                    ->options(fn (callable $get): array => Department::where('team_id', $get('team_id'))->pluck('name', 'id')->toArray())
                    ->searchable()
                    ->nullable(),
                TextInput::make('first_name')
                    ->label(__('First name'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('last_name')
                    ->label(__('Last name'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label(__('Email'))
                    ->required()
                    ->email()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('phone')
                    ->label(__('Phone'))
                    ->tel()
                    ->maxLength(255)
                    ->nullable(),
                TextInput::make('job_title')
                    ->label(__('Job title'))
                    ->maxLength(255)
                    ->nullable(),
                DatePicker::make('hire_date')
                    ->label(__('Hire date'))
                    ->nullable(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('full_name')
            ->columns([
                TextColumn::make('full_name')
                    ->label(__('Full name'))
                    ->searchable(),
                TextColumn::make('email')
                    ->label(__('Email'))
                    ->searchable(),
                TextColumn::make('team.name')
                    ->label(__('Company'))
                    ->sortable(),
                TextColumn::make('department.name')
                    ->label(__('Department'))
                    ->sortable(),
                TextColumn::make('job_title')
                    ->label(__('Job title')),
                TextColumn::make('hire_date')
                    ->label(__('Hire date'))
                    ->date(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
