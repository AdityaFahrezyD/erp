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
use Illuminate\Support\Facades\Auth;

class ProjectManagement extends Page implements HasForms
{
    use InteractsWithForms;

    public ?string $projectProgressStatus = null;

    public ?string $selectedProjectId = null;

    protected ?string $maxContentWidth = 'full';

    protected static ?string $navigationGroup = 'Project';

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static string $view = 'filament.pages.project-management';

    public string $projectEndDate;

    public int $overallProgress = 0;

    public array $calendarData = [];

    public array $progressPercentages = [
        'staff' => 0,
        'submodul' => 0,
        'modul' => 0,
    ];

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
        $this->projectEndDate = $project->batas_akhir;
        $this->projectProgressStatus = $this->calculateProjectProgressStatus($projectId);
        $this->progressPercentages = $this->calculateProgressPercentages($projectId);
        $this->overallProgress = $this->calculateOverallProgress($projectId);

        $start = Carbon::parse($project->batas_awal);

        $subModuls = SubModul::with('modul')
            ->whereHas('modul', function ($q) use ($projectId) {
                $q->where('project_id', $projectId);
            })->get();
            
        $projectEnd = Carbon::parse($project->batas_akhir);
        $latestSubEnd = $subModuls->max(function ($sub) {
            return Carbon::parse($sub->batas_akhir);
        });
        $end = $latestSubEnd->greaterThan($projectEnd) ? $latestSubEnd : $projectEnd;

        $notes = [];
        foreach ($subModuls as $sub) {
            if ($sub->batas_awal && $sub->batas_akhir && $sub->modul) {
                $modulName = $sub->modul->nama_modul;
                $period = CarbonPeriod::create($sub->batas_awal, $sub->batas_akhir);

                foreach ($period as $date) {
                    $dateKey = $date->toDateString();
                    $projectEnd = Carbon::parse($project->batas_akhir);
                    $isLateDate = $date->gt($projectEnd);

                    $notes[$dateKey][$modulName][] = [
                        'name' => $sub->nama_sub_modul,
                        'isLate' => $isLateDate,
                    ];

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

    protected function calculateProjectProgressStatus(string $projectId): string
    {
        $moduls = \App\Models\ProjectModul::where('project_id', $projectId)->pluck('id');

        
        $subModuls = \App\Models\SubModul::whereIn('modul_id', $moduls)->get();

        if ($subModuls->isEmpty()) {
            return 'new';
        }

        $done = $subModuls->where('status', 'done')->count();
        $onProgress = $subModuls->where('status', 'on progress')->count();

        if ($done === $subModuls->count()) {
            return 'done';
        } elseif ($done > 0 || $onProgress > 0) {
            return 'on progress';
        } else {
            return 'new';
        }
    }
    protected function calculateProgressPercentages(string $projectId): array
    {
        $moduls = \App\Models\ProjectModul::where('project_id', $projectId)->pluck('id');

        $subModuls = \App\Models\SubModul::whereIn('modul_id', $moduls)->pluck('id');

        // Project Staff
        $totalStaff = \App\Models\ProjectStaff::whereIn('sub_modul_id', $subModuls)->count();
        $doneStaff = \App\Models\ProjectStaff::whereIn('sub_modul_id', $subModuls)->where('status', 'done')->count();
        $staffProgress = $totalStaff > 0 ? round(($doneStaff / $totalStaff) * 100) : 0;

        // Sub Modul
        $totalSub = \App\Models\SubModul::whereIn('modul_id', $moduls)->count();
        $doneSub = \App\Models\SubModul::whereIn('modul_id', $moduls)->where('status', 'done')->count();
        $subProgress = $totalSub > 0 ? round(($doneSub / $totalSub) * 100) : 0;

        // Modul
        $totalModul = \App\Models\ProjectModul::where('project_id', $projectId)->count();
        $doneModul = \App\Models\ProjectModul::where('project_id', $projectId)->where('status', 'done')->count();
        $modulProgress = $totalModul > 0 ? round(($doneModul / $totalModul) * 100) : 0;

        return [
            'staff' => $staffProgress,
            'submodul' => $subProgress,
            'modul' => $modulProgress,
        ];
    }
    protected function calculateOverallProgress(string $projectId): int
    {
        // Ambil semua modul dari project
        $moduls = \App\Models\ProjectModul::where('project_id', $projectId)->pluck('id');

        // Ambil semua submodul dari modul
        $subModulIds = \App\Models\SubModul::whereIn('modul_id', $moduls)->pluck('id');

        // Hitung progress staff
        $staffs = \App\Models\ProjectStaff::whereIn('sub_modul_id', $subModulIds)->get();
        $staffScore = $staffs->count() > 0
            ? $staffs->map(fn ($s) => $this->mapStatusToScore($s->status))->avg()
            : null;

        // Hitung progress submodul
        $submoduls = \App\Models\SubModul::whereIn('modul_id', $moduls)->get();
        $submodulScore = $submoduls->count() > 0
            ? $submoduls->map(fn ($s) => $this->mapStatusToScore($s->status))->avg()
            : null;

        // Hitung progress modul
        $modulModels = \App\Models\ProjectModul::where('project_id', $projectId)->get();
        $modulScore = $modulModels->count() > 0
            ? $modulModels->map(fn ($m) => $this->mapStatusToScore($m->status))->avg()
            : null;

        // Rata-rata dari ketiganya (abaikan null)
        $allScores = collect([$staffScore, $submodulScore, $modulScore])->filter();
        return $allScores->count() > 0 ? round($allScores->avg()) : 0;
    }

    protected function mapStatusToScore(string $status): int
    {
        return match ($status) {
            'done' => 100,
            'on progress', 'ready for test' => 50,
            default => 0, // new atau lainnya
        };
    }




}
