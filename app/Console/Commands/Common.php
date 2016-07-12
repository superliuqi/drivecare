<?php
namespace App\Console\Commands;
use Validator;
use Curl;
use PRedis;
class common{
    /**
     * 创建订单编号
     * @param goodsID
     * @return tradeID
     */
     public function createTradeID(){
         list($usec) = explode(" ", microtime());
         $msec       = explode(".",$usec);
         return date("Ymdhis").$msec[1];
     }
    /**
    * sign
    * @param username password appKey secret
    * @return sign array
    */
    public function getSignArray($array){
        $array['secret'] = $this->getSecret($array['appKey']);
        foreach ($array as $key=>$value){
            $arr[$key] = $key;
        }
        sort($arr);
        $str = "";
        foreach ($arr as $k => $v){
            $str = $str.$arr[$k].$array[$v];
        }
        $array['sign'] = strtoupper(sha1($str));
        unset($array['secret']);
        return $array;
    }
    /**
    * check sign
    * @param array
    * @return  true false
    */
    public function checkSign($array){
      $p_sign = $array['sign'];
      unset($array['sign']);
      $array['secret'] = $this->getSecret($array['appKey']);
      foreach ($array as $key=>$value){
          $arr[$key] = $key;
      }
      sort($arr);
      $str = "";
      foreach ($arr as $k => $v){
          $str = $str.$arr[$k].$array[$v];
      }
      $sign = strtoupper(sha1($str));
      // var_dump($sign);exit;
      if($p_sign == $sign){
        return TRUE;
      }
      return FALSE;
    }
    /**
     * 生成回调请求参数
     * @param $param
     * @return string
     */
    public function createLinkString($url='',$param) {
        return $url.'?'.http_build_query($param);
    }

    /**
     * 获取access_token
     * @param appKey sign code grantType accountID scope ....
     * @return param array
     */
    public function getAccessToken($request_data){
        $url=config('app.request_url')['getauthorizationCode'];
        $result = $this->accessApi($request_data,$url);
        $code = json_decode($result,TRUE);
        if($code['ERRORCODE']==0){
            $request_data['grantType']='authorizationCode';
            $request_data['code']=$code['RESULT']['authorizationCode'];
            $url=config('app.request_url')['getAccessToken'];
            $result = $this->accessApi($request_data,$url);
            $code = json_decode($result,TRUE);
        }
        if(!isset($code['RESULT']['accountID'])){
            $code['RESULT']['accountID']=$request_data['accountID'];
        }
        return $code;
    }

    /**
     * 更新access_token
     * @param appKey sign refreshToken grantType....
     * @return param array
     */
    public function updateAccessToken($request_data){
        $url=config('app.request_url')['updateAccessToken'];
        $result = $this->accessApi($request_data,$url);
        return json_decode($result,TRUE);
    }

    /**
     *  请求api
     *  @param array url
     *  @return param array
     */
    public function accessApi($request_data,$url){
        $result = Curl::to($url)
            ->withData($this->getSignArray($request_data))->post();
        return $result;
    }
    /**
    * 通过redis获取 secret
    * @param appKey
    * @return secret
    */
    public function getSecret($appKey){
      if($appKey == config('app.dev_config')['appKey']){
        return config('app.dev_config')['secret'];
      }
      // var_dump(PRedis::hget($appKey.':appKeyInfo','secret'));exit;
      return PRedis::hget($appKey.':appKeyInfo','secret');
    }
    /**
    * 通过redi获取城市
    * @param lon lat
    * @return cityCode
    */
    public function getCityName($lon,$lat){
        $redis = PRedis::connection('redis.grid');
        $grid = (int)($lon*100).'&'.(int)($lat*100);
        return PRedis::hgetall($grid);
    }
}
