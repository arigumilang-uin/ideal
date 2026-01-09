<?php

namespace App\Policies;

use App\Models\User;

/**
 * User Policy
 * 
 * Authorization logic untuk operasi CRUD User.
 * Defines who can view, create, update, and delete user accounts.
 * 
 * @package App\Policies
 */
class UserPolicy
{
    /**
     * Determine if the user can view any users.
     * 
     * Only Operator Sekolah and Kepala Sekolah can view user list.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Operator Sekolah', 'Kepala Sekolah']);
    }

    /**
     * Determine if the user can view the target user.
     */
    public function view(User $user, User $model): bool
    {
        // Users can always view themselves
        if ($user->id === $model->id) {
            return true;
        }

        // Operator Sekolah and Kepala Sekolah can view all users
        return $user->hasAnyRole(['Operator Sekolah', 'Kepala Sekolah']);
    }

    /**
     * Determine if the user can create users.
     * 
     * Only Operator Sekolah can create users.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('Operator Sekolah');
    }

    /**
     * Determine if the user can update the target user.
     * 
     * - Users can update their own profile (limited fields)
     * - Operator Sekolah can update all users
     */
    public function update(User $user, User $model): bool
    {
        // Users can update themselves (own profile)
        if ($user->id === $model->id) {
            return true;
        }

        // Operator Sekolah can update all users
        return $user->hasRole('Operator Sekolah');
    }

    /**
     * Determine if the user can delete the target user.
     * 
     * Only Operator Sekolah can delete users.
     * Users cannot delete themselves.
     */
    public function delete(User $user, User $model): bool
    {
        // Cannot delete yourself
        if ($user->id === $model->id) {
            return false;
        }

        return $user->hasRole('Operator Sekolah');
    }

    /**
     * Determine if the user can reset password for target user.
     */
    public function resetPassword(User $user, User $model): bool
    {
        return $user->hasRole('Operator Sekolah');
    }

    /**
     * Determine if the user can toggle activation status.
     */
    public function toggleActivation(User $user, User $model): bool
    {
        // Cannot deactivate yourself
        if ($user->id === $model->id) {
            return false;
        }

        return $user->hasRole('Operator Sekolah');
    }

    /**
     * Determine if the user can assign roles.
     */
    public function assignRole(User $user): bool
    {
        return $user->hasRole('Operator Sekolah');
    }

    /**
     * Determine if the user can bulk activate/deactivate users.
     */
    public function bulkActivate(User $user): bool
    {
        return $user->hasRole('Operator Sekolah');
    }

    /**
     * Determine if the user can see audit logs.
     */
    public function viewAuditLogs(User $user): bool
    {
        return $user->hasAnyRole(['Operator Sekolah', 'Kepala Sekolah']);
    }
}
