<?php
namespace markov_akane_mastodon\mastodon\getActions;

class GetGlobalTimelineApi {

    private $nMaxGetTootCount; // 連合TLから最大いくつトゥートを取得する

    public function __construct($getTootCount = 40) {
        // デフォルトでは40個トゥートを取得する
        $this->nMaxGetTootCount = $getTootCount;
    }

    public function getGlovalTimeline() {
        $url = "https://akanechan.love/api/v1/timelines/public?limit=40";
        $json = file_get_contents($url); // 連合から取得したJSON
        $json = mb_convert_encoding($json, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
        $ary = json_decode($json,true);

        return $ary;
    }
}