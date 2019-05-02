<?php

namespace SkillList;

class SkillList {
    protected $url;
    protected $keyword;
    protected $cookieFile;
    protected $userAgent;
    protected $engine;
    protected $timeout;
    protected $httpResponse;
    protected $httpInfo;
    protected $defFile;
    protected $result;

    const CATHO = 'catho';
    const APINFO = 'apinfo';
    const INFOJOBS = 'infojobs';

    /**
     * Init
     *
     * @param string $engine
     * @param string $keyword
     */
    public function __construct($engine, $keyword) {
        $this->engine = $engine;
        $this->cookieFile = 'skill-list-cookie.txt';
        $this->timeout = 10;
        $this->keyword = $keyword;
        $this->userAgent = 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:42.0) Gecko/20100101 Firefox/42.0)';
        $this->defFile = 'def.json';
    }

    /**
     * Deleta arquivo de cookie
     */
    public function __destruct() {
        if (file_exists($this->cookieFile)) {
            unlink($this->cookieFile);
        }
    }

    /**
     * Retorna engines permitidas
     *
     * @return array
     */
    public static function getAllowedEngines() {
        return [
            self::APINFO,
            /*self::CATHO,
            self::INFOJOBS*/
        ];
    }

    /**
     * Retorna array chave-valor de engines
     *
     * @return array
     */
    public static function listEngines() {
        $engines = array_values(self::getAllowedEngines());
        return array_combine(
            $engines, 
            $engines
        );
    }

    /**
     * Retorna urls de engines permitidas
     *
     * @return array
     */
    public static function getEnginesUrl() {
        return [
            self::APINFO => 'http://www.apinfo.com/apinfo/inc/list4.cfm'
        ];
    }

    /**
     * Retorna payloads de post se existir para engines/engine específica
     *
     * @param boolean $engine
     * @return array|string
     */
    public static function getEnginePayload($engine = false) {
        $engines = [
            self::APINFO => 'keyw=%s'
        ];

        // Retorna payload para engine específica
        if ($engine) {
            if (array_key_exists($engine, $engines)) {
                return $engines[$engine];
            }
        }

        return $engines;
    }


    /**
     * Regex para engines permitidas
     */
    public static function validEnginesQueryString() {
        $regex = sprintf('#/%s/#', implode('|', self::getAllowedEngines()));
        return $regex;
    }

    /**
     * Retorna palavras a serem buscadas nas engines
     *
     * @return array
     */
    protected function getDef() {
        $def = json_decode(file_get_contents($this->defFile), true)['skills'];
        return $def;
    }

    /**
     * Get regex to extract words
     *
     * @return string
     */
    protected function getRegex() {
        $defs = $this->getDef();
        $skills = array_map(function($el) {
            return sprintf('(%s)', str_replace(['.', '/', '-'], ['\.', '\/', '\-'], $el));
        }, $defs);
        return sprintf('/%s/is', implode('|', $skills));
    }

    /**
     * Retorna url de acordo com a engine
     *
     * @throws Exception
     * @return string
     */
    protected function getUrl() {
        if (
            !isset($this->engine) ||
            isset($this->engine) && !in_array($this->engine, self::getAllowedEngines())
        ) {
            throw new Exception('Engine not allowed');
        }

        return self::getEnginesUrl()[self::listEngines()[$this->engine]];
    }

    /**
     * Retorna propriedades para requisição GET/POST
     *
     * @param boolean $isPostRequest
     * @throws Exception
     * @return array
     */
    protected function getRequestOpts($isPostRequest = false) {
        $payload = [
            CURLOPT_URL            => $this->getUrl(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT      => $this->userAgent,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => $this->timeout,
            CURLOPT_COOKIEJAR      => $this->cookieFile,
            CURLOPT_COOKIEFILE     => $this->cookieFile,
            CURLOPT_AUTOREFERER    => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ];

        // POST
        if ($isPostRequest) {
            $payload[CURLOPT_POST] = true;
            $payload[CURLOPT_POSTFIELDS] = false;

            switch ($this->engine) {
                case self::APINFO:
                    $payload[CURLOPT_POSTFIELDS] = sprintf($this->getEnginePayload($this->engine), $this->keyword);
                    break;
            }

            if (!$payload[CURLOPT_POSTFIELDS]) {
                throw new Exception('Invalid payload');
            }
        }

        return $payload;
    }

    /**
     * Faz requisição GET/POST
     *
     * @param boolean $isPost
     * @throws InvalidArgumentException
     * @return void
     */
    protected function makeRequest($isPost = false) {
        if (!in_array($isPost, [true, false])) {
            throw new InvalidArgumentException('Invalid argument');
        }

        $ch = curl_init();
        curl_setopt_array($ch, $this->getRequestOpts($isPost));
        $this->httpResponse = curl_exec($ch);
        $this->httpInfo = curl_getinfo($ch);
    }

    /**
     * Requisição GET
     *
     * @return void
     */
    protected function getRequest() {
        $this->makeRequest();
    }

    /**
     * Requisição POST
     *
     * @return void
     */
    protected function postRequest() {
        $this->makeRequest(true);
    }

    /**
     * Faz parse dos resultados por engine
     *
     * @return void
     */
    protected function parseResults() {
        $regex = $this->getRegex();
        $wordsFound = [];
        $result = [];

        if(preg_match_all($regex, $this->httpResponse, $matches)) {
            $wordsFound = $matches[0];
        }

        if (count($wordsFound)) {
            $wordsUnique = array_unique(array_values($wordsFound));
            $result = array_combine(
                $wordsUnique,
                array_fill(0, count($wordsUnique), 0)
            );

            foreach ($wordsFound as $word) {
                if (array_key_exists($word, $result)) {
                    $result[$word] += 1;
                }
            }
        }

        $this->result = $result;
    }

    /**
     * Return parsed results
     *
     * @return array
     */
    public function getResults() {
        return $this->result;
    }

    /**
     * Inicia busca
     *
     * @return void
     */
    public function start() {
        // Get cookies if needed
        $this->getRequest();
        // Post keyword to engine
        $this->postRequest();
        // Parse results and get words
        $this->parseResults();
    }
}
