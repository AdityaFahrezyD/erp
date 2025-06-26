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
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\Filter;

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

            Forms\Components\DatePicker::make('batas_awal')
                ->label('Batas Awal')
                ->required()
                ->rules(function (callable $get) {
                    $projectId = $get('project_id');
                    if (!$projectId) {
                        return [];
                    }

                    $project = \App\Models\GoingProject::find($projectId);

                    if (!$project) {
                        return [];
                    }

                    return [
                        'date',
                        'after_or_equal:' . $project->batas_awal,
                    ];
                }),

                    
            Forms\Components\DatePicker::make('batas_akhir')
                ->label('Batas Akhir')
                ->required()
                ->rules(function (callable $get) {
                    $batasAwal = $get('batas_awal');
                    if (!$batasAwal) {
                        return [];
                    }

                    return [
                        'date',
                        'after_or_equal:' . $batasAwal,
                    ];
                }),           

            Textarea::make('deskripsi_sub_modul')
                ->label('Deskripsi Sub Modul')
                ->nullable(),

            TextInput::make('optimistic_time')
                ->label('Waktu Optimis')
                ->numeric()
                ->minValue(1)
                ->suffix('hari'),

            TextInput::make('most_likely_time')
                ->label('Waktu Realistis')
                ->numeric()
                ->minValue(1)
                ->suffix('hari'),

            TextInput::make('pessimistic_time')
                ->label('Waktu Pesimis')
                ->numeric()
                ->minValue(1)
                ->suffix('hari'),

            Select::make('dependencies')
                ->label('Bergantung pada Submodul')
                ->multiple()
                ->options(function (callable $get, ?SubModul $record) {
                    $modulId = $get('modul_id');
                    if (!$modulId) return [];

                    // Ambil semua submodul dari modul yang sama, kecuali dirinya sendiri (saat edit)
                    return \App\Models\SubModul::where('modul_id', $modulId)
                        ->when($record, fn($q) => $q->where('id', '!=', $record->id))
                        ->pluck('nama_sub_modul', 'id');
                })
                ->searchable()
                ->reactive()
                ->columnSpan('full'),


            Placeholder::make('expected_time')
                ->label('Waktu Ekspektasi (PERT)')
                ->content(fn ($record) => $record?->expected_time ? number_format($record->expected_time, 2) . ' hari' : '-'),

            Placeholder::make('variance')
                ->label('Variansi Waktu')
                ->content(fn ($record) => $record?->variance ? number_format($record->variance, 2) . ' hari²' : '-'),
            
            Placeholder::make('is_critical_path')
                ->label('Critical Path?')
                ->content(fn ($record) => $record?->is_critical_path ? 'Ya (Jalur Kritis)' : 'Tidak')
                ->hint('Ditentukan otomatis berdasarkan analisis PERT'),
        ]);

    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_sub_modul')->label('Nama Sub Modul')->searchable(),
                TextColumn::make('sub_modul.nama_modul')->label('Modul'),
                TextColumn::make('deskripsi_sub_modul')->label('Deskripsi')->wrap(),
                // TextColumn::make('created_at')->label('Dibuat')->dateTime('d M Y'),
                TextColumn::make('expected_time')
                    ->label('Waktu Ekspektasi')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 2) . ' hari' : '-'),

                TextColumn::make('variance')
                    ->label('Variansi')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 2) . ' hari²' : '-'),

                IconColumn::make('is_critical_path')
                    ->label('Critical Path')
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-minus-circle')
                    ->trueColor('danger')
                    ->falseColor('gray'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'done' => 'success',
                        'on progress' => 'primary',
                        'new' => 'gray',
                        default => 'gray',
    }),

            ])

            ->filters([
                Filter::make('project_modul')
                    ->form([
                        Select::make('project_id')
                            ->label('Project')
                            ->placeholder('Pilih Project')
                            ->options(
                                \App\Models\GoingProject::whereIn('status', ['Pending', 'On Progress'])
                                    ->pluck('project_name', 'project_id')
                            )
                            ->reactive()
                            ->afterStateUpdated(fn (callable $set) => $set('modul_id', null)),

                        Select::make('modul_id')
                            ->label('Modul')
                            ->placeholder('Pilih Modul')
                            ->options(function (callable $get) {
                                $projectId = $get('project_id');
                                if (!$projectId) {
                                    return [];
                                }

                                return \App\Models\ProjectModul::where('project_id', $projectId)
                                    ->pluck('nama_modul', 'id');
                            }),
                    ])
                    ->query(function (Builder $query, array $data) {
                        // Jika tidak ada project_id dan modul_id, jangan tampilkan apa pun
                        if (empty($data['project_id']) && empty($data['modul_id'])) {
                            $query->whereRaw('1 = 0'); // untuk menghasilkan query kosong
                            return $query;
                        }

                        if (!empty($data['modul_id'])) {
                            $query->where('modul_id', $data['modul_id']);
                        } elseif (!empty($data['project_id'])) {
                            $query->whereHas('modul', fn ($q) => $q->where('project_id', $data['project_id']));
                        }

                        return $query;
                    }),
            ])
            ->emptyStateHeading('Data Kosong')
            ->emptyStateDescription(function () {
                // Ambil data filter dari query string (karena tidak bisa akses langsung seperti closure)
                $projectId = request()->input('tableFilters.project_modul.project_id');
                $modulId = request()->input('tableFilters.project_modul.modul_id');

                if (empty($projectId) && empty($modulId)) {
                    return 'Belum ada sub modul untuk project/modul ini.';
                }

                return 'Silakan filter project dan modul terlebih dahulu.';
            })

            ->actions([
                Tables\Actions\EditAction::make(),
            ])

            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    // SubModulResource.php

    public static function afterSave(Form $form, SubModul $record): void
    {
        $data = $form->getState();

        if (isset($data['dependencies'])) {
            $record->dependencies()->sync($data['dependencies']);
        }
    }

    public static function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['dependencies']); // Karena tidak disimpan di tabel `sub_modul`
        return $data;
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
