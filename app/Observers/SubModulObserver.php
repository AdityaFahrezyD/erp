<?php

namespace App\Observers;

use App\Models\SubModul;
use App\Models\SubModulDependencies;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SubModulObserver
{
    public function saving(SubModul $subModul): void
    {
        if ($subModul->optimistic_time && $subModul->most_likely_time && $subModul->pessimistic_time) {
            $O = $subModul->optimistic_time;
            $M = $subModul->most_likely_time;
            $P = $subModul->pessimistic_time;

            $subModul->expected_time = ($O + 4 * $M + $P) / 6;
            $subModul->variance = pow(($P - $O) / 6, 2);
        }
    }



    public function saved(SubModul $subModul): void
    {
        // Recalculate modul yang sedang diubah
        $this->recalculateCriticalPath($subModul->modul_id);

        // Cari modul lain yang tergantung pada submodul ini
        $affectedModulIds = SubModulDependencies::where('depends_on_sub_modul_id', $subModul->id)
            ->pluck('sub_modul_id')
            ->map(fn ($subModulId) => SubModul::find($subModulId)?->modul_id)
            ->filter()
            ->unique();

        foreach ($affectedModulIds as $modulId) {
            $this->recalculateCriticalPath($modulId);
        }
        // Jika batas_awal belum diisi
        if (!$subModul->batas_awal) {
            $maxDependencyDate = SubModulDependencies::where('sub_modul_id', $subModul->id)
                ->join('sub_modul as s', 's.id', '=', 'sub_modul_dependencies.depends_on_sub_modul_id')
                ->max('s.batas_akhir');
                
            Log::info('Dependencies untuk SubModul ID ' . $subModul->id . ':', [
                SubModulDependencies::where('sub_modul_id', $subModul->id)->pluck('depends_on_sub_modul_id')->toArray()
            ]);

            if ($maxDependencyDate) {
                $subModul->batas_awal = $maxDependencyDate;
            }
        }

        // Jika batas_awal sudah ada dan expected_time sudah ada, hitung batas_akhir
        if ($subModul->batas_awal && $subModul->expected_time) {
            $subModul->batas_akhir = Carbon::parse($subModul->batas_awal)
                ->addDays(round((float) $subModul->expected_time))
                ->toDateString();
        }


        // Simpan update tanpa trigger observer lagi
        $subModul->saveQuietly();
    }

    public static function afterCreate(CreateRecord $operation, SubModul $record)
    {
        static::saveDependencies($record, request()->input('dependencies', []));

        // Update tanggal setelah dependencies tersimpan
        app(\App\Observers\SubModulObserver::class)->updateDates($record);
    }


    public function updateDates(SubModul $subModul): void
    {
        if (!$subModul->batas_awal) {
            $maxDependencyDate = SubModulDependencies::where('sub_modul_id', $subModul->id)
                ->join('sub_modul as s', 's.id', '=', 'sub_modul_dependencies.depends_on_sub_modul_id')
                ->max('s.batas_akhir');

            if ($maxDependencyDate) {
                $subModul->batas_awal = $maxDependencyDate;
            }
        }

        if ($subModul->batas_awal && $subModul->expected_time) {
            $subModul->batas_akhir = Carbon::parse($subModul->batas_awal)
                ->addDays(round((float) $subModul->expected_time))
                ->toDateString();
        }

        $subModul->saveQuietly();
    }


    public function deleted(SubModul $subModul): void
    {
        $this->recalculateCriticalPath($subModul->modul_id);
    }

    public function recalculateCriticalPath(string $modulId): void
    {
        $submoduls = SubModul::with('dependencies')->where('modul_id', $modulId)->get()->keyBy('id');
        if ($submoduls->isEmpty()) return;

        [$dependencies, $successors] = $this->buildGraph($submoduls);
        [$est, $eft] = $this->calculateForwardPass($submoduls, $dependencies);
        [$lft, $lst] = $this->calculateBackwardPass($submoduls, $successors, $eft);

        $projectCompletionTime = max($eft);
        $criticalNodes = [];

        foreach ($submoduls as $id => $submodul) {
            $float = $lst[$id] - $est[$id];
            $isCritical = abs($float) < 0.001;

            $submodul->est = round($est[$id], 2);
            $submodul->eft = round($eft[$id], 2);
            $submodul->lst = round($lst[$id], 2);
            $submodul->lft = round($lft[$id], 2);
            $submodul->total_float = round($float, 2);
            $submodul->is_critical_path = $isCritical;

            $submodul->saveQuietly();

            if ($isCritical) {
                $criticalNodes[] = $id;
            }
        }

        Log::info("Critical Path recalculated for modul: {$modulId}, total critical: " . count($criticalNodes));
    }

    private function buildGraph($submoduls): array
    {
        $dependencies = [];
        $successors = [];

        foreach ($submoduls as $submodul) {
            $dependsOn = $submodul->dependencies->pluck('id')->toArray();
            $dependencies[$submodul->id] = $dependsOn;

            foreach ($dependsOn as $dep) {
                $successors[$dep][] = $submodul->id;
            }
        }

        foreach ($submoduls->keys() as $id) {
            $dependencies[$id] = $dependencies[$id] ?? [];
            $successors[$id] = $successors[$id] ?? [];
        }

        return [$dependencies, $successors];
    }

    private function calculateForwardPass($submoduls, $dependencies): array
    {
        $est = [];
        $eft = [];

        $calculate = function ($id) use (&$calculate, &$est, $dependencies, $submoduls) {
            if (isset($est[$id])) return $est[$id];

            $maxEST = 0;
            foreach ($dependencies[$id] ?? [] as $depId) {
                $depEST = $calculate($depId);
                $depDuration = $submoduls[$depId]->expected_time ?? 0;
                $maxEST = max($maxEST, $depEST + $depDuration);
            }

            $est[$id] = $maxEST;
            return $maxEST;
        };

        foreach ($submoduls->keys() as $id) {
            $calculate($id);
            $eft[$id] = $est[$id] + ($submoduls[$id]->expected_time ?? 0);
        }

        return [$est, $eft];
    }

    private function calculateBackwardPass($submoduls, $successors, $eft): array
    {
        $lft = [];
        $lst = [];
        $projectCompletionTime = max($eft);

        // End nodes = tidak punya successor
        $endNodes = array_keys(array_filter($successors, fn($s) => empty($s)));

        foreach ($endNodes as $id) {
            $lft[$id] = $projectCompletionTime;
        }

        $calculate = function ($id) use (&$calculate, &$lft, $successors, $submoduls, $projectCompletionTime) {
            if (isset($lft[$id])) return $lft[$id];

            $minLST = PHP_FLOAT_MAX;
            foreach ($successors[$id] ?? [] as $succId) {
                $succLFT = $calculate($succId);
                $succDuration = $submoduls[$succId]->expected_time ?? 0;
                $succLST = $succLFT - $succDuration;
                $minLST = min($minLST, $succLST);
            }

            return $lft[$id] = ($minLST === PHP_FLOAT_MAX)
                ? $projectCompletionTime
                : $minLST;
        };

        foreach ($submoduls->keys() as $id) {
            $calculate($id);
            $duration = $submoduls[$id]->expected_time ?? 0;
            $lst[$id] = $lft[$id] - $duration;
        }

        return [$lft, $lst];
    }

}
