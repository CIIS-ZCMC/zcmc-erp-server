<?php 

namespace App\Helpers;

use App\Models\LogDescription;
use App\Models\TransactionLog;
use Illuminate\Database\Eloquent\Model;

class TransactionLogHelper{
    public static function register(Model $model, $logCode,)
    {
        $logDescription = LogDescription::where('code', $logCode)->first();

        if ($logDescription) {
            $log = new TransactionLog();
            $log->log_description_id = $logDescription->id;
            $log->user_id = auth()->user()->id;
            $log->referrence()->associate($model);
            $log->save();
        }
    }
}