<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OtherExpenseResource\Pages;
use App\Filament\Resources\OtherExpenseResource\RelationManagers;
use App\Models\OtherExpense;
use App\Models\GoingProject;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Hidden;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Collection;

class OtherExpenseResource extends Resource
{
    protected static ?string $model = OtherExpense::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';
    protected static ?string $navigationLabel = 'Expense';

    public static function form(Form $form): Form
    {
        $user = Auth::user();
        $isRestrictedRole = $user && in_array($user->role, ['finance', 'staff']);

        // Check if record exists (edit mode) or new (create mode)
        $isEditMode = $form->getRecord() !== null;

        $formSchema = [
            Hidden::make('expense_id')
                ->default(fn() => (string) Str::uuid())
                ->dehydrated(true)
                ->visibleOn('create'),

            Hidden::make('user_id')
                ->default(fn() => Auth::id())
                ->dehydrated(true),

            Select::make('type_expense')
                ->label('Jenis Pengeluaran')
                ->required()
                ->reactive()
                ->options([
                    'project' => 'Project',
                    'other' => 'Other',
                ])
                ->afterStateUpdated(function ($state, callable $set) {
                    if ($state === 'other') {
                        $set('judul_project', null);
                        $set('fk_project_id', null);
                        $set('project_staff_id', []);
                    }
                }),

            Select::make('judul_project')
                ->label('Judul Project')
                ->options(function (callable $get) {
                    $type = $get('type_expense');
                    if ($type === 'project') {
                        return \App\Models\GoingProject::pluck('project_name', 'project_id');
                    }
                    return [];
                })
                ->required()
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                    $type = $get('type_expense');
                    if ($type === 'project') {
                        $set('fk_project_id', $state);
                        $set('project_staff_id', []); // Reset staff_ids saat proyek berubah
                    } else {
                        $set('fk_project_id', null);
                        $set('project_staff_id', []);
                    }
                })
                ->hidden(fn (callable $get) => $get('type_expense') !== 'project')
                ->placeholder('Pilih project'),

            Hidden::make('fk_project_id')->dehydrated(),

