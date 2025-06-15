<?php

namespace App\Filament\Resources\GoingProjectResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\RelationManagers\Concerns\CanViewRecords;
use Filament\Tables;
use Filament\Forms;
use Illuminate\Database\Eloquent\Builder;
use App\Models\GoingProject;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Facades\Filament;



class ModulesRelationManager extends RelationManager
{
    protected static string $relationship = 'modules';

    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('nama_modul')->required(),
            Textarea::make('deskripsi_modul')->nullable(),
            TextInput::make('alokasi_dana')
                ->numeric()
                ->required()
                ->live(onBlur: true),
            TextInput::make('unpaid_amount')
                ->label('Sisa Tagihan')
                ->numeric()
                ->disabled()
                ->dehydrated(false)
        ]);
    }


    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_modul'),
                TextColumn::make('deskripsi_modul'),
                TextColumn::make('alokasi_dana')->money('IDR'),
                TextColumn::make('unpaid_amount')->money('IDR')->label('Sisa Tagihan'),
                TextColumn::make('created_at')->dateTime(),
            ])
            ->filters([])
            ->actions([
                
                EditAction::make()
                    ->after(function ($record) {
                        $approvedInvoicesTotal = $record->invoices()
                            ->where('approve_status', 'approved')
                            ->sum('invoice_amount');

                        $record->update([
                            'unpaid_amount' => max($record->alokasi_dana - $approvedInvoicesTotal, 0),
                        ]);

                        $this->updateProjectUnpaidAmount();
                    }),

                DeleteAction::make()
                    ->after(function () {
                        $this->updateProjectTotal();
                        $this->updateProjectUnpaidAmount();
                    }),
                Tables\Actions\Action::make('invoice')
                    ->label('Invoice')
                    ->icon('heroicon-m-credit-card')
                    ->url(fn ($record) => route('filament.' . Filament::getCurrentPanel()->getId() . '.resources.invoices.create', [
                        'project_id' => $record->project_id,
                        'modul_id' => $record->id,
                        'company' => $record->project->company, 
                        'recipient' => $record->project->pic,
                        'recipient_email' => $record->project->pic_email,
                    ]))
                    ->color('success')

            ])
            ->headerActions([
                CreateAction::make()
                    ->after(function ($record) {
                        $record->update([
                            'unpaid_amount' => $record->alokasi_dana ?? 0,
                        ]);
                        $this->updateProjectTotal();
                        $this->updateProjectUnpaidAmount();
                    }),
            ]);
    }

    protected function afterCreate(): void
    {
        $this->updateProjectTotal();
        $this->updateProjectUnpaidAmount();
    }

    protected function afterDelete(): void
    {
        $this->updateProjectTotal();
        $this->updateProjectUnpaidAmount();
    }

    protected function afterSave(): void
    {
        $this->updateProjectTotal();
        $this->updateProjectUnpaidAmount();
    }


    private function updateProjectTotal(): void
    {
        $project = $this->ownerRecord;

        $total = \App\Models\ProjectModul::where('project_id', $project->project_id)->sum('alokasi_dana');

        $project->update([
            'total_harga_proyek' => $total,
        ]);
    }

    public function updateUnpaidAmount()
    {
        foreach ($modules as $modul) {
            $modul->updateUnpaidAmount(); // Ini panggil fungsi dari model
        }
    }

    private function updateProjectUnpaidAmount(): void
    {
        $project = $this->ownerRecord;

        $modules = $project->modules; // relasi
        foreach ($modules as $modul) {
            $modul->updateUnpaidAmount();
        }

        
        $totalUnpaid = $modules->sum('unpaid_amount');

        $project->update([
            'unpaid_amount' => $totalUnpaid,
        ]);
    }

}
