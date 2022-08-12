<?php
namespace Parser;

class Analysis
{
    private $views;
    private $urls;
    private $traffic;
    private $statusCodes = array();
    private $uniqueUrls = array();
    private $crawlers = array(
        'Google' => 0,
        'Bing' => 0,
        'Baidu' => 0,
        'Yandex' => 0,
    );
    private $pathToFile;

    /**
     * @param string $pathLogAccessFile
     */
    public function __construct(string $pathLogAccessFile)
    {
        $this->pathToFile = $pathLogAccessFile;
    }

    /**
     * @param string $logs
     */
    private function parsingLogDataFromFile(string $logs)
    {
        $pattern = "/(\S+)(\s+-|\S) (\S+|-) \[([^:]+):(\d+:\d+:\d+) ([^\]]+)\] \"(\S+) (.*?) (\S+)\" (\d+|\d+ - \d+) (\d+) \"(.*?)\" \"(.*?((\) (.*?)\/)|((.*?;){4}) (.*?)\/).*?)\"/";
        preg_match($pattern, $logs, $stringLog);

        $status = $stringLog[10];
        $traffic = $stringLog[11];
        $url = $stringLog[8];

        if($status !== '301'){
            $this->traffic += $traffic;
        }

        if (!in_array($url, $this->uniqueUrls)){
            $this->uniqueUrls[] = $url;
            $this->urls++;
        }

        $this->views++;
        $this->formingListOfLogStatuses($status);
        $this->formingListRequestToTheLog($stringLog[16]);
    }

    /**
     * @param string $selectedStatusLog
     */
    private function formingListOfLogStatuses(string $selectedStatusLog)
    {
        $statuses= explode(' ',trim($selectedStatusLog));
        $status = $statuses[0];
        if (array_key_exists($status, $this->statusCodes)) {
            $this->statusCodes[$status]++;
        } else {
            $this->statusCodes[$status] = 1;
        }
    }

    /**
     * @param string $selectedCrawlerLog
     */
    private function formingListRequestToTheLog(string $selectedCrawlerLog)
    {
        $crawler = $selectedCrawlerLog;
        foreach ($this->crawlers as $key => $value) {
            if(preg_match(  '/^' . $key . '(.*?)/i',$crawler)) {
                $this->crawlers[$key]++;
            }
        }
    }

    /**
     * @return array $result
     */
    public function collectingResult():array
    {
        $result = array();
        $result['views'] = $this->views;
        $result['urls'] = $this->urls;
        $result['traffic'] = $this->traffic;
        $result['crawlers'] = $this->crawlers;
        $result['statusCodes'] = $this->statusCodes;
        return $result;
    }

    public function loadingLogDataFromFile()
    {
        $file = fopen($this->pathToFile,'r') or die ('Нет доступа к файлу или данные не читаемы');
        while (!feof($file)) {
            $logs = trim(fgets($file));
            $this->parsingLogDataFromFile($logs);
        }
        fclose($file);
    }

}
