<?php

namespace App\Traits;

trait HasApprovalWorkflow
{
    protected array $approvalTransitions = [
        'pending_approval' => ['approved', 'cancelled'],
        'approved' => ['cancelled'],
        'cancelled' => [],
    ];

    public function getApprovalConfigKey(): string
    {
        return 'default';
    }

    public function getCurrentStatus(): string
    {
        return $this->status;
    }

    public function getAllowedApprovalTransitions(): array
    {
        return $this->approvalTransitions;
    }

    public function setApprovalStatus(string $status, ?array $extra = []): void
    {
        $this->update(array_merge(['status' => $status], $extra ?? []));
    }

    public function onSubmitting(): void {}

    public function onApproved(): void {}

    public function onRejected(): void {}

    public function onCancelled(): void {}

    public function getApprovedStatus(): string
    {
        return 'approved';
    }
}
