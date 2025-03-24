<?php

namespace App\Observers;

use App\Helpers\TransactionLogHelper;
use App\Models\SuccessIndicator;

class SuccessIndicatorObserver
{
    /**
     * Handle the SuccessIndicator "created" event.
     */
    public function created(SuccessIndicator $successIndicator): void
    {
        TransactionLogHelper::register($successIndicator, 'SI-POST');
    }

    /**
     * Handle the SuccessIndicator "updated" event.
     */
    public function updated(SuccessIndicator $successIndicator): void
    {
        TransactionLogHelper::register($successIndicator, 'SI-PUT');
    }

    /**
     * Handle the SuccessIndicator "deleted" event.
     */
    public function deleted(SuccessIndicator $successIndicator): void
    {
        TransactionLogHelper::register($successIndicator, 'SI-DELETE');
    }

    /**
     * Handle the SuccessIndicator "restored" event.
     */
    public function restored(SuccessIndicator $successIndicator): void
    {
        //
    }

    /**
     * Handle the SuccessIndicator "force deleted" event.
     */
    public function forceDeleted(SuccessIndicator $successIndicator): void
    {
        //
    }
}
