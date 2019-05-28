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
        $convertedText = $this->convertToAko($rawText);
        //print_r($convertedText, false);
        $markovText = $this->convertToMarkov($convertedText);
        
        //print_r($markovText, false);
        $this->toot($markovText);
    }
    
    // 連合TLからトゥートを取得し整形する
    function generateText(){
        $ol = new originalList();
        $url = "https://akanechan.love/api/v1/timelines/public";
        $json = file_get_contents($url); // 連合から取得したJSON
        $json = mb_convert_encoding($json, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
        $ary = json_decode($json,true);
        $string = "";
        $i = 0; // ループ用
        foreach($ary as $skey => $sValue) {
            // 先頭10トゥートを抽出対象とする
            if($i == 10) {
                break;
            }
            
            // 取得したJSONをパースしhtmlタグを削除したトゥートだけを抽出する
            $rawValue = strip_tags($sValue['content']);
            
            // URLを除外する
            preg_match_all('(https?://[-_.!~*\'()a-zA-Z0-9;/?:@&=+$,%#]+)', $rawValue, $result);
            foreach($result as $sResult){
                $rawValue = str_replace($sResult,"",$rawValue);
            }
            
            // 末尾が句読点や感嘆符じゃなかったら文節判定用に「。」を付ける
            if(substr($rawValue,-1) != "。" 
                && substr($rawValue,-1) != "、" 
                && substr($rawValue,-1) != "！" 
                && substr($rawValue,-1) != "？")
            {
                $rawValue .= "。";
            }
            
            // 文章を連結する
            $string .= $rawValue;
            $i++;
        }
        //$rawText = $string . $ol->implodeSentences(); // この行を有効化するとオリジナルテキストも参照する
        
        return $string;
    }
    // 変換リストに沿った文章の加工を行う
    function convertToAko($rawText) {
        $sentence = "";
        $convertEntity = new convertEntity();
        // 変換対象の用語リストを配列で取得
        $aryConvertList = $convertEntity->aryConvertList;
        foreach($aryConvertList as $sBefore => $sAfter) {
            $rawText = str_replace($sBefore, $sAfter, $rawText);
        }
        $sentence = $rawText;
        return $sentence;
    }
    // マルコフ連鎖を利用した変換を行う
    function convertToMarkov($rawText) {
        //return $rawText; // この行を有効化するとマルコフ連鎖をオフ
        $mc = new Markovchain();
        $i = 0; // 無限ループ回避
        do {
            // 1文字以上の文章ができるまで処理をやり直す
            $markovText = $mc->makeMarkovText($rawText);
            // 最初に句点が出るところまで切り出す
            $markovText = substr($markovText,0,strpos($markovText, '。'));
            $i++;
        } while(mb_strlen($markovText) == 0 || $i < 100);
        
        return $markovText;
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
        $access_token = '6f5f65a75df235c6f719a9389a1dff5a1053f7ca6aab0f4f83677d4fd0acfdfe';
        $method       = 'POST';
        $endpoint     = '/api/v1/statuses';
        $url          = "${schema}://${host}${endpoint}";
        $visibility   = 'unlisted'; //投稿のプライバシー設定→「未収載」
        $toot_msg     = rawurlencode($sentence); //メッセージをcURL用にエスケープ
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
        //print_r(json_decode($result, JSON_OBJECT_AS_ARRAY));
        print $toot_msg;
    }
}
