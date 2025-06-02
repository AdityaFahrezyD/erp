<?php

namespace App\Filament\Resources\GoingProjectResource\RelationManagers\ModulesRelationManager;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Forms;
use Illuminate\Database\Eloquent\Builder;

class StaffRelationManager extends RelationManager
{
    protected static string $relationship = 'staff'; // relasi dari ProjectModul ke ProjectStaff

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Select::make('id_user')
                ->relationship('user', 'name')
                ->searchable()
                ->required(),
            Forms\Components\Select::make('status')
                ->options([
                    'New' => 'New',
                    'In progress' => 'In progress',
                    'Ready for test' => 'Ready for test',
                    'Closed' => 'Closed',
                ])
                ->default('New'),
        ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('user.name')->label('Nama Staff'),
            Tables\Columns\TextColumn::make('status'),
        ]);
    }
}
