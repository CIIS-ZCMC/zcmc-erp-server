<?php 

namespace App\Helpers;

class MetadataComposerHelper
{
    protected bool $is_development;
    
    public function __construct()
    {
        $this->is_development = $this->shouldEnableDevMode();
    }
    
    protected function shouldEnableDevMode(): bool
    {
        return app()->environment('local') || config('app.debug');
    }

    public static function compose($method, $module)
    {
        if($method === 'get'){
            $meta['methods'] = ["GET, POST, PUT, DELETE"];
            $meta['modes'] = ['selection', 'pagination'];

            if(self::$is_development){
                $meta['urls'] = [
                    env("SERVER_DOMAIN")."/api/".$module."?id=[primary-key]",
                    env("SERVER_DOMAIN")."/api/".$module."?page={currentPage}&per_page={number_of_record_to_return}",
                    env("SERVER_DOMAIN")."/api/".$module."?page={currentPage}&per_page={number_of_record_to_return}&mode=selection",
                    env("SERVER_DOMAIN")."/api/".$module."?page={currentPage}&per_page={number_of_record_to_return}&search=value",
                ];
            }

            return $meta;
        }
        
        if($method === 'put'){
            $meta = ["methods" => "[PUT]"];
        
            if (self::$is_development) {
                $meta["urls"] = [
                    env("SERVER_DOMAIN")."/api/".$module."?id=1",
                    env("SERVER_DOMAIN")."/api/".$module."?id[]=1&id[]=2"
                ];
                $meta['fields'] = ["title", "code", "description"];
            }
            
            return $meta;
        }
        
        $meta = ['methods' => ["GET, PUT, DELETE"]];

        if(self::$is_development) {
            $meta["urls"] = [
                env("SERVER_DOMAIN") . "/api/" . $module . "?id=1",
                env("SERVER_DOMAIN") . "/api/" . $module . "?id[]=1&id[]=2",
                env("SERVER_DOMAIN") . "/api/" . $module . "?query[target_field]=value"
            ];

            $meta["fields"] =  ["code"];
        }

        return $meta;
    }
}