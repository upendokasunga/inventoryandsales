<?php

namespace App\Services;

use App\Contracts\Approvable;
use App\Models\ApprovalConfiguration;
use App\Models\ApprovalTracking;
use App\Models\ApprovalTrackingLog;
use Illuminate\Support\Facades\Cache;

class CentralApprovalService
{
    public function submit(Approvable $model): void
    {
        $transitions = $model->getAllowedApprovalTransitions();
        $currentStatus = $model->getCurrentStatus();

        if (!in_array('pending_approval', $transitions[$currentStatus] ?? [])) {
            throw new \InvalidArgumentException(
                "Cannot submit '{$currentStatus}' for approval."
            );
        }

        $model->onSubmitting();
        $model->setApprovalStatus('pending_approval');
        $this->createTracking($model);
    }

    public function approve(Approvable $model, ?string $comments = null): void
    {
        $tracking = $this->getTracking($model);

        if (!$tracking) {
            if ($model->getCurrentStatus() === 'pending_approval') {
                $tracking = $this->createTracking($model);
            } else {
                throw new \InvalidArgumentException('Document is not pending approval.');
            }
        }

        if ($tracking->isFullyApproved()) {
            throw new \InvalidArgumentException('Document is already fully approved.');
        }

        $this->checkLevelPermission($tracking);

        $tracking->advanceLevel();

        $this->logAction($tracking, 'approved', $comments);

        if ($tracking->isFullyApproved()) {
            $model->onApproved();
            $model->setApprovalStatus($model->getApprovedStatus(), [
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
        }
    }

    public function reject(Approvable $model, ?string $comments = null): void
    {
        $tracking = $this->getTracking($model);

        if (!$tracking) {
            if ($model->getCurrentStatus() === 'pending_approval') {
                $tracking = $this->createTracking($model);
            } else {
                throw new \InvalidArgumentException('Document is not pending approval.');
            }
        }

        if (!$tracking->isPending()) {
            throw new \InvalidArgumentException('Document is not pending approval.');
        }

        $this->checkLevelPermission($tracking);

        $tracking->update([
            'status' => 'rejected',
            'completed_at' => now(),
            'completed_by' => auth()->id(),
        ]);

        $this->logAction($tracking, 'rejected', $comments);

        $model->onRejected();
        $model->setApprovalStatus('draft');
    }

    public function cancel(Approvable $model, ?string $comments = null): void
    {
        $currentStatus = $model->getCurrentStatus();

        $transitions = $model->getAllowedApprovalTransitions();
        $allowed = $transitions[$currentStatus] ?? [];
        if (!in_array('cancelled', $allowed)) {
            throw new \InvalidArgumentException(
                "Cannot cancel document in status '{$currentStatus}'."
            );
        }

        $tracking = $this->getTracking($model);
        if ($tracking) {
            $tracking->update(['status' => 'cancelled']);
            $this->logAction($tracking, 'cancelled', $comments);
        }

        $model->onCancelled();
        $model->setApprovalStatus('cancelled');
    }

    public function createTracking(Approvable $model): ?ApprovalTracking
    {
        $config = ApprovalConfiguration::byModule($model->getApprovalConfigKey())->first();

        if (!$config || !$config->requiresApproval()) {
            $tracking = $this->getTracking($model);
            if (!$tracking) {
                $tracking = ApprovalTracking::create([
                    'approvable_type' => get_class($model),
                    'approvable_id' => $model->getKey(),
                    'approval_configuration_id' => null,
                    'current_level' => 0,
                    'required_levels' => 0,
                    'status' => 'approved',
                    'submitted_at' => now(),
                    'submitted_by' => auth()->id(),
                    'completed_at' => now(),
                    'completed_by' => auth()->id(),
                ]);
                $this->logAction($tracking, 'approved', 'No approval required (auto-approved)');
                $model->setApprovalStatus($model->getApprovedStatus(), [
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                ]);
            }
            return $tracking;
        }

        $tracking = ApprovalTracking::create([
            'approvable_type' => get_class($model),
            'approvable_id' => $model->getKey(),
            'approval_configuration_id' => $config->id,
            'current_level' => 0,
            'required_levels' => $config->approval_level,
            'status' => 'pending',
            'submitted_at' => now(),
            'submitted_by' => auth()->id(),
        ]);

        $this->logAction($tracking, 'submitted');

        return $tracking;
    }

    public function getTracking(Approvable $model): ?ApprovalTracking
    {
        return ApprovalTracking::where('approvable_type', get_class($model))
            ->where('approvable_id', $model->getKey())
            ->latest()
            ->first();
    }

    protected function checkLevelPermission(ApprovalTracking $tracking): void
    {
        if (!$tracking->configuration) {
            return;
        }

        $user = auth()->user();

        if (!$user) {
            throw new \InvalidArgumentException('You must be logged in.');
        }

        // Super admins can approve any level
        if ($user->groups()->where('is_super_admin', true)->exists()) {
            return;
        }

        $nextLevel = $tracking->current_level + 1;
        $level = $tracking->configuration->levels()
            ->where('level', $nextLevel)
            ->first();

        if (!$level) {
            return;
        }

        if (!$user->groups()->where('group_id', $level->group_id)->exists()) {
            throw new \InvalidArgumentException(
                'You do not have permission to approve at level ' . $nextLevel . '.'
            );
        }
    }

    protected function logAction(ApprovalTracking $tracking, string $action, ?string $comments = null): void
    {
        ApprovalTrackingLog::create([
            'approval_tracking_id' => $tracking->id,
            'level' => $tracking->current_level,
            'action' => $action,
            'user_id' => auth()->id(),
            'comments' => $comments,
        ]);

        Cache::forget('approval.tracking.' . $tracking->id);
    }
}
