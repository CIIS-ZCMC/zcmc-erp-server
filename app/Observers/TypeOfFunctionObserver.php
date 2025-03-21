<?php

namespace App\Observers;

use App\Helpers\TransactionLogHelper;
use App\Models\TypeOfFunction;

class TypeOfFunctionObserver
{
    /**
     * Handle the TypeOfFunction "created" event.
     */
    public function created(TypeOfFunction $typeOfFunction): void
    {
        TransactionLogHelper::register($typeOfFunction, 'TOF-POST');
    }

    /**
     * Handle the TypeOfFunction "updated" event.
     */
    public function updated(TypeOfFunction $typeOfFunction): void
    {
        TransactionLogHelper::register($typeOfFunction, 'TOF-PUT');
    }

    /**
     * Handle the TypeOfFunction "deleted" event.
     */
    public function deleted(TypeOfFunction $typeOfFunction): void
    {
        TransactionLogHelper::register($typeOfFunction, 'TOF-DELETE');
    }

    /**
     * Handle the TypeOfFunction "restored" event.
     */
    public function restored(TypeOfFunction $typeOfFunction): void
    {
        //
    }

    /**
     * Handle the TypeOfFunction "force deleted" event.
     */
    public function forceDeleted(TypeOfFunction $typeOfFunction): void
    {
        //
    }
}
