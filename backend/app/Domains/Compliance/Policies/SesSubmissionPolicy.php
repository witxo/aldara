<?php

namespace App\Domains\Compliance\Policies;

use App\Models\User;
use App\Domains\Compliance\Models\SesSubmission;

class SesSubmissionPolicy
{
    public function view(User $user, SesSubmission $submission): bool
    {
        return $user->is_superadmin || $user->tenants()->where('tenant_id', $submission->tenant_id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->is_superadmin || $user->hasPermission('ses.create');
    }

    public function update(User $user, SesSubmission $submission): bool
    {
        return $user->is_superadmin || $user->hasPermission('ses.update', $submission->tenant_id);
    }

    public function delete(User $user, SesSubmission $submission): bool
    {
        return $user->is_superadmin || $user->hasPermission('ses.delete', $submission->tenant_id);
    }
}
