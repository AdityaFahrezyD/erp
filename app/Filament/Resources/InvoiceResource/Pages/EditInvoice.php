<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Facades\Filament;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;

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
            $breadcrumbs[array_key_last($breadcrumbs)] = 'Detail';
        }

        return $breadcrumbs;
    }

    public function getTitle(): string
    {
        $panelId = Filament::getCurrentPanel()?->getId();
        
        if ($panelId === 'finance') {
            return 'Invoice Detail';
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
            return [
                Action::make('Print')
                    ->disabled(fn () => $this->record->approve_status !== 'approved')
                    ->action(function () {
                        $pdfContent = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf', [
                            'invoice' => $this->record,
                        ])->output();

                        return response()->streamDownload(function () use ($pdfContent) {
                            echo $pdfContent;
                        }, 'invoice-' . $this->record->invoice_id . '.pdf');
                    })
                    ->color('success')
                    ->icon('heroicon-m-printer'),
            ];
        }

        return [
            Action::make('Print')
                ->disabled(fn () => $this->record->approve_status !== 'approved')
                ->action(function () {
                    $pdfContent = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf', [
                        'invoice' => $this->record,
                    ])->output();

                    return response()->streamDownload(function () use ($pdfContent) {
                        echo $pdfContent;
                    }, 'invoice-' . $this->record->invoice_id . '.pdf');
                })
                ->color('success')
                ->icon('heroicon-m-printer'),
            
            Action::make('Approve')
                ->hidden(fn () => $this->record->approve_status !== 'pending') // hanya tampil saat pending
                ->action(function () {
                    $this->record->update(['approve_status' => 'approved']);
                    Notification::make()
                        ->success()
                        ->title('Invoice Approved')
                        ->body('Invoice has been approved successfully!')
                        ->send();
                    $this->js("window.location.reload()");
                })
                ->color('success')
                ->icon('heroicon-m-check'),

            Action::make('Decline')
                ->hidden(fn () => $this->record->approve_status !== 'pending')
                ->action(function () {
                    $this->record->update(['approve_status' => 'declined']);
                    Notification::make()
                        ->success()
                        ->title('Invoice Declined')
                        ->body('Invoice has been declined successfully!')
                        ->send();
                    $this->js("window.location.reload()");
                })
                ->color('danger')
                ->icon('heroicon-m-x-mark'),

            Action::make('Cancel Approve')
                ->hidden(fn () => $this->record->approve_status !== 'approved')
                ->action(function () {
                    $this->record->update(['approve_status' => 'pending']);
                    Notification::make()
                        ->success()
                        ->title('Invoice Approval Canceled')
                        ->body('Invoice approval has been canceled and moved back to Pending.')
                        ->send();
                    $this->js("window.location.reload()");
                })
                ->color('warning')
                ->icon('heroicon-m-arrow-left'),

            Action::make('Cancel Decline')
                ->hidden(fn () => $this->record->approve_status !== 'declined')
                ->action(function () {
                    $this->record->update(['approve_status' => 'pending']);
                    Notification::make()
                        ->success()
                        ->title('Invoice Decline Canceled')
                        ->body('Invoice decline has been canceled and moved back to Pending.')
                        ->send();
                    $this->js("window.location.reload()");
                })
                ->color('warning')
                ->icon('heroicon-m-arrow-left'),

            Actions\DeleteAction::make()
                ->hidden(fn () => $this->record->approve_status === 'approved')
                ->icon('heroicon-m-trash'),
        ];
    }
}
