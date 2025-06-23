<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use App\Models\ProjectStaff;
use App\Models\GoingProject;
use App\Models\ProjectModul;
use App\Models\SubModul;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;


class ProjectLog extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationGroup = 'Project';

    protected static ?string $navigationIcon = 'heroicon-o-view-columns';

    protected static string $view = 'filament.pages.project-log';

    public ?string $selectedProjectId = null;
    public ?string $selectedModulId = null;
    public array $cards = [];

    public function mount()
    {
        $this->cards = [];
    }

    public function getFormSchema(): array
    {
        return [
            Forms\Components\Select::make('selectedProjectId')
                ->label('Pilih Project')
                ->options(GoingProject::pluck('project_name', 'project_id'))
                ->searchable()
                ->reactive()
                ->afterStateUpdated(fn ($state) => $this->selectedModulId = null),

            Forms\Components\Select::make('selectedModulId')
                ->label('Pilih Modul')
                ->options(function () {
                    if (!$this->selectedProjectId) return [];
                    return ProjectModul::where('project_id', $this->selectedProjectId)->pluck('nama_modul', 'id');
                })
                ->reactive()
                ->afterStateUpdated(fn () => $this->loadKanban()),
        ];
    }

    public function loadKanban(): void
    {
        $this->cards = [
            'new' => $this->getCardsByStatus('new'),
            'on progress' => $this->getCardsByStatus('on progress'),
            'ready for test' => $this->getCardsByStatus('ready for test'),
            'done' => $this->getCardsByStatus('done'),
        ];
    }

    protected function getCardsByStatus(string $status): Collection
    {
        return ProjectStaff::with(['pegawai', 'staff.modul']) // pakai relasi pegawai, bukan user
            ->where('status', $status)
            ->whereHas('staff', function ($query) {
                $query->where('modul_id', $this->selectedModulId);
            })
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'modul' => $item->staff->modul->nama_modul ?? '-', 
                    'sub_modul' => $item->staff->nama_sub_modul ?? '-',
                    'staff' => $item->pegawai->nama ?? '-', // pakai relasi pegawai
                    'status' => $item->status,
                ];
            });
    }



    public function updateCardStatus(string $cardId, string $newStatus): void
    {
        $card = ProjectStaff::find($cardId);
        if ($card) {
            $card->update(['status' => $newStatus]);
            $this->loadKanban();
        }
    }
    public function getStatuses(): array
    {
        return [
            'new' => 'New',
            'on progress' => 'On Progress',
            'ready for test' => 'Ready for Test',
            'done' => 'Done',
        ];
    }

}


