<?php
require __DIR__ . '/vendor/autoload.php';
use YuzuruS\Mecab\Markovchain;
$mention = new mention();
$mention->execMention();

class mention {
    function execMention(){
        $aryInfo = $this->getNotifications();
        $aryAkane = $this->getStatuses($aryInfo);
        $tootNecessity = $this->actionReblog($aryAkane);
        // 1件以上ブーストしていた場合あかねちゃんからも言及する
        if($tootNecessity) {
            $this->toot(' :river: ', false);
        }
    }

    function getNotifications() {
        // サーバ情報などの読み込み
        $arySetting = parse_ini_file("mastodon_setting.ini");
        /* Settings */
        $schema       = 'https';
        $host         = $arySetting['server'];
        $access_token = $arySetting['access_token'];
        $method       = 'GET';
        $endpoint     = '/api/v1/notifications';
        $url          = "${schema}://${host}${endpoint}";
        $url         .= "?limit=10";
        /* Build request */
        $query  = "curl -X ${method}";
        $query .= " --header 'Authorization:";
        $query .= " Bearer ${access_token}'";
        $query .= " -sS ${url}";
        /* Request */
        $result = `$query`; //バッククォートに注意
        /* Show result */
        $aryResult = json_decode($result, JSON_OBJECT_AS_ARRAY);
        
        // BTされたトゥートとユーザIDを紐付けた配列を生成する
        $aryBt = array();
        foreach($aryResult as $key => $value) {
            if($value['type'] != "reblog") {
                continue;
            }
            $aryBt += array($value['account']['id'] => strip_tags($value['status']['content']));
            $this->deleteNotification($value['id']);
        }
        //print_r($aryBt, false);
        return $aryBt;
    }

    // 通知の削除
    function deleteNotification($id) {
        // サーバ情報などの読み込み
        $arySetting = parse_ini_file("mastodon_setting.ini");
        /* Settings */
        $schema       = 'https';
        $host         = $arySetting['server'];
        $access_token = $arySetting['access_token'];
        $method       = 'POST';
        $endpoint     = '/api/v1/notifications/dismiss/';
        $url          = "${schema}://${host}${endpoint}";        
        /* Build request */
        $query  = "curl -X ${method}";
        $query .= " -d 'id=${id}'";
        $query .= " --header 'Authorization:";
        $query .= " Bearer ${access_token}'";
        $query .= " -sS ${url}";
        /* Request */
        $result = `$query`; //バッククォートに注意
        return;
    }

    function getStatuses($aryInfo) {
        // 関数の返り値
        $aryAkane = array();
        // サーバ情報などの読み込み
        $arySetting = parse_ini_file("mastodon_setting.ini");
        /* Settings */
        $schema       = 'https';
        $host         = $arySetting['server'];
        $access_token = $arySetting['access_token'];
        $method       = 'GET';
        $endpoint     = '/api/v1/accounts/';
        $aryIds       = array_keys($aryInfo);
        foreach($aryIds as $id) {
            $status       = "$id/statuses/";
            $url          = "${schema}://${host}${endpoint}${status}";        
            /* Build request */
            $query  = "curl -X ${method}";
            $query .= " --header 'Authorization:";
            $query .= " Bearer ${access_token}'";
            $query .= " -sS ${url}";
            /* Request */
            $result = `$query`; //バッククォートに注意
            $aryResult = json_decode($result, JSON_OBJECT_AS_ARRAY);

            $aryBt = array();
            foreach($aryResult as $key => $value) {
                if(strpos($value['content'], 'あかねちゃん') !== false
                    && strpos($value['content'], 'RT') === false
                    && $value['reblogged'] == 0
                    && $value['visibility'] !== 'private') {
                    // 言及対象かつブーストしていなかったら
                    //array_push($aryBt,$value['content']);
                    $aryBt += array($value['id'] => strip_tags($value['content']));
                }
            }
            $aryAkane += $aryBt;
        }
        //print_r($aryAkane, false);
        return $aryAkane;
    }

    // 実際のブースト処理
    function actionReblog($aryAkane) {
        if(count($aryAkane) <= 0) {
            return false;
        }
        // サーバ情報などの読み込み
        $arySetting = parse_ini_file("mastodon_setting.ini");
        /* Settings */
        $schema       = 'https';
        $host         = $arySetting['server'];
        $access_token = $arySetting['access_token'];
        $method       = 'POST';
        $endpoint     = '/api/v1/statuses/';
        $aryIds       = array_keys($aryAkane);
        $url          = "${schema}://${host}${endpoint}";

        foreach($aryIds as $id) {
            $status       = "$id/reblog/";
            $url          = "${schema}://${host}${endpoint}${status}";
            /* Build request */
            $query  = "curl -X ${method}";
            $query .= " --header 'Authorization:";
            $query .= " Bearer ${access_token}'";
            $query .= " -sS ${url}";
            /* Request */
            $result = `$query`; //バッククォートに注意
            /* Show result */
            //print_r(json_decode($result, JSON_OBJECT_AS_ARRAY));
            //print $query; 
        }
        return true;
    }

    // 言及後のトゥート処理
    function toot($sentence, $addNecessity = true) {
        // サーバ情報などの読み込み
        $arySetting = parse_ini_file("mastodon_setting.ini");
        /* Settings */
        $schema       = 'https';
        $host         = $arySetting['server'];
        $access_token = $arySetting['access_token'];
        $method       = 'POST';
        $endpoint     = '/api/v1/statuses';
        $url          = "${schema}://${host}${endpoint}";
        $visibility   = 'unlisted'; //投稿のプライバシー設定→「未収載」
        $toot_msg     = rawurlencode($sentence); //メッセージをcURL用にエスケープ
        if ($addNecessity) {
            $toot_msg = $this->addPrefix($toot_msg);
            $toot_msg = $this->addSuffix($toot_msg);
        }
        /* Build request */
        $query  = "curl -X ${method}";
        $query .= " -d 'status=${toot_msg}'";
        $query .= " -d 'visibility=${visibility}'";
        $query .= " --header 'Authorization:";
        $query .= " Bearer ${access_token}'";
        $query .= " -sS ${url}";
        /* Request */
        $result = `$query`; //バッククォートに注意
        /* Show result */
        //print_r(json_decode($result, JSON_OBJECT_AS_ARRAY));
        print $toot_msg;
    }
}

