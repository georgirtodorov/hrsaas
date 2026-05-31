<?php

namespace App\Filament\Company\Resources\EmployeeResource\Pages;

use App\Filament\Company\Resources\EmployeeResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['team_id'] = Filament::getTenant()?->id;

        return $data;
    }
}
