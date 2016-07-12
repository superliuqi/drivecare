<?php
namespace App\Http\Controllers;
use Validator;
use Crypt;
use DB;
use Redis;
use Redirect;
use App\Console\Commands\common;
use App\Models\TokenInfo;
use App\Models\InsuranceAdInfo;
use Illuminate\Support\MessageBag;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
/**
 * oauth
 */
class AdController extends Controller {
    private $common;
    function __construct() {
        $this->common=new common();
    }

    /**
    * 展示广告主屏页面
    */
    public function showPage(Request $request){       
        $get_data = $request->all();
        $tokenKey = $get_data['tokenKey'];
        $ad_data  = InsuranceAdInfo::select('adStatus')->where('tokenKey',$tokenKey)->first();
        $imei     = TokenInfo::select('imei','accountID','accessToken')->where('tokenKey',$tokenKey)->first();
        $get_data['imei'] = $imei->imei;
        $get_data['accountID'] = $imei->accountID;
        $get_data['accessToken'] = $imei->accessToken;
        if($ad_data->adStatus != '0'){
          return response()->view('succ',$get_data);
        }
          return response()->view('index',$get_data);
      }

    /**
    * 展示成功页面
    */
    public function showSuccess(Request $request){
      $get_data = $request->all();
      $tokenKey = $get_data['tokenKey'];
      $imei     = TokenInfo::select('imei','accountID','accessToken')->where('tokenKey',$tokenKey)->first();
      $get_data['imei'] = $imei->imei;
      $get_data['accountID'] = $imei->accountID;
      $get_data['accessToken'] = $imei->accessToken;
      return response()->view('succ',$get_data);
    }

    /**
    * 展示理赔流程页面
    */
    public function claims(Request $request){
      return response()->view('claims');
    }

    /**
    * 展示保障条款页面
    */
    public function clause(Request $request){
      return response()->view('clause');
    }

