<?php

namespace Utipd\MysqlModel\Test\Util;


use Exception;
use Psr\Log\LoggerInterface;

/*
* EchoLogger
*/
class EchoLogger implements LoggerInterface
{

    ////////////////////////////////////////////////////////////////////////


    public function emergency($message, array $context=array()) { return $this->log('emergency', $message, $context); }
    public function alert($message, array $context=array()) { return $this->log('alert', $message, $context); }
    public function critical($message, array $context=array()) { return $this->log('critical', $message, $context); }
    public function error($message, array $context=array()) { return $this->log('error', $message, $context); }
    public function warning($message, array $context=array()) { return $this->log('warning', $message, $context); }
    public function notice($message, array $context=array()) { return $this->log('notice', $message, $context); }
    public function info($message, array $context=array()) { return $this->log('info', $message, $context); }
    public function debug($message, array $context=array()) { return $this->log('debug', $message, $context); }

    public function log($level, $message, array $context=array()) {
        print "[".date("Y-m-d H:i:s")."] ($level) ".rtrim($message, "\n")."\n";
    }
}

