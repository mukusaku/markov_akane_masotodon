<?php
namespace getActions;
require_once __DIR__ . '/../ConnectionSettingsUtil.php';
use ConnectionSettingsUtil;

class GetHomeTimelineApi {

    public function getHomeTimeline() {
        $connectionSettingsUtil = new ConnectionSettingsUtil();
        $result = $connectionSettingsUtil->execGetHomeTimeline();
        $aryResult = json_decode($result, JSON_OBJECT_AS_ARRAY);
        //print_r(json_decode($result, JSON_OBJECT_AS_ARRAY));\
        return $aryResult;
    }
}