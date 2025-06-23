<?php

namespace App\Filament\Resources\SubModulResource\RelationManagers;

use App\Models\Pegawai;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StaffRelationManager extends RelationManager
{
    protected static string $relationship = 'staff';

    public function form(Form $form): Form
    {
        return $form->schema([
            Select::make('id_pegawai')
                ->label('Staff')
                ->options(function (RelationManager $livewire) {
                    $subModul = $livewire->getOwnerRecord();
                    $assignedStaffIds = $subModul->staff()->pluck('id_pegawai')->toArray();

                    return Pegawai::whereHas('posisi', function ($query) {
                            $query->where('posisi', 'Staff IT'); // posisi = 'Staff IT'
                        })
                        ->whereNotIn('pegawai_id', $assignedStaffIds)
                        ->get()
                        ->mapWithKeys(fn ($pegawai) => [$pegawai->pegawai_id => $pegawai->nama]);
                })
                ->searchable()
                ->required(),


            Select::make('status')
                ->label('Status Pengerjaan')
                ->options([
                    'new' => 'New',
                    'on progress' => 'On Progress',
                    'ready for test' => 'Ready for Test',
                    'done' => 'Done',
                ])
                ->default('new')
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id_pegawai')
            ->columns([
                TextColumn::make('pegawai.nama')->label('Staff'),
                TextColumn::make('status')->label('Status')->badge(),
                TextColumn::make('created_at')->label('Ditambahkan')->dateTime('d M Y'),
            ])
            ->filters([])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
}
