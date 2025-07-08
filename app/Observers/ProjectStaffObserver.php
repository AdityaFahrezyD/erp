<?php

namespace App\Observers;

use App\Models\ProjectStaff;
use App\Models\SubModul;
use App\Models\ProjectModul;

class ProjectStaffObserver
{
    public function saved(ProjectStaff $staff)
    {
        $this->updateSubModulStatus($staff->sub_modul_id);
    }

    public function deleted(ProjectStaff $staff)
    {
        $this->updateSubModulStatus($staff->sub_modul_id);
    }

    protected function updateSubModulStatus($subModulId)
    {
        $subModul = SubModul::with('staff')->find($subModulId);

        if (!$subModul) return;

        $staff = $subModul->staff;
        $staffCount = $staff->count();

        $doneCount = $staff->where('status', 'done')->count();
        $inProgressCount = $staff->whereIn('status', ['on progress', 'ready for test'])->count();

        if ($staffCount > 0 && $doneCount === $staffCount) {
            $subModul->status = 'done';
        } elseif ($inProgressCount > 0 || $doneCount > 0) {
            $subModul->status = 'on progress';
        } else {
            $subModul->status = 'new';
        }

        $subModul->save();

        $this->updateProjectModulStatus($subModul->modul_id);
    }

    protected function updateProjectModulStatus($modulId)
    {
        $modul = ProjectModul::with('sub_modul')->find($modulId);

        if (!$modul) return;

        $subModuls = $modul->sub_modul;
        $total = $subModuls->count();
        $done = $subModuls->where('status', 'done')->count();
        $inProgress = $subModuls->whereIn('status', ['on progress', 'ready for test'])->count();

        if ($total > 0 && $done === $total) {
            $modul->status = 'done';
        } elseif ($inProgress > 0 || $done > 0) {
            $modul->status = 'on progress';
        } else {
            $modul->status = 'new';
        }

        $modul->save();
    }
}


