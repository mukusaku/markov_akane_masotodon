<?php
require __DIR__ . '/vendor/autoload.php';
require 'convertEntity.php';

use YuzuruS\Mecab\Markovchain;
$toot = new main();
$toot->execToot();
class main {

    function execToot(){
        $rawText = $this->generateText();
        //print_r($rawText, false);
        $sentence = $this->convertToAko($rawText);
        //print_r($sentence, false);
        $this->toot($sentence);
    }

    function generateText(){
        $mc = new Markovchain();
        $url = "https://akanechan.love/api/v1/timelines/public";
        $json = file_get_contents($url); // 連合から取得したJSON
        $json = mb_convert_encoding($json, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
        $ary = json_decode($json,true);
        $string = "";
        foreach($ary as $skey => $sValue) {
            
            // 取得したJSONをパースしトゥートだけを抽出する
            // 末尾が句読点の「。」じゃなかったら「。」を付ける
            if(substr($sValue['content'],-1) != "。") {
                $sValue['content'] .= "。";
            }
            $string .= $sValue['content'];
        }
        return $string;
    }

    function convertToAko($rawText) {
        $convertEntity = new convertEntity();
        $sentence = "";
        // 変換対象の用語リストを配列で取得
        $aryConvertList = $convertEntity->aryConvertList;
        foreach($aryConvertList as $sBefore => $sAfter) {
            $rawText = str_replace($sBefore, $sAfter, $rawText);
        }
        $sentence = strip_tags($rawText);

        return $sentence;
    }

    function addPrefix($sentence) {
        $convertEntity = new convertEntity();
        $aryPrefix = $convertEntity->aryPrefixList;
        $rand = array_rand($aryPrefix);

        return $aryPrefix[$rand] . $sentence;
    }

    function addSuffix($sentence) {
        $convertEntity = new convertEntity();
        $arySuffix = $convertEntity->arySuffixList;
        $rand = array_rand($arySuffix);

        return $sentence . $arySuffix[$rand];
    }

    function toot($sentence) {
        /* Settings */
        $schema       = 'https';
        $host         = 'akanechan.love';
        $access_token = '3b2822ca6af1899b8bd1a1dd89924d7adafc4d4323820f89543a1171f91dfc93';
        $method       = 'POST';
        $endpoint     = '/api/v1/statuses';
        $url          = "${schema}://${host}${endpoint}";
        $visibility   = 'unlisted'; //投稿のプライバシー設定→「未収載」
        $toot_msg     = substr($sentence,0,strpos($sentence, '。'));
        $toot_msg     = rawurlencode($toot_msg); //メッセージをcURL用にエスケープ

        $toot_msg = $this->addPrefix($toot_msg);
        $toot_msg = $this->addSuffix($toot_msg);

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
        print_r(json_decode($result, JSON_OBJECT_AS_ARRAY));
    }
}