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

class GoingProjectResource extends Resource
{
    protected static ?string $model = GoingProject::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench';

    protected static ?string $navigationGroup = 'Project';

    public static function form(Form $form): Form
    {
                
        return $form
            ->schema([
                Forms\Components\TextInput::make('project_name'),
                Forms\Components\Select::make('status')
                ->label('Status Project')
                ->required()
                ->options([
                     'pending' => 'pending',
                     'on progress' => 'on progress',
                     'done' => 'done',
                     'cancelled' => 'cancelled',
                     'waiting for payment' => 'waiting for payment'
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
                Tables\Columns\TextColumn::make('total_harga_proyek')->money('IDR'),
                Tables\Columns\TextColumn::make('unpaid_amount')->money('IDR'),
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
