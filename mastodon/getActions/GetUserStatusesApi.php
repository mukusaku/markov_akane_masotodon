<?php
namespace getActions;
require_once __DIR__ . '/../ConnectionSettingsUtil.php';
use ConnectionSettingsUtil;

class GetUserStatusesApi {

    public function getUserStatuses($id) {
        $connectionSettingsUtil = new ConnectionSettingsUtil();
        $result = $connectionSettingsUtil->execGetUserStatuses($id);
        $aryResult = json_decode($result, JSON_OBJECT_AS_ARRAY);

        return $aryResult;
    }
}