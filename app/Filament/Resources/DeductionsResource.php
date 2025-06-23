<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeductionsResource\Pages;
use App\Models\Deductions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DeductionsResource extends Resource
{
    protected static ?string $model = Deductions::class;

    protected static ?string $navigationIcon = 'heroicon-o-minus-circle';
    protected static ?string $navigationLabel = 'Manajemen Pemotongan';
    protected static ?string $navigationGroup = 'Penggajian';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('fk_pegawai_id')
                    ->label('Pegawai')
                    ->options(\App\Models\Pegawai::pluck('nama', 'pegawai_id'))
                    ->required(),
                Forms\Components\Select::make('deduction_type')
                    ->label('Jenis Pemotongan')
                    ->options([
                        'penalty' => 'Denda',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->label('Jumlah')
                    ->numeric()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('pegawai.nama')
                    ->label('Nama Pegawai')
                    ->searchable(),
                Tables\Columns\TextColumn::make('deduction_type')
                    ->label('Jenis Pemotongan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('fk_pegawai_id')
                    ->label('Pegawai')
                    ->options(\App\Models\Pegawai::pluck('nama', 'pegawai_id')),
                Tables\Filters\SelectFilter::make('deduction_type')
                    ->label('Jenis Pemotongan')
                    ->options([
                        'tax' => 'Pajak',
                        'insurance' => 'Asuransi',
                        'penalty' => 'Denda',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDeductions::route('/'),
            'create' => Pages\CreateDeductions::route('/create'),
            'edit' => Pages\EditDeductions::route('/{record}/edit'),
        ];
    }
}