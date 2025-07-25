<?php

namespace App\Filament\Resources;

use App\Filament\Exports\FinanceExporter;
use App\Filament\Imports\FinanceImporter;
use Filament\Actions\ImportAction;
use App\Filament\Resources\FinanceResource\Pages;
use App\Filament\Resources\FinanceResource\RelationManagers;
use App\Models\Finance;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Illuminate\Support\Str;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Tables\Actions\ExportAction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FinanceResource extends Resource
{
    protected static ?string $model = Finance::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    

    public static function form(Form $form): Form
    {
        // Get current user
        $user = Auth::user();
        $isRestrictedRole = $user && in_array($user->role, ['finance', 'staff']);

        // Check if record exists (edit mode) or new (create mode)
        $isEditMode = $form->getRecord() !== null;

        // Create base form schema
        $formSchema = [
            // Use a finance_id field (UUID)
            Hidden::make('finance_id')
                ->default(fn() => (string) Str::uuid())
                ->dehydrated(true)
                ->visibleOn('create'),
                
            // Generate transaction_id for new records
            Hidden::make('transaction_id')
                ->default(function() {
                    return 'TRX-'.strtoupper(Str::random(8));
                })
                ->dehydrated(true)
                ->visibleOn('create'),

            // Set saldo (this will be calculated in the model instead)
            Hidden::make('saldo')
                ->dehydrated(true),

            // Auto-assign user_id for all users
            Hidden::make('user_id')
                ->default(fn() => Auth::id())
                ->dehydrated(true),
                
            Select::make('type')
                ->label('Tipe Transaksi')
                ->required()
                ->reactive()
                ->options([
                    'invoice' => 'Invoice',
                    'payroll' => 'Payroll',
                    'other' => 'Other',
                ]),
        
            DatePicker::make('date')
                ->label('Tanggal Transaksi')
                ->required()
                ->default(now()),

            // Field untuk pilih judul berdasarkan tipe
            Select::make('judul_transaksi')
                ->label('Transaksi')
                ->options(function (callable $get) {
                    $type = $get('type');

                    if ($type === 'invoice') {
                        return \App\Models\Invoice::pluck('information', 'invoice_id');
                    }

                    if ($type === 'payroll') {
                        return \App\Models\Payroll::pluck('email_penerima', 'payroll_id');
                    }

                    if ($type === 'other') {
                        return \App\Models\OtherExpense::pluck('nama_pengeluaran', 'expense_id');
                    }

                    return [];
                })
                ->required()
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                    $type = $get('type');

                    if ($type === 'invoice') {
                        $set('fk_invoice_id', $state);
                        $set('fk_payroll_id', null);
                        $set('fk_expense_id', null);
                    } elseif ($type === 'payroll') {
                        $set('fk_payroll_id', $state);
                        $set('fk_invoice_id', null);
                        $set('fk_expense_id', null);
                    } elseif ($type === 'project') {
                        $set('fk_expense_id', $state);
                        $set('fk_invoice_id', null);
                        $set('fk_payroll_id', null);
                    } else {
                        $set('fk_invoice_id', null);
                        $set('fk_payroll_id', null);
                        $set('fk_expense_id', null);
                    }
                })
                ->hidden(fn (callable $get) => $get('type') === null)
                ->placeholder('Pilih judul berdasarkan tipe transaksi'),

            // Hidden fields untuk simpan ke kolom fk
            Hidden::make('fk_invoice_id')->dehydrated(),
            Hidden::make('fk_payroll_id')->dehydrated(),
            Hidden::make('fk_expense_id')->dehydrated(),
        
            TextInput::make('amount')
                ->label('Amount')
                ->numeric()
                ->required(),
                
            Select::make('status_pembayaran')
                ->label('Status Pembayaran')
                ->options([
                    0 => 'Belum Dibayar',
                    1 => 'Sudah Dibayar',
                ])
                ->default(0),
            
            Textarea::make('notes')
                ->label('Catatan')
                ->nullable()
                ->rows(3),
        ];

        

        return $form->schema($formSchema);
    }

    public static function table(Table $table): Table
    {
        $user = Auth::user();
        $isRestrictedRole = $user && in_array($user->role, ['finance', 'staff']);

        return $table
            // Filter data shown based on user_id for finance and staff users
            ->modifyQueryUsing(function (Builder $query) use ($user, $isRestrictedRole) {
                if ($isRestrictedRole) {
                    return $query->where('user_id', $user->id);
                }
                return $query;
            })
            ->columns([
                TextColumn::make('transaction_id')
                    ->searchable()
                    ->sortable()
                    ->label('ID Transaksi'),
                TextColumn::make('type')
                    ->label('Tipe Transaksi')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->color(fn (string $state): string => match ($state) {
                        'invoice' => 'success',
                        'payroll' => 'warning',
                        'other' => 'info',
                    }),
                
                TextColumn::make('judul_transaksi')
                    ->label('Transaksi')
                    ->getStateUsing(function ($record) {
                        return match ($record->type) {
                            'invoice' => optional($record->invoice)->information, // sesuaikan nama kolom
                            'payroll' => optional($record->payroll)->email_penerima, // sesuaikan nama kolom
                            'other' => optional($record->other_expense)->nama_pengeluaran,
                            default => '-',
                        };
                    })
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('amount')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('saldo')
                    ->label('Saldo')
                    ->money('IDR')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('notes')
                    ->label('Catatan')
                    ->limit(20)
                    ->placeholder('Tidak ada catatan')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status_pembayaran')
                    ->label('Status Pembayaran')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->color(fn (int $state): string => match ($state) {
                        0 => 'gray',
                        1 => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => $state ? 'Lunas' : 'Belum Lunas'),

                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipe Transaksi')
                    ->options([
                        'invoice' => 'Invoice',
                        'payroll' => 'Payroll',
                        'other' => 'Other',
                    ]),
                Tables\Filters\SelectFilter::make('status_pembayaran')
                    ->label('Status Pembayaran')
                    ->options([
                        0 => 'Belum Dibayar',
                        1 => 'Sudah Dibayar',
                    ]),
                Tables\Filters\SelectFilter::make('approve_status')
                    ->label('Status Persetujuan')
                    ->options([
                        0 => 'Menunggu Persetujuan',
                        1 => 'Disetujui',
                        2 => 'Ditolak',
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
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(!$isRestrictedRole), // Only admin/owner can use bulk delete
                    Tables\Actions\ExportBulkAction::make()->exporter(FinanceExporter::class),
                    Tables\Actions\BulkAction::make('approveBulk')
                        ->label('Confirm status payment')
                        ->action(fn ($records) => $records->each(fn ($record) =>
                            $record->update(['status_pembayaran' => 1])
                        ))
                        ->requiresConfirmation()
                        ->color('success')
                        ->icon('heroicon-m-check'),
                ]),
            ])
            ->headerActions([
                Tables\Actions\ImportAction::make()
                    ->importer(FinanceImporter::class)
                    ->visible(!$isRestrictedRole), // Only admin/owner can import
                Tables\Actions\ExportAction::make()
                    ->exporter(FinanceExporter::class)
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    // public static function getNavigationBadge(): ?string
    // {
    //     return (string) Finance::whereIn('status', ['Pending', 'On Progress', 'Waiting for Payment'])->count();
    // }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFinances::route('/'),
            'create' => Pages\CreateFinance::route('/create'),
            'edit' => Pages\EditFinance::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        // All authenticated users can view FinanceResource
        return Auth::check();
    }
    
    public static function shouldRegisterNavigation(): bool
    {
        // All authenticated users can see Finance menu in navigation
        return Auth::check();
    }
}