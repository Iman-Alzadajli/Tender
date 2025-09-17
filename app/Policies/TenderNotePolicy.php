<?php

namespace App\Policies;

use App\Models\TenderNote;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TenderNotePolicy
{
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TenderNote $tenderNote): bool
    {
        return $user->id === $tenderNote->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TenderNote $tenderNote): bool
    {
        return $user->id === $tenderNote->user_id;
    }
}
