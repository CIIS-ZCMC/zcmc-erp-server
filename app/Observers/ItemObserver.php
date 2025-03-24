<?php

namespace App\Observers;

use App\Helpers\TransactionLogHelper;
use App\Models\Item;

class ItemObserver
{
    /**
     * Handle the Item "created" event.
     */
    public function created(Item $item): void
    {
        TransactionLogHelper::register($item, "I-POST");
    }

    /**
     * Handle the Item "updated" event.
     */
    public function updated(Item $item): void
    {
        TransactionLogHelper::register($item, "I-PUT");
    }

    /**
     * Handle the Item "deleted" event.
     */
    public function deleted(Item $item): void
    {
        TransactionLogHelper::register($item, "I-DELETE");
    }

    /**
     * Handle the Item "restored" event.
     */
    public function restored(Item $item): void
    {
        //
    }

    /**
     * Handle the Item "force deleted" event.
     */
    public function forceDeleted(Item $item): void
    {
        //
    }
}
