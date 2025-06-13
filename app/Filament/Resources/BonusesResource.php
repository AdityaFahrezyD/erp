<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BonusesResource\Pages;
use App\Models\Bonuses;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BonusesResource extends Resource
{
    protected static ?string $model = Bonuses::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift';
    protected static ?string $navigationLabel = 'Manajemen Bonus';
    protected static ?string $navigationGroup = 'Penggajian';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('fk_pegawai_id')
                    ->label('Pegawai')
                    ->options(\App\Models\Pegawai::pluck('nama', 'pegawai_id'))
                    ->required(),
                Forms\Components\TextInput::make('bonus_type')
                    ->label('Jenis Bonus')
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
                Tables\Columns\TextColumn::make('bonus_type')
                    ->label('Jenis Bonus')
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
            'index' => Pages\ListBonuses::route('/'),
            'create' => Pages\CreateBonuses::route('/create'),
            'edit' => Pages\EditBonuses::route('/{record}/edit'),
        ];
    }
}