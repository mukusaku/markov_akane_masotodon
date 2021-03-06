<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/mastodon/getActions/GetGlobalTimelineApi.php';
require __DIR__ . '/mastodon/postActions/PostTootApi.php';
require 'convertEntity.php';
require 'originalList.php';
use YuzuruS\Mecab\Markovchain;

$formatter = new formatter();
$formatter->execToot();
class formatter {
    function execToot(){
        // マルコフ連鎖の元となるテキストを生成する
        $rawText = $this->generateText();
        //print_r($rawText, false);
        $convertedText = $this->convertToAko($rawText);
        //print_r($convertedText, false);
        $markovText = $this->convertToMarkov($convertedText);
        //print_r($markovText, false);
        if(isset($markovText)) {
            $this->toot($markovText);
            return;
        } else {
            return;
        }
    }
    
    // 連合TLからトゥートを取得し整形する
    function generateText(){

        // 連合TL取得APIを叩きトゥートを取得
        $getGlobaltimeLineApi = new getActions\GetGlobalTimelineApi();
        $ary = $getGlobaltimeLineApi->getGlobalTimeline();

        $ol = new originalList();
        $string = "";
        $i = 0; // ループ用
        foreach($ary as $skey => $sValue) {
            // 先頭40トゥートを抽出対象とする
            if($i == 40) {
                break;
            }
            
            // 取得したJSONをパースしhtmlタグを削除したトゥートだけを抽出する
            $rawValue = strip_tags($sValue['content']);
            
            // 英字・記号が含まれていたらスキップ
            if(preg_match('/[a-zA-Z0-9!-\/:-@¥\[-`{-~\]]/', $rawValue)) {
                continue;
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
        for($i=0; $i<=100; $i++){
            $markovText = $mc->makeMarkovText($rawText);
            // 最初に句点が出るところまで切り出す
            $markovText = substr($markovText,0,strpos($markovText, '。'));
            // 1文字以上50文字以下の文章が生成できた場合はループを抜ける
            if(mb_strlen($markovText) > 0 && mb_strlen($markovText) <= 50) {
                return $markovText;
            }
        }
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
    function toot($sentence, $bAdding = true) {
        // 接頭辞、接尾辞の追加
        if($bAdding) {
            $sentence = $this->addPrefix($sentence);                    
            $sentence = $this->addSuffix($sentence);
        }
        // トゥートAPIを叩く
        $request = new postActions\PostTootApi();
        $request->toot($sentence);
    }
}
