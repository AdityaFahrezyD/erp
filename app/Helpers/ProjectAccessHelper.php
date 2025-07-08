<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;
use App\Models\Pegawai;
use App\Models\GoingProject;

class ProjectAccessHelper
{
    public static function isAdmin(): bool
    {
        return Auth::check() && Auth::user()->role === 'admin';
    }

    public static function isProjectLeader($project): bool
    {
        $user = Auth::user();
        if (!$user || !$project) return false;

        $leader = Pegawai::find($project->project_leader);
        return $leader && $leader->email === $user->email;
    }


    public static function isProjectStaff(): bool
    {
        $user = Auth::user();
        if (!$user) return false;

        return Pegawai::where('email', $user->email)->exists();
    }

    public static function pegawaiId(): ?string
    {
        return Pegawai::where('email', Auth::user()->email)->value('pegawai_id');
    }
}

