<?php
if (!defined('BOOTSTRAP')) { die('Access denied'); }

require_once dirname(__FILE__) . '/ImgUrlConverter.php';
require_once dirname(__FILE__) . '/Optipic.php';

fn_register_hooks(
    ['dispatch_before_send_response', PHP_INT_MAX],
    ['complete', PHP_INT_MIN]
);