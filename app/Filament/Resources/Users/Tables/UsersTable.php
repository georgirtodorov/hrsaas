<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label(__('Email'))
                    ->searchable()
                    ->sortable(),
                ToggleColumn::make('is_admin')
                    ->label(__('Is admin'))
                    ->sortable(),
                TextColumn::make('teams.name')
                    ->label(__('Companies'))
                    ->badge()
                    ->searchable(),
                TextColumn::make('employee.department.name')
                    ->label(__('Department'))
                    ->sortable(),
                TextColumn::make('employee.job_title')
                    ->label(__('Job Title'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('Created at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('is_admin')
                    ->label(__('Is admin'))
                    ->options([
                        '0' => __('No'),
                        '1' => __('Yes'),
                    ]),
                SelectFilter::make('team')
                    ->relationship('teams', 'name'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
