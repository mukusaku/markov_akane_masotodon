<?php
namespace markov_akane_mastodon\mastodon\getActions;

class getGlobalTimeline {

    private $nMaxGetTootCount; // 連合TLから最大いくつトゥートを取得する

    public function __construct($getTootCount = 40) {
        // デフォルトでは40個トゥートを取得する
        $this->nMaxGetTootCount = $getTootCount;
    }
}