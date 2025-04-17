<?php

namespace App\Filament\Finance\Resources\InvoiceResource\Pages;

use App\Filament\Finance\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;
}
