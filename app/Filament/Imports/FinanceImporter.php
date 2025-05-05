<?php

namespace App\Filament\Imports;

use App\Models\Finance;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class FinanceImporter extends Importer
{
    protected static ?string $model = Finance::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('finance_id')
                ->requiredMapping()
                ->rules(['required', 'max:36']),
            ImportColumn::make('transaction_id')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('user_id')
                ->requiredMapping()
                ->rules(['required', 'max:36']),
            ImportColumn::make('type')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('date')
                ->requiredMapping()
                ->rules(['required', 'date']),
            ImportColumn::make('description')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('amount')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('saldo')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('notes'),
            ImportColumn::make('status_pembayaran')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('approve_status')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
        ];
    }

    public function resolveRecord(): ?Finance
    {
        // return Finance::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Finance();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your finance import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
