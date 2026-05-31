<?php

namespace App\Filament\Resources\Teams\RelationManagers;

use App\Enums\TeamRole;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MembersRelationManager extends RelationManager
{
    protected static string $relationship = 'members';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->url(fn (User $record): string => UserResource::getUrl('edit', ['record' => $record])),
                TextColumn::make('employee.department.name')
                    ->label('Department')
                    ->sortable(),
                TextColumn::make('pivot.role')
                    ->label('Team Role')
                    ->badge()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->form(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Select::make('role')
                            ->options(fn (): array => collect(TeamRole::assignable())->pluck('label', 'value')->toArray())
                            ->required()
                            ->default(TeamRole::Member->value),
                    ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->form([
                        Select::make('role')
                            ->options(fn (): array => collect(TeamRole::assignable())->pluck('label', 'value')->toArray())
                            ->required(),
                    ]),
                DetachAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
