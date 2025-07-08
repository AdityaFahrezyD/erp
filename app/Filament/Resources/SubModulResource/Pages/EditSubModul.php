<?php

namespace App\Filament\Resources\SubModulResource\Pages;

use App\Filament\Resources\SubModulResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\SubModulDependencies;
use Illuminate\Database\Eloquent\Model;

class EditSubModul extends EditRecord
{
    protected static string $resource = SubModulResource::class;

protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $this->saveDependencies();
    }

    protected function saveDependencies(): void
    {
        // Delete existing dependencies
        SubModulDependencies::where('sub_modul_id', $this->record->id)->delete();

        // Create new dependencies
        $dependencies = $this->data['dependencies'] ?? [];
        foreach ($dependencies as $dependencyId) {
            SubModulDependencies::create([
                'sub_modul_id' => $this->record->id,
                'depends_on_sub_modul_id' => $dependencyId,
            ]);
        }
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with('modul');
    }


}
