<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Admin;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public $config = array(
        'AppID'     => '236a7caa592b692f',  //此处填写你的appid
        'AppSecret' => '5049dc170dd8ec17fd27e186e0b3f07e',  //此处填写你的AppSecret
        'code'      => '',   //授权码
        'info'      => '',   //令牌信息存储
        'user'      => '',   //user信息
        'admin'     => false,  //判断是否为管理员
    );
    const CODE_REDIRECT = 'http://139.199.79.172/renwen/public/token';   //获取code后跳转地址
    const TOKEN_REDIRECT = 'http://139.199.79.172/renwen/public/token';   //获取token后跳转地址
    const PUBLIC_OPTION = 'https://openapi.yiban.cn/';   //查询信息公用开头网址部分
    const OAUTH_CODE = self::PUBLIC_OPTION.'oauth/authorize';   //获取code
    const OAUTH_TOKEN = self::PUBLIC_OPTION.'oauth/access_token';   //获取token
    const TOKEN_QUERY = self::PUBLIC_OPTION.'oauth/token_info';   //获取token信息
    const TOKEN_REVOKE = self::PUBLIC_OPTION.'oauth/revoke_token';   //取消授权

    /**
     * 登录
     */
    public function login(){
        return $this->getCode();
    }

    public function logout(Request $request){
        $url = self::TOKEN_REVOKE;
        $param = array();
        $config = session('config');
        $param['access_token'] = $config['info']['access_token'];
        $param['client_id'] = $config['AppID'];
        $result = $this->config['user'] = $this->queryURL($url, $param, true);
        // 注销回话的token
        if(isset($result['info'])){
            return -1;
        }
        $request->session()->flush();
        return 1;
    }

    /**
     * 获取code
     * @return void
     */
    public function getCode(){
        $query = http_build_query(array(
            'client_id'		=> $this->config['AppID'],
            'redirect_uri'	=> self::CODE_REDIRECT,
        ));
        $url = self::OAUTH_CODE.'?'.$query;
        return redirect($url);
    }

    /**
     * 访问接口，获取json数据
     * @param [type] $url
     * @param array $parm
     * @param boolean $isPOST
     * @return void
     */
    public function queryURL($url, $param=array(), $isPOST=false){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        if($isPOST) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
        }else if(!empty($param)) {
            $xi = parse_url($url);
            $url .= empty($xi['query']) ? '?' : '&';
            $url .= http_build_query($param);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        $result = curl_exec($ch);
        if($result == false) {
            return 'failed to login';
        }
        curl_close($ch);
        
        return json_decode($result, true);
    }

    public function getToken(Request $request){
        // 检查code是否正确返回
        if(!empty($this->config['info']) && !isset($this->config['info']['access_token'])){
            $error = '授权失败，请重新授权';
            return $error;
        }
        
        // 获取token
        $code = $request->input('code');
        $this->config['code'] = $code;
        if(!empty($code)){
            $url = self::OAUTH_TOKEN;
            $param = array(
                'client_id'		=> $this->config['AppID'],
                'client_secret'	=> $this->config['AppSecret'],
                'code'			=> $this->config['code'],
                'redirect_uri'	=> self::TOKEN_REDIRECT,
            );
            $this->config['info'] = $this->queryURL($url, $param, TRUE);
            if(!isset($this->config['info']['access_token'])){
                $error = '授权失败，请重新授权';
                return $error;
            }
            if(!$this->checkInstitute($this->config['info']['access_token'])){
                return '您的账号不是人文学院的易班账号，不能使用该应用，非常抱歉^_^;';
            }
            
            // 保存token信息，跳转主页
            session(['config'=>$this->config]);
            return redirect('http://www.scauwlb.top/renwen');
        }
    }

    protected function checkInstitute($token){
        $url = self::PUBLIC_OPTION.'user/verify_me';
        $param = array();
        $param['access_token'] = $token;
        $arr = $this->queryURL($url, $param, false);
        $institute = $arr['info']['yb_collegename'];
        if(($institute==='数学与信息、软件学院') || ($institute ==='人文与法学学院')){
            return true;
        }
        return false;
    }

    public function getInfo(Request $request){
        // 从session中取出token
        $config = session('config');
        if(empty($config['info']['access_token'])){
            $error = '授权失败，请重新授权';
            // return $error;
            return dd($request->session());
        }
        
        $url = self::PUBLIC_OPTION.'user/me';
        $param = array();
        $param['access_token'] = $config['info']['access_token'];

        //判断是否是管理员
        $user = $this->queryURL($url, $param);
        $admin = false;
        $admin_account = $user['info']['yb_username'];
        $lend_num = Admin::where('account', $admin_account)->get()->count();
        if($lend_num > 0){
            $admin = true;
        }
        
        // 保存用户信息到session
        $config['user'] = $user;
        $config['admin'] = $admin;
        session(['config'=>$config]);
        
        // 返回用户信息
        return response()->json(['info'=>$user, 'admin'=>$admin]);
    }

    public function judge(Request $request){
        $config = session('config');
        if($config->admin){
            return -1;
        }

        $id = request('id');
        $pass = request('pass');
        $lend = Lend::find($id);
        $lend->pass = $pass;
        $lend->update();
        $msg = 'success';

        return $msg;
    }

    public function error(){
        return view('500error');
    }

}
