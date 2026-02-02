<?php

namespace App\Services\Routers;

use App\Models\Router;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RouterCleanupService
{
    public function findDuplicates(): Collection
    {
        // Duplicates by identity (ignoring blanks)
        $dupeIdentities = Router::query()
            ->select('identity', DB::raw('COUNT(*) as c'))
            ->whereNotNull('identity')
            ->where('identity', '!=', '')
            ->groupBy('identity')
            ->having('c', '>', 1)
            ->pluck('identity');

        if ($dupeIdentities->isEmpty()) {
            return collect();
        }

        // Load all routers for those identities
        $routers = Router::query()
            ->whereIn('identity', $dupeIdentities)
            ->orderBy('identity')
            ->orderByDesc('last_seen_at')  // keep the most recently seen
            ->orderByDesc('updated_at')
            ->get();

        // Group by identity and decide keep/delete
        return $routers->groupBy('identity')->map(function ($group) {
            // Prefer keep: connected OR has last_seen_at OR newest updated
            $keep = $group->firstWhere('status', 'connected')
                ?? $group->firstWhere(fn ($r) => !is_null($r->last_seen_at))
                ?? $group->sortByDesc('updated_at')->first();

            $candidates = $group->filter(fn ($r) => $r->id !== $keep->id);

            // Only delete SAFE candidates: never seen + not connected
            $toDelete = $candidates->filter(function ($r) {
                return $r->status !== 'connected' && is_null($r->last_seen_at);
            })->values();

            return [
                'identity' => $group->first()->identity,
                'keep' => $keep,
                'delete' => $toDelete,
                'all' => $group->values(),
            ];
        })->values();
    }

    public function deleteDuplicates(bool $dryRun = true): array
    {
        $groups = $this->findDuplicates();

        $deleteIds = $groups
            ->flatMap(fn ($g) => $g['delete']->pluck('id'))
            ->unique()
            ->values();

        if ($dryRun) {
            return [
                'dry_run' => true,
                'groups' => $groups,
                'delete_ids' => $deleteIds,
                'deleted_count' => 0,
            ];
        }

        $deleted = 0;

        DB::transaction(function () use ($deleteIds, &$deleted) {
            // extra safety guard
            $deleted = Router::query()
                ->whereIn('id', $deleteIds)
                ->where('status', '!=', 'connected')
                ->whereNull('last_seen_at')
                ->delete();
        });

        return [
            'dry_run' => false,
            'groups' => $groups,
            'delete_ids' => $deleteIds,
            'deleted_count' => $deleted,
        ];
    }
}
