<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Filament\Resources\InvoiceResource\RelationManagers;
use App\Models\Invoice;
use Dom\Text;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Radio;
use Filament\Support\RawJs;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Facades\Request;
use Filament\Forms\Get;
use App\Models\ProjectModul;
use App\Models\GoingProject;



class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-s-document';

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();

        $panelId = Filament::getCurrentPanel()?->getId();
        $user = auth()->user();

        if ($panelId === 'finance') {
            $query->where('user_id', $user->id);
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        $panelId = Filament::getCurrentPanel()?->getId();
        $isReadonly = $panelId === 'finance';
        $isCreate = blank($form->getRecord());
        if (!$isCreate && !$isReadonly) {
            $isReadonly = $form->getRecord()->approve_status !== 'pending';
        }
        $isEdit = filled($form->getRecord());

        return $form->schema(array_filter([
            //Hidden::make('user_id')->default(auth()->id()),

            $isReadonly && !$isCreate
                ? Placeholder::make('information')
                    ->label('Invoice Name')
                    ->content(fn ($record) => $record->information)
                : TextInput::make('information')
                    ->label('Information')
                    ->required(),

            $isReadonly && !$isCreate
                ? Placeholder::make('project_id')
                    ->label('Project Name')
                    ->content(fn ($record) => optional($record->project)->project_name ?? '-')
                : Select::make('project_id')
                    ->label('Project Name')
                    ->relationship('project', 'project_name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->default(Request::input('project_id'))
                    ->reactive()
                    ->afterStateUpdated(fn ($state, callable $set) => $set('modul_id', null)),

            $isReadonly && !$isCreate
                ? Placeholder::make('modul_id')
                    ->label('Modul Name')
                    ->content(fn ($record) => optional($record->modul)->nama_modul ?? '-')
                : Select::make('modul_id')
                    ->label('Modul Name')
                    ->options(function (Get $get) {
                        $projectId = $get('project_id');
                        if (!$projectId) return [];
                        return \App\Models\ProjectModul::where('project_id', $projectId)->pluck('nama_modul', 'id');
                    })
                    ->searchable()
                    ->preload()
                    ->required()
                    ->disabled(fn (Get $get) => ! $get('project_id'))
                    ->default(Request::input('modul_id'))
                    ->afterStateUpdated(function (\Filament\Forms\Set $set, $state) {
                        if (!$state) return;
                        $modul = ProjectModul::find($state);
                        $set('unpaid_amount', $modul?->unpaid_amount ?? 0);
                    }),

            TextInput::make('unpaid_amount')
                ->label('Sisa Tagihan')
                ->numeric()
                ->disabled()
                ->dehydrated(false)
                ->reactive()
                ->default(0) // Default awal saja
                ->afterStateHydrated(function (\Filament\Forms\Set $set, ?string $state, Get $get) { // Corrected type hint here
                    $modulId = $get('modul_id');
                    if (!$modulId) return;
                    $modul = ProjectModul::find($modulId);
                    $set('unpaid_amount', $modul?->unpaid_amount ?? 0);
                }),


            $isReadonly && !$isCreate
                ? Placeholder::make('recipient')
                    ->label('Recipient')
                    ->content(fn ($record) => $record->recipient)
                : TextInput::make('recipient')
                    ->label('Recipient')
                    ->default(Request::input('recipient'))
                    ->required(),

            $isReadonly && !$isCreate
                ? Placeholder::make('company')
                    ->label('Company Name')
                    ->content(fn ($record) => $record->company)
                : TextInput::make('company')
                    ->default(Request::input('company'))
                    ->label('Company Name'),

            $isReadonly && !$isCreate
                ? Placeholder::make('recipient_email')
                    ->label('Recipient Email')
                    ->content(fn ($record) => $record->recipient_email)
                : TextInput::make('recipient_email')
                    ->label('Recipient Email')
                    ->default(Request::input('recipient_email'))
                    ->required(),

            $isReadonly && !$isCreate
                ? Placeholder::make('invoice_amount')
                    ->label('Amount')
                    ->content(fn ($record) => 'Rp ' . number_format($record->invoice_amount, 0, ',', '.'))
                : TextInput::make('invoice_amount')
                    ->label('Amount')
                    ->numeric()
                    ->mask(RawJs::make('$money($input)'))
                    ->stripCharacters(',')
                    ->minValue(0)
                    ->required()
                    ->maxValue(function (Get $get) {
                        $modul = \App\Models\ProjectModul::find($get('modul_id'));
                        return $modul?->unpaid_amount;
                    }),


            $isReadonly && !$isCreate
                ? Placeholder::make('is_repeat')
                    ->label('Invoice Type')
                    ->content(fn ($record) => $record->is_repeat ? 'Repeat Monthly' : 'One-Time')
                : Select::make('is_repeat')
                    ->label('Invoice Type')
                    ->options([
                        false => 'One-Time Invoice',
                        true => 'Repeat Monthly',
                    ])
                    ->native(false)
                    ->required(),
            
            $isReadonly && !$isCreate
                ? Placeholder::make('send_date')
                    ->content(fn ($record) => \Carbon\Carbon::parse($record->send_date)->format('d M Y'))
                : DatePicker::make('send_date')
                    ->minDate(Carbon::tomorrow())
                    ->required(),

            $isEdit && $isReadonly
                ? Placeholder::make('approve_status')
                    ->content(fn ($record) => $record->approve_status)
                : null,
        ]));
    }


    public static function table(Table $table): Table
    {
        $panelId = Filament::getCurrentPanel()?->getId();

        $columns = [
            TextColumn::make('no')
                ->label('No')
                ->state(function ($record, $livewire) {
                    $records = $livewire->getTableRecords();
                    $index = $records->search(fn ($item) => $item->getKey() === $record->getKey());
                    return ((int)$livewire->getTablePage() - 1) * (int)$livewire->getTableRecordsPerPage() + $index + 1;
                })
                ->alignCenter(),
            Tables\Columns\TextColumn::make('project.project_name')->label('Project')->searchable(),
            
            Tables\Columns\TextColumn::make('modul.nama_modul')->label('Modul')->searchable(),
            TextColumn::make('information')->label('Invoice Name')->searchable(),
            TextColumn::make('recipient')->searchable()->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('invoice_amount')->label('Amount')->money('IDR', locale: 'id')->sortable(),
            TextColumn::make('is_repeat')->label('Type')->formatStateUsing(fn ($state) => $state ? 'Repeat Monthly' : 'One-Time'),
            TextColumn::make('approve_status')->label('Status')->badge()
                ->color(fn (string $state): string => match ($state) {
                    'pending' => 'warning',
                    'approved' => 'success',
                    'declined' => 'danger',
                    default => 'gray',
                }),
            TextColumn::make('created_at')->dateTime()->label('Created At')->sortable()->toggleable(),
        ];

        $actions = [];
        $bulkActions = [];

        if ($panelId === 'owner' || $panelId === 'admin') {
            $columns[] = TextColumn::make('user.name')->label('Dibuat Oleh')->searchable();
            $actions = [
                // Add actions if needed
            ];

            $bulkActions = [
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
            ];
        }

        return $table
            ->columns($columns)
            ->filters([
                SelectFilter::make('approve_status')
                    ->label('Approve Status')
                    ->options([
                        'approved' => 'Approved',
                        'pending' => 'Pending',
                        'declined' => 'Declined',
                    ]),
            ])
            ->actions($actions)
            ->bulkActions($bulkActions)
            ->defaultSort('created_at', 'desc');
    }


    public static function getRelations(): array
    {
        return [
            // Add relations if necessary
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
