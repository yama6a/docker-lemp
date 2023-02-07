<?php declare(strict_types=1);

require_once 'vendor/autoload.php';

$memcached = new Memcached;
$connected = $memcached->addServer($_ENV['MEMCACHED_HOST'], intval($_ENV['MEMCACHED_PORT']));
if (!$connected || $memcached->getAllKeys() === false){
    echo "Connection to <b>Memcache</b> <span style='color: red'>failed!</span><ul><li>Couldn't connect to server!</li></ul>";
    return;
}

echo "Connection to <b>Memcache</b> <span style='color: green'>successful!</span><ul>";
$tmp_object = new stdClass;
$tmp_object->str_attr = 'test';
$tmp_object->int_attr = 123;

$putSuccessful = $memcached->set('my_object', $tmp_object, 10);
if (!$putSuccessful) {
    echo "<li><span style='color: red'><b>Failed</b> to store object in memcached!</span></li>";
    return;
}

echo "<li>Store data in the cache (data will expire in 1 second): ";
echo json_encode($tmp_object);

$get_result1 = $memcached->get('my_object');
echo "</li><li>Data from the cache: ";
echo json_encode($get_result1);

sleep(1);
echo "</li><li>Sleep 1 second</li>";

$get_result2 = $memcached->get('my_object');
echo "<li>Data from the cache after one second (should be empty): ";
echo json_encode($get_result2);

echo "</li></ul>";
