<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PegawaiResource\Pages;
use App\Filament\Resources\PegawaiResource\RelationManagers;
use App\Models\Pegawai;
use App\Models\Posisi;
use App\Models\Asuransi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Hidden;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class PegawaiResource extends Resource
{
    protected static ?string $model = Pegawai::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Employee';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // ID Pegawai (hanya saat create)
            Hidden::make('pegawai_id')
                ->default(fn () => (string) Str::uuid())
                ->dehydrated(true)
                ->visibleOn('create'),

            // Nama Pegawai
            TextInput::make('nama')
                ->label('Nama Pegawai')
                ->required(),

            // Posisi (Dropdown berdasarkan fk_posisi_id)
            Select::make('fk_posisi_id')
                ->relationship('posisi', 'posisi')
                ->label('Posisi')
                ->required()
                ->reactive()
                ->afterStateUpdated(function ($state, $set) {
                    if ($state) {
                        $posisi = Posisi::find($state);
                        if ($posisi) {
                            $set('base_salary', $posisi->gaji); // Asumsi kolom gaji di Posisi adalah 'salary'
                        }
                    }
                }),

            // Tanggal Masuk
            DatePicker::make('start_date')
                ->label('Tanggal Masuk')
                ->required()
                ->default(now()),

            // Nomor HP
            TextInput::make('phone')
                ->label('Nomor HP')
                ->required(),

            // Email
            TextInput::make('email')
                ->label('Email')
                ->required(),

            // Status Pernikahan
            Select::make('status')
                ->options([
                    'single' => 'Single',
                    'maried' => 'Married',
                ])
                ->label('Status Pernikahan')
                ->required(),

            // Jumlah Tanggungan
            TextInput::make('tanggungan')
                ->numeric()
                ->minValue(0)
                ->label('Jumlah Tanggungan')
                ->required(),

            // Asuransi (Dropdown berdasarkan fk_asuransi_id)
            Select::make('fk_asuransi_id')
                ->relationship('asuransi', 'tingkat')
                ->label('Asuransi')
                ->required(),

            // Gaji Dasar
            TextInput::make('base_salary')
                ->label('Gaji Dasar')
                ->numeric()
                ->required(),

            // Waktu Penggajian
            Select::make('pay_cycle')
                ->label('Waktu Penggajian')
                ->required()
                ->options([
                    'monthly' => 'Setiap Bulan',
                    'weekly' => 'Setiap Minggu',
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        $user = Auth::user();
        $isRestrictedRole = $user && in_array($user->role, ['owner', 'finannce']);
        return $table
            ->columns([
                TextColumn::make('nama')
                    ->searchable()
                    ->sortable()
                    ->label('Nama Pegawai'),

                TextColumn::make('posisi.posisi')
                    ->searchable()
                    ->sortable()
                    ->label('Posisi'),

                TextColumn::make('start_date')
                    ->date('d M Y')
                    ->searchable()
                    ->sortable()
                    ->label('Tanggal Masuk'),

                TextColumn::make('phone')
                    ->searchable()
                    ->sortable()
                    ->label('Nomor HP'),

                TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->label('Email'),

                // TextColumn::make('status')
                //     ->label('Status Pernikahan')
                //     ->badge()
                //     ->searchable()
                //     ->sortable()
                //     ->color(fn ($state): string => match ($state) {
                //         'single' => 'info',
                //         'maried' => 'success',
                //         default => 'gray',
                //     })
                //     ->formatStateUsing(fn ($state) => match ($state) {
                //         'single' => 'Single',
                //         'maried' => 'Married',
                //         default => 'Unknown',
                //     }),

                // TextColumn::make('tanggungan')
                //     ->searchable()
                //     ->sortable()
                //     ->label('Jumlah Tanggungan'),

                // TextColumn::make('base_salary')
                //     ->label('Gaji Dasar')  
                //     ->money('IDR', locale: 'id')
                //     ->sortable(),

                // TextColumn::make('asuransi.nama')
                //     ->searchable()
                //     ->sortable()
                //     ->label('Asuransi'),

                TextColumn::make('pay_cycle')
                    ->label('Waktu Penggajian')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->color(fn ($state): string => match ($state) {
                        'monthly' => 'success',
                        'weekly' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'monthly' => 'Setiap Bulan',
                        'weekly' => 'Setiap Minggu',
                        default => 'Setiap Bulan',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('pay_cycle')
                    ->label('Waktu Penggajian')
                    ->options([
                        'monthly' => 'Setiap Bulan',
                        'weekly' => 'Setiap Minggu',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status Pernikahan')
                    ->options([
                        'single' => 'Single',
                        'maried' => 'Married',
                    ]),

                Tables\Filters\Filter::make('start_date')
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
                                fn (Builder $query, $date): Builder => $query->whereDate('start_date', '>=', $date),
                            )
                            ->when(
                                $data['date_to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('start_date', '<=', $date),
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
            'index' => Pages\ListPegawais::route('/'),
            'create' => Pages\CreatePegawai::route('/create'),
            'edit' => Pages\EditPegawai::route('/{record}/edit'),
        ];
    }
    public static function canViewAny(): bool
    {
        $user = Auth::user();
        return $user && in_array($user->role, ['admin', 'owner']);
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        return $user && in_array($user->role, ['admin', 'owner']);
    }
}
