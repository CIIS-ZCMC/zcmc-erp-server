<?php 

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

class SecurePlainPasswordHelper
{
    public static function execute(Request $request)
    {
        $password = $request->password;
        $hash_password = Hash::make($password);
        $encrypted_password = Crypt::encrypt($hash_password);
        $expire_at =  Carbon::now()->addMonths(3);

        return [
            "password" => $encrypted_password,
            "expire_at" => $expire_at
        ];
    }
}