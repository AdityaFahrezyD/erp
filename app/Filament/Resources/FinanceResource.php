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
                ->options([
                    'invoice' => 'Invoice',
                    'payroll' => 'Payroll',
                    'other' => 'Other',
                ]),
        
            DatePicker::make('date')
                ->label('Tanggal Transaksi')
                ->required()
                ->default(now()),
        
            TextInput::make('description')
                ->label('Deskripsi')
                ->required()
                ->maxLength(255),
        
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

        // Add approve_status field only for admin/owner (not finance or staff)
        if (!$isRestrictedRole) {
            $formSchema[] = Select::make('approve_status')
                ->label('Status Persetujuan')
                ->options([
                    0 => 'Menunggu Persetujuan',
                    1 => 'Disetujui',
                    2 => 'Ditolak',
                ])
                ->default(0);
        } else {
            // For finance and staff users, keep approve_status as hidden
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
                        'other' => 'gray',
                    }),

                TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(30)
                    ->searchable(),

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

                TextColumn::make('approve_status')
                    ->label('Status Persetujuan')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->color(fn (int $state): string => match ($state) {
                        0 => 'gray',
                        1 => 'primary',
                        2 => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        0 => 'Menunggu Persetujuan',
                        1 => 'Disetujui',
                        2 => 'Ditolak',
                        default => 'Menunggu Persetujuan',
                    }),
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