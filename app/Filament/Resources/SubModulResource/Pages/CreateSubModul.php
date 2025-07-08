<?php

namespace App\Filament\Resources\SubModulResource\Pages;

use App\Filament\Resources\SubModulResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use App\Models\SubModulDependencies;

class CreateSubModul extends CreateRecord
{
    protected static string $resource = SubModulResource::class;

    protected function afterCreate(): void
    {
        $this->saveDependencies();
    }

    protected function saveDependencies(): void
    {
        $dependencies = $this->data['dependencies'] ?? [];
        
        if (empty($dependencies)) {
            return;
        }

        // Create new dependencies
        foreach ($dependencies as $dependencyId) {
            SubModulDependencies::create([
                'sub_modul_id' => $this->record->id,
                'depends_on_sub_modul_id' => $dependencyId,
            ]);
        }
    }



}
