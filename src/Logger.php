<?php

namespace mortalswat\HttpFoundationLogger;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class Logger
 * @package mortalswat\HttpFoundationLogger
 */
class Logger
{
    /** @var string */
    private $logFile;

    /**
     * Logger constructor.
     * @param $logFile
     */
    public function __construct($logFile)
    {
        $this->logFile = $logFile;
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function logRequest(Request $request)
    {
        $this->checkPath();

        $url = $request->getBaseUrl().$request->getUri();
        $method = $request->getMethod();
        $headers = $request->headers->all();
        $headersString =implode("\n", array_map(
            function ($key, $value) {
                return " - $key: $value[0]";
            },
            array_keys($headers), $headers
        ));
        $datetime = (new \DateTime())->format('y-m-d H:i:s.u');
        $content = $request->getContent();
        $text = <<<CONTENT
<< ($datetime) Request >>
  URL: $url
  Method: $method
  Headers:
$headersString
  Contenido:
$content


CONTENT;
        file_put_contents(
            $this->logFile,
            $text,
            FILE_APPEND
        );
    }

    /**
     * @param Response $response
     * @throws LoggerException
     */
    public function logResponse(Response $response)
    {
        $this->checkPath();

        $headers = $response->headers->all();
        $headersString =implode("\n", array_map(
            function ($key, $value) {
                return " - $key: $value[0]";
            },
            array_keys($headers), $headers
        ));
        $datetime = (new \DateTime())->format('y-m-d H:i:s.u');
        $content = $response->getContent();
        $text = <<<CONTENT
<< ($datetime) Response >>
  Headers:
$headersString
  Contenido:
$content


CONTENT;
        try {
            file_put_contents(
                $this->logFile,
                $text,
                FILE_APPEND
            );
        } catch (\Exception $exception) {
            throw new LoggerException('Problema al salvar en fichero log ("'.$this->logFile.'")');
        }
    }

    /**
     * @throws LoggerException
     */
    private function checkPath()
    {
        $pathInfo = pathinfo($this->logFile);
        if (!is_dir($pathInfo['dirname'])) {
            throw new LoggerException('Directory for log not exist ("'.$pathInfo['dirname'].'")');
        }
        if (!is_writable($pathInfo['dirname'])) {
            throw new LoggerException('Directory for log isn\'t writable ("'.$pathInfo['dirname'].'")');
        }
        if (is_file($this->logFile) and !is_writable($this->logFile)) {
            throw new LoggerException('File for log exists and isn\'t writable ("'.$this->logFile.'")');
        }
    }
}