            Select::make('nama_pengeluaran')
                ->label('Nama Pengeluaran')
                ->required()
                ->reactive()
                ->options(function (callable $get) {
                    $type = $get('type_expense');
                    if ($type === 'project') {
                        return [
                            'transport' => 'Transport',
                            'accommodation' => 'Accommodation',
                            'consultant' => 'Consultant',
                            'printing' => 'Printing',
                            'equipment' => 'Equipment',
                            'entertainment' => 'Entertainment',
                        ];
                    } elseif ($type === 'other') {
                        return [
                            'vacation' => 'Vacation',
                            'tax' => 'Tax',
                        ];
                    }
                    return [];
                })
                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                    $type = $get('type_expense');
                    if ($type === 'project' && !in_array($state, ['transport', 'accommodation'])) {
                        $set('project_staff_id', []); // Reset staff_ids jika bukan transport/accommodation
                    }
                }),

            Select::make('project_staff_id')
                ->label('Nama Pegawai')
                ->multiple() // Memungkinkan pemilihan lebih dari satu pegawai
                ->options(function (callable $get) {
                    // Ambil project_id dari form
                    $projectId = $get('fk_project_id');
                    
                    if ($projectId) {
                        // Query GoingProject dengan eager loading relasi
                        $project = GoingProject::with(['modules.sub_modul.pegawai'])
                            ->find($projectId);
                        
                        if ($project) {
                            $pegawai = collect();
                            
                            // Kumpulkan semua pegawai dari sub modul
                            foreach ($project->modules as $module) {
                                foreach ($module->sub_modul as $subModul) {
                                    $pegawai = $pegawai->merge($subModul->pegawai);
                                }
                            }
                            
                            // Kembalikan daftar unik pegawai dengan id dan nama
                            return $pegawai->pluck('nama', 'pegawai_id')->unique()->all();
                        }
                    }
                    
                    return []; // Kembalikan array kosong jika proyek tidak ditemukan
                })
                ->visible(function (callable $get) {
                    // Tampilkan field hanya jika type_expense adalah 'transport' atau 'accommodation'
                    $type = $get('type_expense');
                    $nama = $get('nama_pengeluaran');
                    return $type === 'project' && in_array($nama, ['transport', 'accommodation']);
                })
                ->required(),

            TextInput::make('keterangan')
                ->required()
                ->label('Keterangan'),

            TextInput::make('jumlah')
                ->label('Amount')
                ->numeric()
                ->required(),

            DatePicker::make('tanggal')
                ->label('Tanggal Transaksi')
                ->required()
                ->default(now()),
        ];

        if (!$isRestrictedRole) {
            $formSchema[] = Select::make('approve_status')
                ->label('Status Persetujuan')
                ->options([
                    'pending' => 'Menunggu Persetujuan',
                    'approved' => 'Disetujui',
                    'declined' => 'Ditolak',
                ])
                ->default(0);
        } else {
            $formSchema[] = Hidden::make('approve_status')
                ->default(0)
                ->dehydrated(true);
        }

        return $form->schema($formSchema);
    }

    public static function table(Table $table): Table
    {
        $user = Auth::user();
        $isRestrictedRole = $user && in_array($user->role, ['finance', 'staff']);

        return $table
            ->columns([
                // TextColumn::make('expense_id')
                //     ->searchable()
                //     ->sortable()
                //     ->label('ID Pengeluaran'),
                TextColumn::make('nama_pengeluaran')
                    ->searchable()
                    ->sortable()
                    ->label('Nama Pengeluaran'),
                TextColumn::make('type_expense')
                    ->label('Jenis Pengeluaran')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->color(fn (string $state): string => match ($state) {
                        'project' => 'info',
                        'other' => 'gray',
                    }),
                TextColumn::make('judul_project')
                    ->label('Judul Project')
                    ->getStateUsing(function ($record) {
                        return match ($record->type) {
                            'project' => optional($record->going_projects)->project_name,
                            'other' => $record->nama_pengeluaran, // untuk other, ambil dari deskripsi atau buat kolom sendiri
                            default => '-',
                        };
                    })
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('keterangan')
                    ->searchable()
                    ->sortable()
                    ->label('Keterangan'),

                TextColumn::make('jumlah')
                    ->label('Amount')  
                    ->money('IDR', locale: 'id')
                    ->sortable(),

                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('approve_status')
                    ->label('Status Persetujuan')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->color(fn ($state): string => match ($state) {
                        'pending' => 'gray',
                        'approved' => 'primary',
                        'declined' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pending' => 'Menunggu Persetujuan',
                        'approved' => 'Disetujui',
                        'declined' => 'Ditolak',
                        default => 'Menunggu Persetujuan',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipe Transaksi')
                    ->options([
                        'project' => 'Project',
                        'other' => 'Other',
                    ]),

                Tables\Filters\SelectFilter::make('approve_status')
                    ->label('Status Persetujuan')
                    ->options([
                        'pending' => 'Menunggu Persetujuan',
                        'approved' => 'Disetujui',
                        'declined' => 'Ditolak',
                    ]),
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('date_from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('date_to')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['date_to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                ->visible(function ($record) use ($user, $isRestrictedRole) {
                        // Finance and staff can only edit their own data that's still pending approval
                        if ($isRestrictedRole) {
                            return $record->approve_status == 0 && $record->user_id == $user->id;
                        }
                        // Admin/owner can edit all
                        return true;
                    }),
                Tables\Actions\DeleteAction::make()
                    ->visible(function ($record) use ($user, $isRestrictedRole) {
                        // Finance and staff can only delete their own data that's still pending approval
                        if ($isRestrictedRole) {
                            return $record->approve_status == 0 && $record->user_id == $user->id;
                        }
                        // Admin/owner can delete all
                        return true;
                    }),
            ])
            ->bulkActions([
                    Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('approveBulk')
                        ->label('Approve selected')
                        ->action(fn ($records) => $records->each(fn ($record) =>
                            $record->update(['approve_status' => 'approved'])
                        ))
                        ->requiresConfirmation()
                        ->color('success')
                        ->icon('heroicon-m-check'),
                    Tables\Actions\BulkAction::make('declineBulk')
                        ->label('Decline selected')
                        ->action(fn ($records) => $records->each(fn ($record) =>
                            $record->update(['approve_status' => 'declined'])
                        ))
                        ->requiresConfirmation()
                        ->color('danger')
                        ->icon('heroicon-m-x-mark'),
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
            'index' => Pages\ListOtherExpenses::route('/'),
            'create' => Pages\CreateOtherExpense::route('/create'),
            'edit' => Pages\EditOtherExpense::route('/{record}/edit'),
        ];
    }
}
