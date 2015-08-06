<?php

    if ( ! defined ( 'BASEPATH' ) )
        exit( 'No direct script access allowed' );

    class Wtest extends Front {

        public function __construct ()
        {
            // ob_start();//解决setcookie Cannot modify header information - headers already sent by
            header ( "Content-type:text/html;charset=utf-8" );
            header ( "Cache-Control: no-cache, must-revalidate" ); //HTTP 1.1
            header ( "Pragma: no-cache" ); //HTTP 1.0
//        header("Cache-Control: max-age=3600");
            parent::__construct ();
//        header(("Last-Modified:" . gmdate("D,d M Y H:i:s") . "GMT"));
//        if (!isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) || strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) + 32400 < time()) {
//            header(("Last-Modified:" . gmdate("D,d M Y H:i:s") . "GMT"));
//        } else {
//            header("HTTP/1.1 304");
//            exit;
//        }
        }

        public $appID = "wx420e03eb26cc5100";
        public $appsecret = "301fa91bb4dddd9e2bddbcb29f8ac6d5";
        public $url = "http://lanzhoumarathon2015.hupu.com/wtest/redUrl";


        public function index ()
        {
            $echoStr = $_GET[ "echostr" ];
            echo $echoStr;
            exit;
        }

        public function getAccessToken ()
        {

            if ( ! $this->redis->get ( WEBNAME . "accessToken" ) )
            {
                $result = usageCurl ( "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->appID}&secret={$this->appsecret}" );
                $this->redis->setex ( WEBNAME . "accessToken" , 3600 , $result );
            }

            return $this->redis->get ( WEBNAME . "accessToken" );
        }

        public function getIP ()
        {
            $accessToken = json_decode ( $this->getAccessToken () )->access_token;
            var_dump ( $accessToken );
            $result = usageCurl ( "https://api.weixin.qq.com/cgi-bin/getcallbackip?access_token={$accessToken}" );
            var_dump ( $result );
        }

        public function getTicket ()
        {
            $accessToken = json_decode ( $this->getAccessToken () )->access_token;
            var_dump ( $accessToken );
            $data = [ "action_name" => "QR_LIMIT_STR_SCENE" , "action_info" => [ "secene" => [ "scene_str:123" ] ] ];
            $dataJson = json_encode ( $data );

            $ch = curl_init ( "https://api.weixin.qq.com/cgi-bin/menu/create?access_token={$accessToken}" );
            curl_setopt ( $ch , CURLOPT_CUSTOMREQUEST , "POST" );
            curl_setopt ( $ch , CURLOPT_POSTFIELDS , $dataJson );
            curl_setopt ( $ch , CURLOPT_RETURNTRANSFER , true );
//        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
//                'Content-Type: application/json',
//                'Content-Length: ' . strlen($dataJson))
//        );
            $result = curl_exec ( $ch );
            curl_close ( $ch );
            var_dump ( $result );
        }

#查询自定义菜单
        public function queryWindow ()
        {
            $accessToken = json_decode ( $this->getAccessToken () )->access_token;
            $result = usageCurl ( "https://api.weixin.qq.com/cgi-bin/menu/get?access_token={$accessToken}" );
            echo $result;
        }

#自定义菜单创建接口
        public function getWindow ()
        {
            $accessToken = json_decode ( $this->getAccessToken () )->access_token;
            var_dump ( $accessToken );
            $data = array (
                'button' =>
                    array (
                        0 =>
                            array (
                                'type' => 'click' ,
                                'name' => '贝拉' ,
                                'key'  => 'V1001_TODAY_MUSIC' ,
                            ) ,
                        1 =>
                            array (
                                'name'       => '1' ,
                                'sub_button' =>
                                    array (
                                        0 =>
                                            array (
                                                'type' => 'view' ,
                                                'name' => '2' ,
                                                'url'  => 'http://www.soso.com/' ,
                                            ) ,
                                        1 =>
                                            array (
                                                'type' => 'view' ,
                                                'name' => '3' ,
                                                'url'  => 'http://v.qq.com/' ,
                                            ) ,
                                        2 =>
                                            array (
                                                'type' => 'click' ,
                                                'name' => '4' ,
                                                'key'  => 'V1001_GOOD' ,
                                            ) ,
                                    ) ,
                            ) ,
                    ) ,
            );
            $dataJson = json_encode ( $data );
            $ch = curl_init ( "https://api.weixin.qq.com/cgi-bin/menu/create?access_token={$accessToken}" );
            curl_setopt ( $ch , CURLOPT_CUSTOMREQUEST , "POST" );
            curl_setopt ( $ch , CURLOPT_POSTFIELDS , $dataJson );
            curl_setopt ( $ch , CURLOPT_RETURNTRANSFER , true );
//        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
//                'Content-Type: application/json',
//                'Content-Length: ' . strlen($dataJson))
//        );
            $result = curl_exec ( $ch );
            curl_close ( $ch );
            var_dump ( $result );
        }

        #获取用户基本信息(UnionID机制)
        public function getUnionID ()
        {
            $accessToken = json_decode ( $this->getAccessToken () )->access_token;
            $result = usageCurl ( "https://api.weixin.qq.com/cgi-bin/user/info?access_token={$accessToken}&openid=ochtjs5WsUpox5QOaUlKuVjgqcAE&lang=zh_CN" );
            var_dump ( json_decode ( $result ) );
        }

        #生成验证地址
        public function getUrl ()
        {
            $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$this->appID}&redirect_uri={$this->url}&response_type=code&scope=snsapi_userinfo&state=1232#wechat_redirect";
            echo $url;

        }

#通过code换取网页授权access_token
        public function redUrl ()
        {
            var_dump ( $_GET );
            var_dump ( $_GET[ 'code' ] );
            var_dump ( $_GET[ 'state' ] );
            $code = $_GET[ 'code' ];
            #获取access_token和openid
            $result = json_decode ( usageCurl ( "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$this->appID}&secret={$this->appsecret}&code={$code}&grant_type=authorization_code" ) );
            var_dump ( $result );

            $checkResult = $this->checkAccessToken ( $result->access_token , $result->openid );
            var_dump ( $checkResult );

            $userInfo = usageCurl ( "https://api.weixin.qq.com/sns/userinfo?access_token={$result->access_token}&openid={$result->openid}&lang=zh_CN" );
            var_dump ( json_decode ( $userInfo ) );
        }

        #检验授权凭证（access_token）是否有效
        public function checkAccessToken ( $accessToken , $openId )
        {
            $result = json_decode ( usageCurl ( "https://api.weixin.qq.com/sns/auth?access_token={$accessToken}&openid={$openId}" ) );

            return $result;

        }


    }
