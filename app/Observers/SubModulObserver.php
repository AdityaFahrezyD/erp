<?php

namespace App\Observers;

use App\Models\SubModul;
use App\Models\SubModulDependencies;
use Illuminate\Support\Facades\Log;

class SubModulObserver
{
    public function saving(SubModul $subModul): void
    {
        // Hitung expected time dan variance
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
        // Hitung ulang critical path seluruh modul terkait
        $this->recalculateCriticalPath($subModul->modul_id);
    }

    public function deleted(SubModul $subModul): void
    {
        // Hitung ulang critical path ketika sub modul dihapus
        $this->recalculateCriticalPath($subModul->modul_id);
    }

    protected function recalculateCriticalPath(string $modulId): void
    {
        try {
            // Ambil semua submodul dalam modul ini
            $submoduls = SubModul::where('modul_id', $modulId)->get()->keyBy('id');

            if ($submoduls->isEmpty()) {
                Log::info("No submodules found for module: {$modulId}");
                return;
            }

            Log::info("Recalculating critical path for module: {$modulId} with " . $submoduls->count() . " submodules");

            // Siapkan struktur graf
            $dependencies = [];
            $successors = [];

            foreach ($submoduls as $submodul) {
                $dependsOn = SubModulDependencies::where('sub_modul_id', $submodul->id)
                    ->pluck('depends_on_sub_modul_id')->toArray();
                $dependencies[$submodul->id] = $dependsOn;

                // Build successors (reverse dependencies)
                foreach ($dependsOn as $dep) {
                    if (!isset($successors[$dep])) {
                        $successors[$dep] = [];
                    }
                    $successors[$dep][] = $submodul->id;
                }

                Log::info("SubModul {$submodul->nama_sub_modul} ({$submodul->id}) depends on: " . implode(', ', $dependsOn));
            }

            // Initialize successors for nodes without any
            foreach ($submoduls->keys() as $id) {
                if (!isset($successors[$id])) {
                    $successors[$id] = [];
                }
            }

            // === FORWARD PASS - Calculate EST and EFT ===
            $est = [];
            $eft = [];
            $calculating = []; // Track nodes being calculated to detect cycles

            $calculateEST = function ($id) use (&$calculateEST, &$dependencies, &$submoduls, &$est, &$calculating) {
                if (isset($est[$id])) {
                    return $est[$id];
                }

                if (in_array($id, $calculating)) {
                    Log::warning("Circular dependency detected at node: {$id}");
                    return $est[$id] = 0;
                }

                $calculating[] = $id;

                $maxEST = 0;
                foreach ($dependencies[$id] ?? [] as $depId) {
                    if (isset($submoduls[$depId])) {
                        $depEST = $calculateEST($depId);
                        $depDuration = $submoduls[$depId]->expected_time ?? 0;
                        $maxEST = max($maxEST, $depEST + $depDuration);
                    }
                }

                $calculating = array_diff($calculating, [$id]);
                return $est[$id] = $maxEST;
            };

            // Calculate EST for all nodes
            foreach ($submoduls->keys() as $id) {
                $calculateEST($id);
            }

            // Calculate EFT
            foreach ($submoduls as $id => $submodul) {
                $eft[$id] = $est[$id] + ($submodul->expected_time ?? 0);
            }

            // Find project completion time (maximum EFT)
            $projectCompletionTime = max($eft);
            Log::info("Project completion time: {$projectCompletionTime}");

            // === BACKWARD PASS - Calculate LFT and LST ===
            $lft = [];
            $lst = [];
            $calculating = [];

            // Find end nodes (nodes with no successors)
            $endNodes = [];
            foreach ($submoduls->keys() as $id) {
                if (empty($successors[$id])) {
                    $endNodes[] = $id;
                    $lft[$id] = $eft[$id]; // LFT for end nodes equals their EFT
                }
            }

            // If no clear end nodes, use nodes with maximum EFT
            if (empty($endNodes)) {
                $maxEFT = max($eft);
                foreach ($submoduls->keys() as $id) {
                    if (abs($eft[$id] - $maxEFT) < 0.001) {
                        $endNodes[] = $id;
                        $lft[$id] = $maxEFT; // Set LFT to project completion time
                    }
                }
            }

            Log::info("End nodes: " . implode(', ', $endNodes));
            Log::info("Project completion time (max EFT): " . max($eft));

            $calculateLFT = function ($id) use (&$calculateLFT, &$successors, &$submoduls, &$lft, &$calculating, $projectCompletionTime) {
                if (isset($lft[$id])) {
                    return $lft[$id];
                }

                if (in_array($id, $calculating)) {
                    Log::warning("Circular dependency detected in backward pass at node: {$id}");
                    return $lft[$id] = $projectCompletionTime;
                }

                $calculating[] = $id;

                // If this node has no successors, it's an end node
                if (empty($successors[$id])) {
                    $calculating = array_diff($calculating, [$id]);
                    return $lft[$id] = $eft[$id];
                }

                $minLFT = PHP_FLOAT_MAX;
                foreach ($successors[$id] as $succId) {
                    if (isset($submoduls[$succId])) {
                        $succLFT = $calculateLFT($succId);
                        $succDuration = $submoduls[$succId]->expected_time ?? 0;
                        // LFT of current node = min(LST of all successors)
                        $succLST = $succLFT - $succDuration;
                        $minLFT = min($minLFT, $succLST);
                    }
                }

                $calculating = array_diff($calculating, [$id]);
                
                if ($minLFT === PHP_FLOAT_MAX) {
                    // Fallback: treat as end node
                    return $lft[$id] = $eft[$id];
                }

                return $lft[$id] = $minLFT + ($submoduls[$id]->expected_time ?? 0);
            };

            // Calculate LFT for all nodes
            foreach ($submoduls->keys() as $id) {
                $calculateLFT($id);
            }

            // Calculate LST from LFT
            foreach ($submoduls->keys() as $id) {
                $lst[$id] = $lft[$id] - ($submoduls[$id]->expected_time ?? 0);
            }

            // === Calculate Total Float ===
            $totalFloat = [];
            $criticalNodes = [];

            Log::info("=== PERT Calculation Results ===");
            foreach ($submoduls->keys() as $id) {
                $totalFloat[$id] = $lst[$id] - $est[$id];
                
                $submodulName = $submoduls[$id]->nama_sub_modul;
                $duration = $submoduls[$id]->expected_time ?? 0;
                
                Log::info("SubModul: {$submodulName}");
                Log::info("  Duration: {$duration}");
                Log::info("  EST: {$est[$id]}, EFT: {$eft[$id]}");
                Log::info("  LST: {$lst[$id]}, LFT: {$lft[$id]}");
                Log::info("  Total Float: {$totalFloat[$id]}");
                Log::info("  Dependencies: " . implode(', ', $dependencies[$id] ?? []));
                Log::info("  Successors: " . implode(', ', $successors[$id] ?? []));
                
                // Critical path nodes have zero or near-zero total float
                if (abs($totalFloat[$id]) < 0.001) {
                    $criticalNodes[] = $id;
                    Log::info("  *** CRITICAL PATH ***");
                }
                Log::info("---");
            }

            Log::info("Critical nodes found: " . count($criticalNodes));
            Log::info("Critical node IDs: " . implode(', ', $criticalNodes));

            // Validation: If all nodes are critical, there might be an error
            if (count($criticalNodes) === $submoduls->count() && $submoduls->count() > 1) {
                Log::warning("WARNING: All sub modules are marked as critical path. This might indicate a calculation error.");
                Log::info("This usually happens when:");
                Log::info("1. All sub modules are independent (no dependencies)");
                Log::info("2. There's an error in dependency setup");
                Log::info("3. All sub modules have the same duration and are sequential");
                
                // Additional check: if no dependencies exist, only the longest duration should be critical
                $allIndependent = true;
                foreach ($dependencies as $deps) {
                    if (!empty($deps)) {
                        $allIndependent = false;
                        break;
                    }
                }
                
                if ($allIndependent) {
                    Log::info("All sub modules are independent. Only longest duration should be critical.");
                    $criticalNodes = [];
                    $maxDuration = max(array_map(fn($sm) => $sm->expected_time ?? 0, $submoduls->toArray()));
                    foreach ($submoduls as $id => $submodul) {
                        if (abs(($submodul->expected_time ?? 0) - $maxDuration) < 0.001) {
                            $criticalNodes[] = $id;
                        }
                    }
                    Log::info("Corrected critical nodes for independent tasks: " . implode(', ', $criticalNodes));
                }
            }

            // === Update Database ===
            foreach ($submoduls as $id => $submodul) {
                $submodul->est = round($est[$id], 2);
                $submodul->eft = round($eft[$id], 2);
                $submodul->lst = round($lst[$id], 2);
                $submodul->lft = round($lft[$id], 2);
                $submodul->total_float = round($totalFloat[$id], 2);
                $submodul->is_critical_path = in_array($id, $criticalNodes);
                
                $submodul->saveQuietly(); // Save without triggering observers
            }

            Log::info("Critical path calculation completed for module: {$modulId}");

        } catch (\Exception $e) {
            Log::error("Error calculating critical path for module {$modulId}: " . $e->getMessage());
            Log::error($e->getTraceAsString());
        }
    }
}