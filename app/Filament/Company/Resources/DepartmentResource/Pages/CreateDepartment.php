<?php

namespace App\Filament\Company\Resources\DepartmentResource\Pages;

use App\Filament\Company\Resources\DepartmentResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateDepartment extends CreateRecord
{
    protected static string $resource = DepartmentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['team_id'] = Filament::getTenant()?->id;

        return $data;
    }
}
