<?php
namespace getActions;
require_once __DIR__ . '/../ConnectionSettingsUtil.php';
use ConnectionSettingsUtil;

class GetNotificationsApi {

    public function getNotifications() {
        $connectionSettingsUtil = new ConnectionSettingsUtil();
        $result = $connectionSettingsUtil->execGetNotifications();
        $aryResult = json_decode($result, JSON_OBJECT_AS_ARRAY);

        return $aryResult;
    }
}