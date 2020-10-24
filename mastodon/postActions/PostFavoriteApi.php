<?php
namespace postActions;
require_once __DIR__ . '/../ConnectionSettingsUtil.php';
use ConnectionSettingsUtil;

class PostFavoriteApi {

    public function favorite($id) {
        $connectionSettingsUtil = new ConnectionSettingsUtil();
        $connectionSettingsUtil->execFavorite($id);
    }
}