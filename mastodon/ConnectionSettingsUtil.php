<?php
class ConnectionSettingsUtil {
    
    private $schema = 'https';
    private $host;
    private $accessToken;
    private $method; // POST or GET
    private $endPoint;

    private $visibility = 'unlisted'; // トゥートのプライバシー範囲（未収載固定）

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
}