<?php

namespace App\Observers;

use App\Helpers\TransactionLogHelper;
use App\Models\PurchaseType;

class PurchaseTypeObserver
{
    /**
     * Handle the PurchaseType "created" event.
     */
    public function created(PurchaseType $purchaseType): void
    {
        TransactionLogHelper::register($purchaseType, 'PT-POST');
    }

    /**
     * Handle the PurchaseType "updated" event.
     */
    public function updated(PurchaseType $purchaseType): void
    {
        TransactionLogHelper::register($purchaseType, 'PT-PUT');
    }

    /**
     * Handle the PurchaseType "deleted" event.
     */
    public function deleted(PurchaseType $purchaseType): void
    {
        TransactionLogHelper::register($purchaseType, 'PT-DELETE');
    }

    /**
     * Handle the PurchaseType "restored" event.
     */
    public function restored(PurchaseType $purchaseType): void
    {
        //
    }

    /**
     * Handle the PurchaseType "force deleted" event.
     */
    public function forceDeleted(PurchaseType $purchaseType): void
    {
        //
    }
}
