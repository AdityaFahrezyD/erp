<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PayrollResource\Pages;
use App\Models\Payroll;
use App\Models\Pegawai;
use App\Models\Bonuses;
use App\Models\Deductions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

class PayrollResource extends Resource
{
    protected static ?string $model = Payroll::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'Penggajian';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('fk_pegawai_id')
                ->label('Pegawai')
                ->options(Pegawai::pluck('nama', 'pegawai_id'))
                ->required()
                ->reactive()
                ->afterStateUpdated(function ($state, callable $get, callable $set) {
                    $jenis_gaji = $get('jenis_gaji');
                    if ($state && $jenis_gaji) {
                        $pegawai = Pegawai::with(['posisi', 'asuransi'])->find($state);
                        if ($pegawai) {
                            // Buat instance sementara Payroll untuk menghitung
                            $payroll = new Payroll([
                                'fk_pegawai_id' => $state,
                                'jenis_gaji' => $jenis_gaji,
                            ]);
                            $payroll->pegawai = $pegawai; // Set relasi pegawai
                            $payroll->calculateSalary();

                            // Set nilai ke form
                            $set('email_penerima', $pegawai->email);
                            $set('gross_salary', $payroll->gross_salary);
                            $set('net_salary', $payroll->net_salary);
                        }
                    }
                }),


            Forms\Components\Select::make('jenis_gaji')
                ->label('Jenis Gaji')
                ->options([
                    'gaji_pokok' => 'Gaji Pokok',
                    'thr' => 'THR',
                    'tunjangan' => 'Tunjangan',
                ])
                ->required()
                ->reactive()
                ->afterStateUpdated(function ($state, callable $get, callable $set) {
                    $fk_pegawai_id = $get('fk_pegawai_id');
                    if ($fk_pegawai_id && $state) {
                        $pegawai = Pegawai::with(['posisi', 'asuransi'])->find($fk_pegawai_id);
                        if ($pegawai) {
                            // Buat instance sementara Payroll untuk menghitung
                            $payroll = new Payroll([
                                'fk_pegawai_id' => $fk_pegawai_id,
                                'jenis_gaji' => $state,
                            ]);
                            $payroll->pegawai = $pegawai; // Set relasi pegawai
                            $payroll->calculateSalary();

                            // Set nilai ke form
                            $set('email_penerima', $pegawai->email);
                            $set('gross_salary', $payroll->gross_salary);
                            $set('net_salary', $payroll->net_salary);
                        }
                    }
                }),    

            Forms\Components\TextInput::make('gross_salary')
                ->label('Gaji Kotor')
                ->numeric()
                ->required()
                ->disabled()
                ->dehydrated(true),

            Forms\Components\TextInput::make('net_salary')
                ->label('Gaji Bersih')
                ->numeric()
                ->required()
                ->disabled()
                ->dehydrated(true),

            Forms\Components\TextInput::make('email_penerima')
                ->label('Email Penerima')
                ->email()
                ->required()
                ->disabled(fn (callable $get) => $get('fk_pegawai_id') !== null)
                ->dehydrated(true), // Disable field jika pegawai sudah dipilih
            Forms\Components\DatePicker::make('tanggal_kirim')
                ->label('Tanggal Kirim')
                ->required(),
            Forms\Components\Select::make('approve_status')
                ->label('Status Persetujuan')
                ->options([
                    'pending' => 'Pending',
                    'approved' => 'Approved',
                    'declined' => 'Declined',
                ])
                ->default('pending')
                ->required(),
        ]);
    }

    // Bagian table, getEloquentQuery, dan getPages tetap sama seperti sebelumnya
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('pegawai.nama')
                    ->label('Pegawai')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('jenis_gaji')
                    ->label('Jenis Gaji')
                    ->color(fn (string $state): string => match ($state) {
                        'gaji_pokok' => 'success',
                        'thr' => 'warning',
                        'tunjangan' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(function (string $state): string {
                        return match ($state) {
                            'gaji_pokok' => 'Gaji Pokok',
                            'thr' => 'THR',
                            'tunjangan' => 'Tunjangan',
                            default => ucfirst($state),
                        };
                    }),
                Tables\Columns\TextColumn::make('gross_salary')
                    ->label('Gaji Kotor')
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('net_salary')
                    ->label('Gaji Bersih')
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('email_penerima')
                    ->label('Email Penerima')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal_kirim')
                    ->label('Tanggal Kirim')
                    ->date(),
                Tables\Columns\BadgeColumn::make('approve_status')
                    ->label('Status Persetujuan')
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'pending' => 'warning',
                        'declined' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(function (string $state): string {
                        return match ($state) {
                            'pending' => 'Pending',
                            'approved' => 'Approved',
                            'declined' => 'Declined',
                            default => ucfirst($state),
                        };
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('approve_status')
                    ->label('Status Persetujuan')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'declined' => 'Declined',
                    ]),
                Tables\Filters\Filter::make('tanggal_kirim')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('until')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['from'], fn ($query) => $query->whereDate('tanggal_kirim', '>=', $data['from']))
                            ->when($data['until'], fn ($query) => $query->whereDate('tanggal_kirim', '<=', $data['until']));
                    }),
                Tables\Filters\Filter::make('email_penerima')
                    ->form([
                        Forms\Components\TextInput::make('email')->label('Email Penerima'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when($data['email'], fn ($query) => $query->where('email_penerima', 'like', "%{$data['email']}%"));
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                ])
            ->headerActions([
                Tables\Actions\Action::make('export_all_pdf')
                    ->label('Download PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function () {
                        $payrolls = Payroll::where('user_id', auth()->id())->get();
                        $pdf = Pdf::loadView('exports.payrolls', ['payrolls' => $payrolls])->setPaper('a4', 'landscape');
                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->stream();
                        }, 'Data Payroll.pdf');
                    }),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\BulkAction::make('approve_all')
                    ->label('Setujui Semua')
                    ->icon('heroicon-o-check')
                    ->requiresConfirmation()
                    ->action(function (Collection $records) {
                        foreach ($records as $record) {
                            $record->update(['approve_status' => 'approved']);
                        }
                    })
                    ->deselectRecordsAfterCompletion(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        if (auth()->user()->role === 'finance') {
            return parent::getEloquentQuery()->where('user_id', auth()->id());
        }
        return parent::getEloquentQuery();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayrolls::route('/'),
            'create' => Pages\CreatePayroll::route('/create'),
            'view' => Pages\ViewPayroll::route('/{record}'),
            'edit' => Pages\EditPayroll::route('/{record}/edit'),
        ];
    }
}