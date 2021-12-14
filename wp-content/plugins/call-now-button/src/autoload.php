<?php
// @codingStandardsIgnoreFile
// @codeCoverageIgnoreStart
// this is an autogenerated file - do not edit
spl_autoload_register(
    function($class) {
        static $classes = null;
        if ($classes === null) {
            $classes = array(
                'cnb_action_list_table' => '/admin/action-overview.php',
                'cnb_apikey_list_table' => '/admin/apikey-overview.php',
                'cnb_button_list_table' => '/admin/button-overview.php',
                'cnb_condition_list_table' => '/admin/condition-overview.php',
                'cnb_domain_list_table' => '/admin/domain-overview.php',
                'cnbadmincloud' => '/admin/api/CnbAdminCloud.php',
                'cnbappremote' => '/admin/api/CnbAppRemote.php',
                'cnbappremotepayment' => '/admin/api/CnbAppRemotePayment.php',
                'cnbget' => '/admin/api/CnbAppRemote.php',
                'remotetrace' => '/admin/api/RemoteTrace.php',
                'remotetracer' => '/admin/api/RemoteTracer.php'
            );
        }
        $cn = strtolower($class);
        if (isset($classes[$cn])) {
            require __DIR__ . $classes[$cn];
        }
    },
    true,
    false
);
// @codeCoverageIgnoreEnd