<?php
require __DIR__ . '/vendor/autoload.php';
require 'convertEntity.php';
require 'originalList.php';

use YuzuruS\Mecab\Markovchain;
$toot = new main();
$toot->execToot();
class main {

    function execToot(){
        $rawText = $this->generateText();
        //print_r($rawText, false);
        $markovText = $this->convertToMarkov($rawText);

        $sentence = $this->convertToAko($markovText);
        //print_r($sentence, false);
        $this->toot($sentence);
    }

    // 連合TLからトゥートを取得し整形する
    function generateText(){
        $ol = new originalList();
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
        //$rawText = $string . $ol->implodeSentences(); // この行を有効化するとオリジナルテキストも参照する
        $rawText = $string;
        return $rawText;
    }

    // マルコフ連鎖を利用した変換を行う
    function convertToMarkov($rawText) {
        $mc = new Markovchain();
        $markovText = $mc->makeMarkovText($rawText);
        return $markovText;
    }

    // 変換リストに沿った文章の加工を行う
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

    // 接頭辞の追加
    function addPrefix($sentence) {
        $convertEntity = new convertEntity();
        $aryPrefix = $convertEntity->aryPrefixList;
        $rand = array_rand($aryPrefix);

        return $aryPrefix[$rand] . $sentence;
    }

    // 接尾辞の追加
    function addSuffix($sentence) {
        $convertEntity = new convertEntity();
        $arySuffix = $convertEntity->arySuffixList;
        $rand = array_rand($arySuffix);

        return $sentence . $arySuffix[$rand];
    }

    // 実際のトゥート処理
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