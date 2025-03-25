<?php

namespace App\Filament\Resources\GoingProjectResource\Pages;

use App\Filament\Resources\GoingProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGoingProjects extends ListRecords
{
    protected static string $resource = GoingProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
