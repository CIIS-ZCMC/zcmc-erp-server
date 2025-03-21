<?php

namespace App\Observers;

use App\Helpers\TransactionLogHelper;
use App\Models\ItemUnit;
use App\Models\LogDescription;
use App\Models\TransactionLog;

class ItemUnitObserver
{
    
    /**
     * Handle the ItemUnit "created" event.
     */
    public function created(ItemUnit $itemUnit): void
    {
        TransactionLogHelper::register($itemUnit, "IU-POST");
    }

    /**
     * Handle the ItemUnit "updated" event.
     */
    public function updated(ItemUnit $itemUnit): void
    {
        TransactionLogHelper::register($itemUnit, "IU-PUT");
    }

    /**
     * Handle the ItemUnit "deleted" event.
     */
    public function deleted(ItemUnit $itemUnit): void
    {
        TransactionLogHelper::register($itemUnit, "IU-DELETE");
    }

    /**
     * Handle the ItemUnit "restored" event.
     */
    public function restored(ItemUnit $itemUnit): void
    {
        //
    }

    /**
     * Handle the ItemUnit "force deleted" event.
     */
    public function forceDeleted(ItemUnit $itemUnit): void
    {
        //
    }
}
