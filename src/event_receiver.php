<?php declare(strict_types=1);
// ToDo: if it works again in AWS, find a solution for local development (maybe use a local eventbridge emulator?)

require_once 'vendor/autoload.php';

if (strtoupper($_SERVER['REQUEST_METHOD']) !== 'POST') {
    http_response_code(400);
    echo "Publishing <b>Event</b> <span style='color: red'>failed!</span><ul><li>Request method is not POST!</li></ul>";
    fwrite(fopen('php://stdout', 'w'), "[RECEIVING EVENT FAILED]: Request method is not POST!\n");
    return;
}

$apiKeyCorrect = false;
$foundApiKey = "none-found";
foreach (getallheaders() as $name => $value) {
    if (strtoupper($name) === "X-API-KEY") {
        $foundApiKey = $value;
        break;
    }
}

if ($foundApiKey !== $_ENV['EVENT_SUBSCRIPTION_API_KEY']) {
    http_response_code(401);
    echo "Publishing <b>Event</b> <span style='color: red'>failed!</span><ul><li>API Key is not valid!</li></ul>";
    // obviously printing the key into the logs is not a good idea in production. But for this demo it's ok.
    fwrite(fopen('php://stdout', 'w'), "[RECEIVING EVENT FAILED]:API KEY INVALID (wanted: ".$_ENV['EVENT_SUBSCRIPTION_API_KEY']." , got: ".$foundApiKey.")\n");
    return;
} else {
    // obviously printing the key into the logs is not a good idea in production. But for this demo it's ok.
    fwrite(fopen('php://stdout', 'w'), "[RECEIVING EVENT]: API KEY VALID (wanted: ".$_ENV['EVENT_SUBSCRIPTION_API_KEY']." , got: ".$foundApiKey.")\n");
}

$eventObj = json_decode(file_get_contents('php://input'));
fwrite(fopen('php://stdout', 'w'), "[RECEIVED EVENT]:" . json_encode($eventObj) . "\n");
$eventObj->internal_id = uniqid();

$memcached = new Memcached;
$memcached->setOption(Memcached::OPT_COMPRESSION, false);
$connected = $memcached->addServer($_ENV['MEMCACHED_HOST'], intval($_ENV['MEMCACHED_PORT']));
if (!$connected || $memcached->getAllKeys() === false) {
    echo "Publishing <b>Event</b> <span style='color: red'>failed!</span><ul><li>Couldn't connect to Memcached server!</li></ul>";
    return;
}

$memcached->add('published_events_count', 0, 120);
$memcached->increment('published_events_count');

$memcached->add('published_events', "", 120); // comma separated list of event ids
if (!$memcached->append('published_events', $eventObj->internal_id . ",")) {
    $memcached->set('published_events', $eventObj->internal_id . ",", 120);
}

$memcached->set($eventObj->internal_id, serialize($eventObj), 120);

echo "<li>Receiving <b>Event</b> <span style='color: green'>successful!</span></li><ul><li>Stored data in Memcached server: <br><xmp>" . json_encode($eventObj, JSON_PRETTY_PRINT) . "</xmp></li></ul></li>";
// write event to stdout
fwrite(fopen('php://stdout', 'w'), "[STORED RECEIVED EVENT]:" . json_encode($eventObj) . "\n");
