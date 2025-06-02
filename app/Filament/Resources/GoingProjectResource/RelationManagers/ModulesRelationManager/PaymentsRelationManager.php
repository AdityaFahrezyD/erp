<?php

namespace App\Filament\Resources\GoingProjectResource\RelationManagers\ModulesRelationManager;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Forms;
use Filament\Tables\Table;
use Filament\Forms\Form;
use App\Models\Invoice;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'invoices'; // dari model ProjectModul

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_amount')->money('IDR'),
                Tables\Columns\TextColumn::make('send_date')->date(),
                Tables\Columns\TextColumn::make('approve_status')
                    ->badge()
                    ->color(fn(string $state): string => match($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'declined' => 'danger',
                    }),
            ])
            ->filters([])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('invoice_amount')->numeric()->required(),
            Forms\Components\DatePicker::make('send_date')->required(),
            Forms\Components\Select::make('approve_status')
                ->options([
                    'pending' => 'Pending',
                    'approved' => 'Approved',
                    'declined' => 'Declined',
                ])->default('pending'),
        ]);
    }
}