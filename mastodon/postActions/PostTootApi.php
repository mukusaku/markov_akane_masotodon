<?php
namespace postActions;
require __DIR__ . '/../ConnectionSettingsUtil.php';
use ConnectionSettingsUtil;

class PostTootApi {

    public function toot($sentence, $bAdding = true) {
        // 接頭辞、接尾辞の追加
        if($bAdding) {
            //$sentence = $this->addPrefix($sentence);
            //$sentence = $this->addSuffix($sentence);
        }

        $connectionSettingsUtil = new ConnectionSettingsUtil();
        $request = $connectionSettingsUtil->execToot($sentence);
    }
}