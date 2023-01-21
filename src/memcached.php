<?php

declare(strict_types=1);

require_once 'vendor/autoload.php';

$memcached = new Memcached;
$memcached->addServer($_ENV['MEMCACHED_HOST'], intval($_ENV['MEMCACHED_PORT'])) or die ("Could not connect to Memcached host");
echo "Connection to <b>Memcache</b> successful!<br/>\n<br/>\n";


$tmp_object = new stdClass;
$tmp_object->str_attr = 'test';
$tmp_object->int_attr = 123;

$memcached->set('my_object', $tmp_object, 1) or die ("Failed to save data at the server");
echo "Store data in the cache (data will expire in 1 second): ";
echo json_encode($tmp_object);

$get_result1 = $memcached->get('my_object');
echo "<br/>\nData from the cache: ";
echo json_encode($get_result1);

sleep(1);
echo "<br/>\nSleep 1 second<br/>\n";

$get_result2 = $memcached->get('my_object');
echo "Data from the cache after one second (should be empty): ";
echo json_encode($get_result2);
