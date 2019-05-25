<?php
require __DIR__ . '/vendor/autoload.php';
use YuzuruS\Mecab\Markovchain;

$mc = new Markovchain();


$url = "https://akanechan.love/api/v1/timelines/public";
// 取得したJSONをパースしトゥートだけを抽出する
$json = file_get_contents($url); // 連合から取得したJSON
$json = mb_convert_encoding($json, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
$ary = json_decode($json,true);

$string = "";
foreach($ary as $skey => $sValue) {
    // 末尾が句読点の「。」じゃなかったら「。」を付ける
    if(substr($sValue['content'],-1) != "。") {
        $sValue['content'] .= "。";
    }
    $string .= $sValue['content'];

}
//print_r($ary,false);
//echo $string."\r";
$text = convertToAko($string);

$markovText = $mc->makeMarkovText($text);

echo $text."\n";
echo '↓'."\n";
echo $markovText."\n";
echo '↓'."\n";

//echo $a;
// -> 楽天アクセスかきくけこ
function convertToAko($sentence){
    // 変換対象の用語リストを配列で取得
//    $aryConvertList = array();
    $aryConvertList = array('アテクシ'=>'あかねちゃん','俺'=>'あかねちゃん');
    foreach($aryConvertList as $sBefore => $sAfter) {
        $sentence = str_replace($sBefore, $sAfter, $sentence);
    }
    $sentence = strip_tags($sentence);
    return $sentence;
}

///////////////////////////////////////////

$toot = substr($markovText,0,strpos($markovText, '。'));
$command = `ruby /home/mastodon/akanebot_mastodon/bot.rb $toot`;
echo $command;