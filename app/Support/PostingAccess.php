<?php

namespace App\Support;

use App\Helpers\AccountingHelper;
use App\Models\Account;
use App\Models\User;

class PostingAccess
{
    /**
     * Check if the user's group allows posting to bank accounts.
     */
    public static function groupAllowsBank(User $user): bool
    {
        return $user->groups()->where('allow_post_bank', true)->exists();
    }

    /**
     * Check if a user can post to a specific account.
     *
     * Cash accounts are restricted to the assigned user_id.
     * Bank accounts require allow_post_bank group permission.
     * Other accounts are unrestricted.
     */
    public static function canPostTo(User $user, Account $account): bool
    {
        if ($account->ifrs_category === 'cash' || strtolower($account->name) === 'cash') {
            return $account->user_id === $user->id;
        }

        if (AccountingHelper::isCashOrBankAccount($account)) {
            return self::groupAllowsBank($user);
        }

        return true;
    }

    /**
     * Check if a user can post to any of the given account IDs.
     */
    public static function canPostToAccounts(User $user, array $accountIds): bool
    {
        foreach ($accountIds as $accountId) {
            $account = Account::find($accountId);
            if ($account && !self::canPostTo($user, $account)) {
                return false;
            }
        }

        return true;
    }
}
