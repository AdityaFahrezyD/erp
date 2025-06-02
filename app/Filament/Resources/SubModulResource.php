<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubModulResource\Pages;
use App\Filament\Resources\SubModulResource\RelationManagers;
use App\Models\SubModul;
use App\Models\GoingProject;
use App\Models\ProjectModul;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;


class SubModulResource extends Resource
{
    protected static ?string $model = SubModul::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Project';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('project_id')
                ->label('Project')
                ->options(fn () => GoingProject::pluck('project_name', 'project_id'))
                ->reactive()
                ->dehydrated(false)
                ->required(),

            Select::make('modul_id')
                ->label('Modul')
                ->options(function (callable $get) {
                    $projectId = $get('project_id');
                    if (!$projectId) {
                        return [];
                    }

                    return ProjectModul::where('project_id', $projectId)
                        ->pluck('nama_modul', 'id');
                })
                ->disabled(fn (callable $get) => !$get('project_id'))
                ->reactive()
                ->required(),

            TextInput::make('nama_sub_modul')
                ->label('Nama Sub Modul')
                ->required(),

            Textarea::make('deskripsi_sub_modul')
                ->label('Deskripsi Sub Modul')
                ->nullable(),
        ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_sub_modul')->label('Nama Sub Modul')->searchable(),
                TextColumn::make('sub_modul.nama_modul')->label('Modul'),
                TextColumn::make('deskripsi_sub_modul')->label('Deskripsi')->wrap(),
                TextColumn::make('created_at')->label('Dibuat')->dateTime('d M Y'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('project_id')
                    ->label('Project')
                    ->options(fn () =>
                        \App\Models\GoingProject::whereIn('status', ['Pending', 'On Progress'])
                            ->pluck('project_name', 'project_id')
                    )
                    ->query(function (Builder $query, $state) {
                        return $query->whereHas('modul', function ($query) use ($state) {
                            $query->where('project_id', $state);
                        });
                    }),

                Tables\Filters\SelectFilter::make('modul_id')
                    ->label('Modul')
                    ->options(fn () => \App\Models\ProjectModul::pluck('nama_modul', 'id'))
                    ->query(fn (Builder $query, $state) => $query->where('modul_id', $state)),
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
            RelationManagers\StaffRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubModuls::route('/'),
            'create' => Pages\CreateSubModul::route('/create'),
            'edit' => Pages\EditSubModul::route('/{record}/edit'),
        ];
    }
}
