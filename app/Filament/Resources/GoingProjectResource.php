<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Support\RawJs;
use App\Models\GoingProject;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\GoingProjectResource\Pages;
use App\Filament\Resources\GoingProjectResource\RelationManagers;
use App\Filament\Resources\GoingProjectResource\RelationManagers\ModulesRelationManager;
use App\Filament\Resources\GoingProjectResource\RelationManagers\PaymentsRelationManager;
use App\Filament\Resources\GoingProjectResource\RelationManagers\StaffRelationManager;
use App\Models\Pegawai;


class GoingProjectResource extends Resource
{
    protected static ?string $model = GoingProject::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench';

    protected static ?string $navigationGroup = 'Project';

    protected static ?string $navigationLabel = 'Project';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('project_name')
                    ->label('Nama Project')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('company')
                    ->label('Nama Perusahaan')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('pic')
                    ->label('PIC')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('pic_email')
                    ->label('Email PIC')
                    ->email()
                    ->required()
                    ->maxLength(255),

            Forms\Components\Select::make('project_leader')
                ->label('Project Leader')
                ->options(function () {
                    return Pegawai::whereHas('posisi', function ($query) {
                        $query->where('posisi', 'Staff IT');
                    })
                    ->get()
                    ->mapWithKeys(fn ($pegawai) => [$pegawai->pegawai_id => $pegawai->nama]);

                })
                ->searchable()
                ->preload()
                ->required(),


                Forms\Components\DatePicker::make('batas_awal')
                    ->label('Batas Awal')
                    ->required(),

                    
                Forms\Components\DatePicker::make('batas_akhir')
                    ->label('Batas Akhir')
                    ->required(),

                Forms\Components\TextInput::make('harga_awal')
                    ->label('Harga Awal Proyek')
                    ->numeric()
                    ->prefix('Rp'),

                Forms\Components\TextInput::make('total_harga_proyek')
                    ->label('Total Harga Proyek')
                    ->numeric()
                    ->prefix('Rp')
                    ->disabled()
                    ->reactive(),

                Forms\Components\Select::make('status')
                    ->label('Status Project')
                    ->options([
                        'pending' => 'Pending',
                        'on progress' => 'On Progress',
                        'done' => 'Done',
                        'cancelled' => 'Cancelled',
                        'waiting for payment' => 'Waiting for Payment',
                    ])
                    ->default('pending')
                    ->required(),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('project_name'),
                Tables\Columns\TextColumn::make('harga_awal')->money('IDR'),
                Tables\Columns\TextColumn::make('total_harga_proyek')->money('IDR'),
                Tables\Columns\TextColumn::make('unpaid_amount')->money('IDR'),
                Tables\Columns\TextColumn::make('leader.nama')
                    ->label('Project Leader')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('batas_awal'),
                Tables\Columns\TextColumn::make('batas_akhir'),
                Tables\Columns\TextColumn::make('status')
                ->label('Status Proyek')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'on progress' => 'primary',
                        'done' => 'success',
                        'cancelled' => 'danger',
                        'waiting for payment' => 'warning',
                        default => 'gray',
                    })            

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
        ModulesRelationManager::class,
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) GoingProject::whereIn('status', ['Pending', 'On Progress', 'Waiting for Payment'])->count();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGoingProjects::route('/'),
            'create' => Pages\CreateGoingProject::route('/create'),
            'edit' => Pages\EditGoingProject::route('/{record}/edit'),
        ];
    }
}