    /**
    * 获取意外险赠送令牌
    * @param appKey sign accessTonken timestamp accountID lat lon imei imsi mac
    * @return tokenKey
    */
    public function getTokenKey(Request $request){
        $post_data = $request->all();
        unset($post_data['_url']);
        $add_rules = [
          'accountID'  => 'required',
          'appKey'     => 'required',
          'lat'  => 'required',
          'lon'  => 'required',
          'imei' => 'required',
          'imsi' => 'required',
          // 'mac'  => 'required',
          'modelVer'    => 'required',
          'androidVer'  => 'required',
          'baseBandVer' => 'required',
          'kernelVer'   => 'required',
          'lcdWidth'    => 'required',
          'lcdHeight'   => 'required'
        ];
        $check_param = $this->checkParam($post_data,$add_rules);
        if($check_param->fails()){
          return response()->json(['ERRORCODE' => 'ME01023', 'RESULT' => $check_param->errors()]);
        }
        if(!$this->common->checkSign($post_data)){
          return response()->json(['ERRORCODE' => 'ME01019', 'RESULT' => "sign is not match"]);
        }
        //accountID不等于imei 说明通过实名认证
        if($post_data['accountID'] != $post_data['imei']){
          //验证accountID
          $accountID_array = [
            'accessToken' => empty($post_data['accessToken'])?'':$post_data['accessToken'],
            'appKey'      => $post_data['appKey'],
            'accountID'   => $post_data['accountID']
          ];
          $request_url = config('app.request_url')['checkAccountID'];
          $result = $this->common->accessApi($accountID_array,$request_url);
          $body = json_decode($result,TRUE);
          if($body['ERRORCODE']!="0"){
            return response()->json($result);
          }
        }
        //获取广告
        $request_data = [
          'appKey'  => $post_data['appKey'],
          'cid'     => $post_data['imei'],
          'typ'     => 4,
          'lng'     => $post_data['lon'],
          'lat'     => $post_data['lat'],
          'speed'   => 0,
          'dir'     => 0,
          'time'    => $post_data['timestamp']
        ];
        $request_url = config('app.request_url')['getAdInfo'];
        $result = $this->common->accessApi($request_data,$request_url);
        $body = json_decode($result,TRUE);
        //ERRORCODE":"ME25006", "RESULT":"content does not exist
        if($body['ERRORCODE']!="0"){
          return response()->json($result);
        }
        //生成tokenKey
        $insert_token_data = [
          'accountID'       => $post_data['accountID'],
          'appKey'          => $post_data['appKey'],
          'tokenKey'        => md5(sha1($post_data['accountID']).$post_data['imei'].$post_data['imsi'].uniqid()),
          'tokenValidTime'  => strtotime('+5 minute'),
          'accessToken'     => empty($post_data['accessToken'])?'':$post_data['accessToken'],
          'latitude'        => $post_data['lat']*1000000,
          'longitude'       => $post_data['lon']*1000000,
          'imei'            => $post_data['imei'],
          'imsi'            => $post_data['imsi'],
          'mac'             => empty($post_data['mac'])?'':$post_data['mac'],
          'modelVer'        => $post_data['modelVer'],
          'androidVer'      => $post_data['androidVer'],
          'baseBandVer'     => $post_data['baseBandVer'],
          'buildVer'        => $post_data['buildVer'],
          'kernelVer'       => $post_data['kernelVer'],
          'lcdWidth'        => $post_data['lcdWidth'],
          'lcdHeight'       => $post_data['lcdHeight'],
          'createTime'      => time()
        ];
        $today    = strtotime('today');
        $tomorrow = strtotime('tomorrow');
        //判断今天是否有领取过
        $is_today_receive = TokenInfo::select('tokenKey','tokenValidTime')->where('accountID',$post_data['accountID'])->whereBetween('updateTime', [$today, $tomorrow])->first();
        if(count($is_today_receive) != 0){
          $return = [
            'tokenKey'      =>  $is_today_receive->tokenKey,
            'tokenValidTime'=>  $is_today_receive->tokenValidTime
          ];
          return response()->json(['ERRORCODE' => 'ME10030', 'RESULT' => $return]);
        }
        $is_has_tokenKey  = TokenInfo::select('tokenKey','tokenValidTime')->where('accountID',$post_data['accountID'])->first();
        if(count($is_has_tokenKey) != 0){
          $save_tokenKey_satus = TokenInfo::where('accountID',$post_data['accountID'])->update($insert_token_data);
        }else{
          $save_tokenKey_satus = TokenInfo::insert($insert_token_data);
        }
        if($save_tokenKey_satus){
          $insert_ad_data = [
            'tokenKey'    => $insert_token_data['tokenKey'],
            'adID'        => $body['RESULT']['aid'],
            'webUrl'      => $body['RESULT']['content']['url'],
            'sponsorName' => $body['RESULT']['content']['text'],
            'logoUrl'     => $body['RESULT']['content']['logoUrl'],
            'createTime'  => time()
          ];
          $save_adInfo_satus = InsuranceAdInfo::insert($insert_ad_data);
          if($save_adInfo_satus){
            $result_array = [
              'tokenKey'       => $insert_token_data['tokenKey'],
              'tokenValidTime' => $insert_token_data['tokenValidTime']
            ]; 
            return response()->json(['ERRORCODE' => '0', 'RESULT' => $result_array]);+
          }
          return response()->json(['ERRORCODE' => 'ME10001', 'RESULT' => "save ad error"]);
        }
        return response()->json(['ERRORCODE' => 'ME10002', 'RESULT' => "save tokenKey error"]);
    }
    /**
    * 根据令牌获取赞助商信息
    * @param appKey sign tokenKey timestamp
    * @return sponsor info
    *
    */
    public function getSponsorByKey(Request $request){
        $post_data = $request->all();
        unset($post_data['_url']);
        $add_rules = [
          'tokenKey'  => 'required'
        ];
        $check_param = $this->checkParam($post_data,$add_rules);
        if($check_param->fails()){
          return response()->json(['ERRORCODE' => 'ME01023', 'RESULT' => $check_param->errors()]);
        }
        if(!$this->common->checkSign($post_data)){
          return response()->json(['ERRORCODE' => 'ME01019', 'RESULT' => "sign is not match"]);
        }
        $is_tokenKey = TokenInfo::select('tokenKey','tokenValidTime')->where('tokenKey',$post_data['tokenKey'])->first();
        if(count($is_tokenKey) === 0){
          return response()->json(['ERRORCODE' => 'ME10003', 'RESULT' => "tokenKey is error"]);
        }
        $ad_data = InsuranceAdInfo::select('tokenKey', 'webUrl', 'sponsorName', 'logoUrl', 'adStatus')->where('tokenKey',$post_data['tokenKey'])->first();
        /*首屏的广告页面地址,广告参数加密后拼链接*/
          $ad_new_data = [
          'tokenKey'    => $ad_data->tokenKey,
          'sponsorUrl'  => $ad_data->webUrl,
          'sponsorName' => $ad_data->sponsorName,
          'sponsorLogo' => $ad_data->logoUrl
        ];
          $return_array = [
          'sponsorUrl' => $this->common->createLinkString(url().'/show/page',$ad_new_data),
          'tokenKey'   => $ad_new_data['tokenKey'],
          'sponsorName'=> $ad_new_data['sponsorName'],
          'sponsorLogo'=> $ad_new_data['sponsorLogo']
        ];
        //判断提交过来的tokenKey 是否已经领取过  领取过的话，也组装url返回，否则继续验证相关条件（是否过期以及tokenKey是否正确）
        if($ad_data->adStatus == '0'){
          if($is_tokenKey->tokenValidTime < time()){
            return response()->json(['ERRORCODE' => 'ME10004', 'RESULT' => "tokenKey is expired"]);
          }
          if(count($ad_data) === 0){
            return response()->json(['ERRORCODE' => 'ME10005', 'RESULT' => "ad is empty"]);
          }
        }
        return response()->json(['ERRORCODE' => '0', 'RESULT' => $return_array]);
    }
    /**
    * 领取意外险
    * @param tokenKey
    * @return
    */
    public function receiveInsurance(Request $request){
      $post_data = $request->all();
      $tokenKey = $request->input('tokenKey');
      $ad_data = InsuranceAdInfo::select('adStatus')->where('tokenKey',$tokenKey)->first();
      if($ad_data->adStatus != '0'){
        return response()->json(['ERRORCODE' => 'ME01025', 'RESULT' => 'tokenKey is used']);
      }
      $update_data = [
        'adStatus'  => 1,
        'securityID'=> md5($tokenKey.time()),
        'updateTime'=> time()
      ];
      $update_time = [
        'updateTime'  =>  time()
      ];
      $update_ad_sataus = InsuranceAdInfo::where('tokenKey',$post_data['tokenKey'])->update($update_data);
      $res = TokenInfo::where('tokenKey',$post_data['tokenKey'])->update($update_time);
      if($update_ad_sataus && $res){
        return response()->json(['ERRORCODE' => '0', 'RESULT' => ['securityID' => $update_data['securityID']]]);
      }
    }
    /**
    * 根据令牌获取安驾保障信息
    * @param appKey sign tokenKey timestamp
    * @return tokenKey isClick securityID chickTime
    */
    public function getSecurityState(Request $request){
      $post_data = $request->all();
      unset($post_data['_url']);
      $add_rules = [
        'tokenKey'  => 'required'
      ];
      $check_param = $this->checkParam($post_data,$add_rules);
      if($check_param->fails()){
        return response()->json(['ERRORCODE' => 'ME01023', 'RESULT' => $check_param->errors()]);
      }
      if(!$this->common->checkSign($post_data)){
        return response()->json(['ERRORCODE' => 'ME01019', 'RESULT' => "sign is not match"]);
      }
      $ad_data = InsuranceAdInfo::select('tokenKey', 'adStatus', 'securityID', 'updateTime')->where('tokenKey',$post_data['tokenKey'])->first();
      if(count($ad_data) === 0){
        return response()->json(['ERRORCODE' => 'ME10005', 'RESULT' => "tokenKey is error"]);
      }
      $result_array = [
        'tokenKey'  => $ad_data->tokenKey,
        'isClick'   => (bool)$ad_data->adStatus,
        'securityID'=> $ad_data->securityID,
        'clickTime' => $ad_data->updateTime
      ];
      return response()->json(['ERRORCODE' => '0', 'RESULT' => $result_array]);
    }

    /**
    * 验证请求参数
    * @param which display appKey redirect_url response_type
    * @return array
    */
    public function checkParam($param,$add_rules=[]){
        $message = [
            'required'  =>  '缺少 :attribute 参数'
        ];
        $rules = [
            'appKey'    =>  'required',
            'sign'      =>  'required',
            'timestamp' =>  'required'
        ];
        $validator = Validator::make($param,array_merge($rules,$add_rules),$message);
        return $validator;
    }
}
