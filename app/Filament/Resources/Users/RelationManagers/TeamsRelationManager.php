<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Enums\TeamRole;
use App\Filament\Resources\Teams\TeamResource;
use App\Models\Team;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TeamsRelationManager extends RelationManager
{
    protected static string $relationship = 'teams';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('role')
                    ->label(__('Role'))
                    ->options(fn (): array => collect(TeamRole::assignable())->pluck('label', 'value')->toArray())
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->url(fn (Team $record): string => TeamResource::getUrl('edit', ['record' => $record])),
                TextColumn::make('pivot.role')
                    ->label(__('Team Role'))
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
                            ->label(__('Role'))
                            ->options(fn (): array => collect(TeamRole::assignable())->pluck('label', 'value')->toArray())
                            ->required()
                            ->default(TeamRole::Member->value),
                    ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->form([
                        Select::make('role')
                            ->label(__('Role'))
                            ->options(fn (): array => collect(TeamRole::assignable())->pluck('label', 'value')->toArray())
                            ->required(),
                    ]),
                DetachAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
