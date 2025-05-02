<?php

namespace App\Filament\Exports;

use App\Models\Finance;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class FinanceExporter extends Exporter
{
    protected static ?string $model = Finance::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('finance_id'),
            ExportColumn::make('transaction_id'),
            ExportColumn::make('user_id'),
            ExportColumn::make('type'),
            ExportColumn::make('date'),
            ExportColumn::make('description'),
            ExportColumn::make('amount'),
            ExportColumn::make('saldo'),
            ExportColumn::make('notes'),
            ExportColumn::make('status_pembayaran'),
            ExportColumn::make('approve_status'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
            ExportColumn::make('deleted_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your finance export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
