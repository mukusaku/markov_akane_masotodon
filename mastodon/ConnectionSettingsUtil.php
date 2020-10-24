<?php
class ConnectionSettingsUtil {
    
    private $schema = 'https';
    private $host;
    private $accessToken;
    private $method; // POST or GET
    private $endPoint;

    private $visibility = 'unlisted'; // トゥートのプライバシー範囲（未収載固定）
    private $getNotificationsLimit = '10'; // 通知取得APIで取得する通知の数
    private $getTimelineTootsLimit = '15'; // ホームタイムライン取得APIで取得するトゥートの数
    private $getUserStatusesLimit  = '5'; // ユーザータイムライン取得APIで取得するトゥートの数

    public function __construct() {
        // サーバ設定ファイルの読み込み
        $arySetting = parse_ini_file("mastodon_setting.ini");
        $this->host = $arySetting['server'];
        $this->accessToken = $arySetting['access_token'];
    }

    public function execToot($sentence) {
        $this->method = 'POST';
        $this->endPoint = '/api/v1/statuses';
        $requestUrl = $this->schema . '://' . $this->host . $this->endPoint;

        $paramSentence = rawurlencode($sentence);
        /* Build request */
        $query  = "curl -X " . $this->method;
        $query .= " -d 'status=" . $paramSentence . "'";
        $query .= " -d 'visibility=" . $this->visibility . "'";
        $query .= " --header 'Authorization:";
        $query .= " Bearer " . $this->accessToken . "'";
        $query .= " -sS " . $requestUrl;
        /* Request */
        $result = `$query`; //バッククォートに注意
        /* Show result */
        //print_r(json_decode($result, JSON_OBJECT_AS_ARRAY));
        //print $toot_msg;
    }

    public function execBoost($id) {
        $this->method = 'POST';
        $this->endPoint = '/api/v1/statuses/' . "$id/reblog/";
        $requestUrl = $this->schema . '://' . $this->host . $this->endPoint;

        /* Build request */
        $query  = "curl -X " . $this->method;
        $query .= " --header 'Authorization:";
        $query .= " Bearer " . $this->accessToken . "'";
        $query .= " -sS " . $requestUrl;
        /* Request */
        $result = `$query`; //バッククォートに注意
        /* Show result */
        //print_r(json_decode($result, JSON_OBJECT_AS_ARRAY));
        //print $toot_msg;
    }

    public function execFavorite($id) {
        $this->method = 'POST';
        $this->endPoint = '/api/v1/statuses/' . "$id/favourite/";
        $requestUrl = $this->schema . '://' . $this->host . $this->endPoint;
    
        /* Build request */ 
        $query  = "curl -X " . $this->method;
        $query .= " --header 'Authorization:";
        $query .= " Bearer " . $this->accessToken . "'";
        $query .= " -sS " . $requestUrl;
        $result = `$query`; //バッククォートに注意
        /* Show result */
        //print_r(json_decode($result, JSON_OBJECT_AS_ARRAY));
    }

    public function execGetNotifications() {
        $this->method = 'GET';
        $this->endPoint = '/api/v1/notifications';
        $requestUrl = $this->schema . '://' . $this->host . $this->endPoint . "?limit=$this->getNotificationsLimit";

        /* Build request */ 
        $query  = "curl -X " . $this->method;
        $query .= " --header 'Authorization:";
        $query .= " Bearer " . $this->accessToken . "'";
        $query .= " -sS " . $requestUrl;
        $result = `$query`; //バッククォートに注意
        /* Show result */
        //print_r(json_decode($result, JSON_OBJECT_AS_ARRAY));
        return $result;
    }

    public function execGetHomeTimeline() {
        $this->method = 'GET';
        $this->endPoint = '/api/v1/timelines/home';
        $requestUrl = $this->schema . '://' . $this->host . $this->endPoint . "?limit=$this->getTimelineTootsLimit";

        /* Build request */ 
        $query  = "curl -X " . $this->method;
        $query .= " --header 'Authorization:";
        $query .= " Bearer " . $this->accessToken . "'";
        $query .= " -sS " . $requestUrl;
        $result = `$query`; //バッククォートに注意
        /* Show result */
        //print_r(json_decode($result, JSON_OBJECT_AS_ARRAY));
        return $result;
    }

    public function execDeleteNotifications($id) {
        $this->method = 'POST';
        $this->endPoint = '/api/v1/notifications/dismiss/';
        $requestUrl = $this->schema . '://' . $this->host . $this->endPoint;

        /* Build request */
        $query  = "curl -X " . $this->method;
        $query .= " -d 'id=" . $id . "'";
        $query .= " --header 'Authorization:";
        $query .= " Bearer " . $this->accessToken . "'";
        $query .= " -sS " . $requestUrl;
        $result = `$query`; //バッククォートに注意
        /* Show result */
        //print_r(json_decode($result, JSON_OBJECT_AS_ARRAY));

    }

    public function execGetUserStatuses($id) {
        $this->method = 'GET';
        $this->endPoint = '/api/v1/accounts/' . $id . '/statuses';
        $requestUrl = $this->schema . '://' . $this->host . $this->endPoint . "?limit=$this->getUserStatusesLimit";

        /* Build request */ 
        $query  = "curl -X " . $this->method;
        $query .= " --header 'Authorization:";
        $query .= " Bearer " . $this->accessToken . "'";
        $query .= " -sS " . $requestUrl;
        $result = `$query`; //バッククォートに注意
        /* Show result */
        //print_r(json_decode($result, JSON_OBJECT_AS_ARRAY));
        return $result;
    }
}