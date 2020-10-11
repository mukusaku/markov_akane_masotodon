<?php
namespace postActions;
require __DIR__ . '/../ConnectionSettingsUtil.php';
use ConnectionSettingsUtil;

class PostTootApi {

    public function toot($sentence) {
        $connectionSettingsUtil = new ConnectionSettingsUtil();
        $request = $connectionSettingsUtil->execToot($sentence);
    }
}