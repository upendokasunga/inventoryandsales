<?php

namespace App\Contracts;

interface Approvable
{
    public function getApprovalConfigKey(): string;

    public function getCurrentStatus(): string;

    public function setApprovalStatus(string $status, ?array $extra = []): void;

    public function getAllowedApprovalTransitions(): array;

    public function onSubmitting(): void;

    public function onApproved(): void;

    public function onRejected(): void;

    public function onCancelled(): void;

    public function getApprovedStatus(): string;
}
