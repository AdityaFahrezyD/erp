<?php

namespace App\Filament\Resources\PayrollResource\Pages;

use App\Filament\Resources\PayrollResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Facades\Filament;

class EditPayroll extends EditRecord
{
    protected static string $resource = PayrollResource::class;

    public function mount($record): void
    {
        parent::mount($record);

        if (Filament::getCurrentPanel()?->getId() === 'finance') {
            $this->form->disabled();
        }
    }

    public function getBreadcrumbs(): array
    {
        $breadcrumbs = parent::getBreadcrumbs();

        $panelId = Filament::getCurrentPanel()?->getId();

        if ($panelId === 'finance') {
            $breadcrumbs[array_key_last($breadcrumbs)] = 'Detail Payroll';
        }

        return $breadcrumbs;
    }

    public function getTitle(): string
    {
        $panelId = Filament::getCurrentPanel()?->getId();

        if ($panelId === 'finance') {
            return 'Payroll Detail';
        }

        return parent::getTitle();
    }

    protected function getFormActions(): array
    {
        if (Filament::getCurrentPanel()?->getId() === 'finance') {
            return [
                Action::make('cancel')
                    ->label('Cancel')
                    ->url($this->previousUrl ?? static::getResource()::getUrl())
                    ->color('gray'),
            ];
        }

        return parent::getFormActions();
    }

    protected function getHeaderActions(): array
    {
        $panelId = Filament::getCurrentPanel()?->getId();

        if ($panelId === 'finance') {
            return [];
        }

        return [
            Actions\DeleteAction::make(),
        ];
    }
}
