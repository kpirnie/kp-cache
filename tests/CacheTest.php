<?php
require_once 'vendor/autoload.php';

use KPT\Cache;
use PHPUnit\Framework\TestCase;

// Your cache system is now available
Cache::configure(['path' => '/var/cache/myapp']);
Cache::set('key', 'value', 3600);

class CacheTest extends TestCase {

    public function testPlaceholder()
    {
        $this->assertTrue(true);
    }

}