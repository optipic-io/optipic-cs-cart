<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_register_hooks(
    'complete',
    'dispatch_before_send_response'
);