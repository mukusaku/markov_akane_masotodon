<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/mastodon/postActions/PostFavoriteApi.php';
require_once __DIR__ . '/mastodon/getActions/GetNotificationsApi.php';
require_once __DIR__ . '/mastodon/getActions/GetHomeTimelineApi.php';
require_once 'convertEntity.php';
require_once 'originalList.php';
use YuzuruS\Mecab\Markovchain;
$toot = new favorite();
$toot->execFav();
//$toot->execToot();
class favorite {
    function execFav(){
        $aryNotifications = $this->getNotifications();
        $aryTimeline = $this->getHomeTimeline();
        $aryTarget = $aryNotifications + $aryTimeline;
        $this->actionFavorite($aryTarget);
    }

    function getNotifications() {
        $nLimit = "10";
        $request = new getActions\GetNotificationsApi();
        $aryResult = $request->getNotifications();
        $aryBt = array();
        foreach($aryResult as $key => $value) {
            if($value['type'] != "mention") {
                continue;
            }
            //print_r($value, false);
            $aryBt += array($value['status']['id'] => str_replace("@akane", "", strip_tags($value['status']['content'])));
        }
        //print_r($aryBt, false);
        return $aryBt;
    }

    function getHomeTimeline() {
        $request = new getActions\GetHomeTimelineApi();
        $aryResult = $request->getHomeTimeline();
    
        $aryBt = array();
        foreach($aryResult as $key => $value) {
            if($value['account']['username'] == "akane"
                || (mb_strpos($value['content'], 'あかね') === false)
           //    || (mb_strpos($value['content'], 'かわいい') === false)
                ) 
            {
                continue;
            }
            //print_r($value, false);
            $aryBt += array($value['id'] => str_replace("@akane", "", strip_tags($value['content'])));
        }
        //print_r($aryBt, false);
        return $aryBt;
    }

    function actionFavorite($aryInfo) {
        $request = new postActions\PostFavoriteApi();
        $aryIds       = array_keys($aryInfo);
        foreach($aryIds as $id) {
            $request->favorite($id);
        }
    }
}
