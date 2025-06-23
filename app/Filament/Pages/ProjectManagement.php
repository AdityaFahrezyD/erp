<?php

namespace App\Filament\Pages;

use Livewire\Component;
use App\Models\GoingProject;
use App\Models\SubModul;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Forms;
use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;

class ProjectManagement extends Page implements HasForms
{
    use InteractsWithForms;

    public ?string $selectedProjectId = null;

    protected ?string $maxContentWidth = 'full';

    protected static ?string $navigationGroup = 'Project';

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static string $view = 'filament.pages.project-management';

    public array $calendarData = [];

    public function mount()
    {
        $this->calendarData = [];
    }

    public function getFormSchema(): array
    {
        return [
            Forms\Components\Select::make('selectedProjectId')
                ->label('Pilih Project')
                ->options(GoingProject::pluck('project_name', 'project_id'))
                ->searchable()
                ->reactive()
                ->afterStateUpdated(fn ($state) => $this->loadCalendar($state))
                ->required(),
        ];
    }
    public function loadCalendar($projectId): void
    {
        $project = GoingProject::find($projectId);

        if (!$project || !$project->batas_awal || !$project->batas_akhir) {
            $this->calendarData = [];
            return;
        }

        $start = Carbon::parse($project->batas_awal);
        $end = Carbon::parse($project->batas_akhir);


        $subModuls = SubModul::with('modul')
            ->whereHas('modul', function ($q) use ($projectId) {
                $q->where('project_id', $projectId);
            })->get();


        $notes = [];
        foreach ($subModuls as $sub) {
            if ($sub->batas_awal && $sub->batas_akhir && $sub->modul) {
                $modulName = $sub->modul->nama_modul;
                $period = CarbonPeriod::create($sub->batas_awal, $sub->batas_akhir);

                foreach ($period as $date) {
                    $dateKey = $date->toDateString();
                    $notes[$dateKey][$modulName][] = $sub->nama_sub_modul;
                }
            }
        }


        $days = [];
        $iter = $start->copy();
        while ($iter <= $end) {
            $date = $iter->copy();
            $monthName = $date->format('F Y');

            $days[$monthName][] = [
                'date' => $date->toDateString(),
                'day' => $date->day,
                'notes' => $notes[$date->toDateString()] ?? [],
            ];

            $iter->addDay();
        }

        $this->calendarData = $days;
    }

}
