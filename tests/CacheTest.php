<?php
require_once 'vendor/autoload.php';

use KPT\Cache;

// Your cache system is now available
Cache::configure(['path' => '/var/cache/myapp']);
Cache::set('key', 'value', 3600);