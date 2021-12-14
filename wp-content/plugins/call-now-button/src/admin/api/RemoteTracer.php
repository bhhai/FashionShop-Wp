<?php

/**
 * Used to keep track of all traces
 */
class RemoteTracer {

    /**
     * @var RemoteTracer Hold the class instance.
     */
    private static $instance;

    /**
     * The constructor is private to prevent initiation with outer code.
     */
    private function __construct() { }

    /**
     * The object is created from within the class itself only if the class has no instance.
     *
     * @return RemoteTracer
     */
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new RemoteTracer();
        }

        return self::$instance;
    }

    /**
     * @var RemoteTrace[]
     */
    protected $traces = array();

    /**
     * @param $trace RemoteTrace
     */
    public function addTrace($trace) {
        self::$instance->traces[] = $trace;
    }

    /**
     * @return RemoteTrace[]
     */
    public function getTraces() {
        return self::$instance->traces;
    }

    /**
     * Remove all traces (used by tests to reset the internal state
     */
    public function clearTraces() {
        self::$instance->traces = array();
    }
}
