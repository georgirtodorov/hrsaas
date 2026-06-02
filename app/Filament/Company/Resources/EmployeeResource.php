<?php

namespace App\Filament\Company\Resources;

use App\Enums\TeamPermission;
use App\Filament\Company\Resources\EmployeeResource\Pages\CreateEmployee;
use App\Filament\Company\Resources\EmployeeResource\Pages\EditEmployee;
use App\Filament\Company\Resources\EmployeeResource\Pages\ListEmployees;
use App\Models\Employee;
use App\Models\User;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $recordTitleAttribute = 'first_name';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    public static function getModelLabel(): string
    {
        return __('Employee');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Employees');
    }

    public static function canCreate(): bool
    {
        $user = auth()->user();
        $tenant = Filament::getTenant();

        return $user && $tenant && $user->hasTeamPermission($tenant, TeamPermission::CreateEmployee);
    }

    public static function canEdit(Model $record): bool
    {
        $user = auth()->user();
        $tenant = Filament::getTenant();

        return $user && $tenant && $user->hasTeamPermission($tenant, TeamPermission::UpdateEmployee);
    }

    public static function canDelete(Model $record): bool
    {
        $user = auth()->user();
        $tenant = Filament::getTenant();

        return $user && $tenant && $user->hasTeamPermission($tenant, TeamPermission::DeleteEmployee);
    }

    public static function getEloquentQuery(): Builder
    {
        $tenant = Filament::getTenant();

        return parent::getEloquentQuery()->when(
            $tenant,
            fn (Builder $query) => $query->whereBelongsTo($tenant, 'team'),
        );
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('first_name')
                    ->label(__('First name'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('last_name')
                    ->label(__('Last name'))
                    ->required()
                    ->maxLength(255),
                Select::make('user_id')
                    ->label(__('User'))
                    ->options(fn (): array => User::query()
                        ->whereHas('teams', fn (Builder $q) => $q->whereKey(Filament::getTenant()?->id ?? 0))
                        ->pluck('name', 'id')
                        ->toArray())
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('department_id')
                    ->label(__('Department'))
                    ->relationship('department', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable(),
                TextInput::make('phone')
                    ->label(__('Phone'))
                    ->tel()
                    ->maxLength(50),
                TextInput::make('job_title')
                    ->label(__('Job title'))
                    ->maxLength(255),
                DatePicker::make('hire_date')
                    ->label(__('Hire date')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('first_name')
                    ->label(__('First name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('last_name')
                    ->label(__('Last name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.email')
                    ->label(__('Email'))
                    ->searchable(),
                TextColumn::make('department.name')
                    ->label(__('Department'))
                    ->sortable(),
                TextColumn::make('job_title')
                    ->label(__('Job title'))
                    ->searchable(),
                TextColumn::make('hire_date')
                    ->label(__('Hire date'))
                    ->date()
                    ->sortable(),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->visible(fn (Model $record): bool => static::canDelete($record)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmployees::route('/'),
            'create' => CreateEmployee::route('/create'),
            'edit' => EditEmployee::route('/{record}/edit'),
        ];
    }
}
