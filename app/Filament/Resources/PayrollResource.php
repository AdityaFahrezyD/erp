<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PayrollResource\Pages;
use App\Models\Payroll;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Collection;

class PayrollResource extends Resource
{
    protected static ?string $model = Payroll::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    public static function form(Form $form): Form
    {
        if (auth()->user()->role === 'owner') {
            return $form->schema([
                Forms\Components\TextInput::make('penerima')->required(),
                Forms\Components\TextInput::make('keterangan')->required(),
                Forms\Components\TextInput::make('harga')->numeric()->required(),
                Forms\Components\TextInput::make('email_penerima')->email()->required(),
                Forms\Components\DatePicker::make('tanggal_kirim')->required(),
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

        return $form->schema([
            Forms\Components\TextInput::make('penerima')->required(),
            Forms\Components\TextInput::make('keterangan')->required(),
            Forms\Components\TextInput::make('harga')->numeric()->required(),
            Forms\Components\TextInput::make('email_penerima')->email()->required(),
            Forms\Components\DatePicker::make('tanggal_kirim')->required(),
            Forms\Components\Select::make('approve_status')
                ->label('Status Persetujuan')
                ->options([
                    'pending' => 'Pending',
                    'approved' => 'Approved',
                    'declined' => 'Declined',
                ])
                ->default('pending')
                ->required(),
            Forms\Components\Checkbox::make('is_repeat')
                ->label('Kirim Ulang Setiap Bulan')
                ->default(false),
        ]);
    }



    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('penerima')->searchable(),
                Tables\Columns\TextColumn::make('keterangan')->searchable(),
                Tables\Columns\TextColumn::make('harga')->money('IDR'),
                Tables\Columns\TextColumn::make('email_penerima')->searchable(),
                Tables\Columns\TextColumn::make('tanggal_kirim')->date(),
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
                            ->when($data['from'], fn ($q) => $q->whereDate('tanggal_kirim', '>=', $data['from']))
                            ->when($data['until'], fn ($q) => $q->whereDate('tanggal_kirim', '<=', $data['until']));
                    }),

                Tables\Filters\TernaryFilter::make('is_repeat')
                    ->label('Kirim Ulang')
                    ->trueLabel('Ya')
                    ->falseLabel('Tidak'),

                Tables\Filters\Filter::make('email_penerima')
                    ->form([
                        Forms\Components\TextInput::make('email')->label('Email Penerima'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query->when($data['email'], fn ($q) => $q->where('email_penerima', 'like', "%{$data['email']}%"));
                    }),
            ])
            ->actions([Tables\Actions\EditAction::make()])
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
                    ->icon('heroicon-o-check-badge')
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
            'edit' => Pages\EditPayroll::route('/{record}/edit'),
        ];
    }
}
