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
            // Menambahkan fitur cronjob hanya untuk role selain owner
            auth()->user()->role !== 'owner'
                ? Forms\Components\TextInput::make('cronjob')->label('Cronjob')->required()
                : null,
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
                    ->colors([
                        'pending' => 'warning',
                        'approved' => 'success',
                        'declined' => 'danger',
                    ])
                    ->formatStateUsing(function (string $state): string {
                        return match ($state) {
                            'pending' => 'Pending',
                            'approved' => 'Approved',
                            'declined' => 'Declined',
                            default => ucfirst($state),
                        };
                    }),
            ])
            ->filters([/* Additional filters can be added here */])
            ->actions([Tables\Actions\EditAction::make()])
            ->headerActions([
                Tables\Actions\Action::make('export_all_pdf')
                    ->label('Download PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function () {
                        $payrolls = Payroll::all();
                        $pdf = Pdf::loadView('exports.payrolls', ['payrolls' => $payrolls])->setPaper('a4', 'landscape');

                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->stream();
                        }, 'Data Payroll.pdf');
                    }),
            ])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
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
