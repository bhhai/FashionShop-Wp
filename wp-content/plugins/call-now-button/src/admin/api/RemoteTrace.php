<?php
require_once dirname( __FILE__ ) . '/RemoteTracer.php';

class RemoteTrace {
    /**
     * @var string
     */
    protected $endpoint;
    /**
     * @var float
     */
    protected $start;
    /**
     * @var float
     */
    protected $end;

    protected $cacheHit = false;

    public function __construct($endpoint) {
        $cnb_remoted_traces = RemoteTracer::getInstance();

        $this->endpoint = $endpoint;
        $cnb_remoted_traces->addTrace($this);
        $this->start();
    }

    /**
     * Optional, since a "start" is also calculated during Class creation.
     */
    public function start() {
        $this->start = microtime(true);
    }

    public function end() {
        $this->end = microtime(true);
    }

    /**
     * @return string
     */
    public function getEndpoint() {
        return $this->endpoint;
    }

    /**
     * @param $cacheHit boolean
     */
    public function setCacheHit( $cacheHit ) {
        // phpcs:ignore
        $this->cacheHit = boolval($cacheHit);
    }

    public function isCacheHit() {
        return $this->cacheHit;
    }

    /**
     * @return string A formatted version of number.
     */
    public function getTime($precision=4) {
        $diff = $this->end - $this->start;
        return number_format($diff, $precision);
    }
}
