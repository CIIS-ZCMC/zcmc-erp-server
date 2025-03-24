<?php

namespace App\Observers;

use App\Helpers\TransactionLogHelper;
use App\Models\Objective;

class ObjectiveObserver
{
    /**
     * Handle the Objective "created" event.
     */
    public function created(Objective $objective): void
    {
        TransactionLogHelper::register($objective, 'O-POST');
    }

    /**
     * Handle the Objective "updated" event.
     */
    public function updated(Objective $objective): void
    {
        TransactionLogHelper::register($objective, 'O-PUT');
    }

    /**
     * Handle the Objective "deleted" event.
     */
    public function deleted(Objective $objective): void
    {
        TransactionLogHelper::register($objective, 'O-DELETE');
    }

    /**
     * Handle the Objective "restored" event.
     */
    public function restored(Objective $objective): void
    {
        //
    }

    /**
     * Handle the Objective "force deleted" event.
     */
    public function forceDeleted(Objective $objective): void
    {
        //
    }
}
