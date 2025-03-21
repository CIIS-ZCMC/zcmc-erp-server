<?php

namespace App\Observers;

use App\Helpers\TransactionLogHelper;
use App\Models\ItemCategory;

class ItemCategoryObserver
{
    /**
     * Handle the ItemCategory "created" event.
     */
    public function created(ItemCategory $itemCategory): void
    {
        TransactionLogHelper::register($itemCategory, 'IC-POST');
    }

    /**
     * Handle the ItemCategory "updated" event.
     */
    public function updated(ItemCategory $itemCategory): void
    {
        TransactionLogHelper::register($itemCategory, 'IC-PUT');
    }

    /**
     * Handle the ItemCategory "deleted" event.
     */
    public function deleted(ItemCategory $itemCategory): void
    {
        TransactionLogHelper::register($itemCategory, 'IC-DELETE');
    }

    /**
     * Handle the ItemCategory "restored" event.
     */
    public function restored(ItemCategory $itemCategory): void
    {
        //
    }

    /**
     * Handle the ItemCategory "force deleted" event.
     */
    public function forceDeleted(ItemCategory $itemCategory): void
    {
        //
    }
}
