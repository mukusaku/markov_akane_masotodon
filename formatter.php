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

//echo $text."\n";
//echo '↓'."\n";
//echo $markovText."\n";
//echo '↓'."\n";

function convertToAko($sentence){
    // 変換対象の用語リストを配列で取得
    $aryConvertList = array('アテクシ'=>'あかねちゃん','俺'=>'あかねちゃん'); // TODO 変換処理は別途実装する
    foreach($aryConvertList as $sBefore => $sAfter) {
        $sentence = str_replace($sBefore, $sAfter, $sentence);
    }
    $sentence = strip_tags($sentence);
    return $sentence;
}

///////////////////////////////////////////

/* Settings */
$schema       = 'https';
$host         = 'akanechan.love';
$access_token = '3b2822ca6af1899b8bd1a1dd89924d7adafc4d4323820f89543a1171f91dfc93';
$method       = 'POST';
$endpoint     = '/api/v1/statuses';
$url          = "${schema}://${host}${endpoint}";
$visibility   = 'unlisted'; //投稿のプライバシー設定→「未収載」
$toot_msg     = substr($markovText,0,strpos($markovText, '。'));
$toot_msg     = rawurlencode($toot_msg); //メッセージをcURL用にエスケープ

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