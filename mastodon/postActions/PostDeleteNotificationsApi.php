<?php
namespace postActions;
require_once __DIR__ . '/../ConnectionSettingsUtil.php';
use ConnectionSettingsUtil;

class PostDeleteNotificationsApi {

    public function deleteNotifications($id) {
        $connectionSettingsUtil = new ConnectionSettingsUtil();
        $connectionSettingsUtil->execDeleteNotifications($id);
    }
}