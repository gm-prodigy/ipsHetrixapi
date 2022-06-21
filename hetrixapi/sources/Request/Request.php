<?php
/**
 * @brief Request Class
 * @copyright -storm_copyright-
 * @package IPS Social Suite
 * @subpackage hetrixapi
 * @since -storm_since_version-
 * @version -storm_version-
 */


namespace IPS\hetrixapi;

use IPS\Task\Queue\OutOfRangeException;
use IPS\Settings;
use IPS\Data\Store;
use IPS\Db;
use IPS\Member;
use IPS\Http\Request;
use IPS\Http\Request\Sockets;
use IPS\Http\Request\Curl;
use IPS\Http\Url;
use IPS\Http\Response;
use IPS\Lang;
use IPS\core\ProfileFields\Field;
use InvalidArgumentException;
use Exception;
use RuntimeException;
use LogicException;

if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
    header(($_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

use function defined;
use function header;


/**
 * Request Class
 *
 * @mixin \IPS\hetrixapi\Request
 */
class _Request
{

    const API_URL = 'https://api.hetrixtools.com/v1/';

    /**
     * @var string
     */
    public $api = '';
    /**
     * @var array
     */
    public $cache = array();
    /**
     * @var array
     */
    public $cacheData = array();
    /**
     * @var string
     */
    public $query = '';
    /**
     * @var
     */
    protected $err;

    public $arr = [];

    public function __construct($queryString = [])
    {
        $this->api = Settings::i()->hetrixapi_key;
        if (!$this->api) {
            throw new InvalidArgumentException('hetrixapi_err_noapi');
        }
    }

    public static function successCodes() { return [200, 201, 204, 304]; }

//    public static function successCodes() {
//        return 200;
//    }

    protected function request($url){
        $req = null;
        $json = null;
        try{
            $req = \IPS\Http\Url::external($url)->request();
            return $req->get();
        } catch (Request\CurlException $e){

        }
    }



    public function uptimeReport(){
        $uptimeURL = 'https://api.hetrixtools.com/v1/' . $this->api . '/uptime/monitors/0/30';

        try{
            /**
             * @var Response $req
             */
            $req = $this->request($uptimeURL);

            if (!\in_array($req->httpResponseCode, $this->successCodes())) {

//                // API Call failed, some logs here.
//                return false;
            }

            $json = json_decode($req, false);



            $statsArr = [];

            

            foreach($json[0] as $i) {
                $object = new \stdClass();
                $object->ID = $i->ID;
                $object->Name = $i->Name;
                $object->Uptime_Status = $i->Uptime_Status;
                array_push($this->arr, $object);
            }

//            return array_merge($arr, $statsArr);
            return $this->arr;

        }catch (\OutOfRangeException $e){
            // some logs here.
        }


    }

    public function gStats(){

        $statsArr = [];

        foreach($this->arr as $stats){
            if($this->serverStats($stats->ID) !== null){
                array_push($statsArr, $this->serverStats($stats->ID));
            }
//                array_push($statsArr, $this->serverStats($stats->ID));

        }

        return $statsArr;
    }

    public function serverStats($id){
        $statsURL = 'https://api.hetrixtools.com/v1/' . $this->api . '/server/stats/' .$id .'/';

        $req = $this->request($statsURL);

        if (!\in_array($req->httpResponseCode, $this->successCodes())) {
              return false;
        }

        $json = json_decode($req, false);

        if(property_exists($json, 'Stats')){
            $object = new \stdClass();
            $object->id = $id;
        $object->name = $json->UptimeMonitorName ? $json->UptimeMonitorName : 'test';

            $object->cpu = property_exists($json, 'Stats') ? round($json->Stats[0]->CPU) : 'NO';
            $object->ram = property_exists($json, 'Stats') ? round($json->Stats[0]->RAM) : 'NO';
            return $object;
        }


        return null;
    }

}

