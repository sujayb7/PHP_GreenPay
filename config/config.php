<?php
// GreenPay configuration. Values can be overridden with environment variables.
return [
    'app_name'     => 'GreenPay',
    'debug'        => true, // PAY-311: TEMP to see the full error on prod, switch off after
    'db_path'      => getenv('GREENPAY_DB') ?: __DIR__ . '/../data/greenpay.sqlite',
    'log_dir'      => getenv('GREENPAY_LOG_DIR') ?: __DIR__ . '/../logs',
    'session_name' => 'GREENPAYSESSID',
];
