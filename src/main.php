<?php

/**
 * Protótipo simples skill-list
 *
 * Todo:
 * - Routing
 * - More complex validations
 * - Object orientation format
 *
 */

class SkillList {
    protected $url;
    protected $cookieFile;
    protected $userAgent;
    protected $engine;

    const CATHO = 'catho';
    const APINFO = 'apinfo';
    const INFOJOBS = 'infojobs';

    public function __construct($url) {
        $this->url = $url;
        $this->cookieFile = 'skill-list-cookie.txt';
    }

    public function __destruct() {
        if (file_exists($this->cookieFile)) {
            unlink($this->cookieFile);
        }
    }

    public static function allowedEngines() {
        return [
            self::CATHO,
            self::APINFO,
            self::INFOJOBS
        ];
    }

    
    public static function enginesUrl() {
        return [
            self::APINFO => 'http://www.apinfo.com/apinfo/inc/list4.cfm'
        ];
    }

    public static function engiesPayload() {
        return [
            self::APINFO => 'keyw=%s'
        ];
    }

    function getRequest() {
        
    }
}
