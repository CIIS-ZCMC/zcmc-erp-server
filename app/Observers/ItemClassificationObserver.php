<?php

namespace App\Observers;

use App\Helpers\TransactionLogHelper;
use App\Models\ItemClassification;

class ItemClassificationObserver
{
    /**
     * Handle the ItemClassification "created" event.
     */
    public function created(ItemClassification $itemClassification): void
    {
        TransactionLogHelper::register($itemClassification, 'ICLASS-POST');
    }

    /**
     * Handle the ItemClassification "updated" event.
     */
    public function updated(ItemClassification $itemClassification): void
    {
        TransactionLogHelper::register($itemClassification, 'ICLASS-PUT');
    }

    /**
     * Handle the ItemClassification "deleted" event.
     */
    public function deleted(ItemClassification $itemClassification): void
    {
        TransactionLogHelper::register($itemClassification, 'ICLASS-DELETE');
    }

    /**
     * Handle the ItemClassification "restored" event.
     */
    public function restored(ItemClassification $itemClassification): void
    {
        //
    }

    /**
     * Handle the ItemClassification "force deleted" event.
     */
    public function forceDeleted(ItemClassification $itemClassification): void
    {
        //
    }
}
