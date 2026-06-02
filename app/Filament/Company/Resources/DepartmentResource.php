<?php

namespace App\Filament\Company\Resources;

use App\Enums\TeamPermission;
use App\Filament\Company\Resources\DepartmentResource\Pages\CreateDepartment;
use App\Filament\Company\Resources\DepartmentResource\Pages\EditDepartment;
use App\Filament\Company\Resources\DepartmentResource\Pages\ListDepartments;
use App\Models\Department;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DepartmentResource extends Resource
{
    protected static ?string $model = Department::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    public static function getModelLabel(): string
    {
        return __('Department');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Departments');
    }

    public static function canCreate(): bool
    {
        $user = auth()->user();
        $tenant = Filament::getTenant();

        return $user && $tenant && $user->hasTeamPermission($tenant, TeamPermission::CreateDepartment);
    }

    public static function canEdit(Model $record): bool
    {
        $user = auth()->user();
        $tenant = Filament::getTenant();

        return $user && $tenant && $user->hasTeamPermission($tenant, TeamPermission::UpdateDepartment);
    }

    public static function canDelete(Model $record): bool
    {
        $user = auth()->user();
        $tenant = Filament::getTenant();

        return $user && $tenant && $user->hasTeamPermission($tenant, TeamPermission::DeleteDepartment);
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
            ->components([
                TextInput::make('name')
                    ->label(__('Name'))
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->label(__('Description'))
                    ->rows(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('description')
                    ->label(__('Description'))
                    ->limit(50),
                TextColumn::make('employees_count')
                    ->label(__('Employees'))
                    ->counts('employees')
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
            'index' => ListDepartments::route('/'),
            'create' => CreateDepartment::route('/create'),
            'edit' => EditDepartment::route('/{record}/edit'),
        ];
    }
}
