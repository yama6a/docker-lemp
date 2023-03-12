<?php declare(strict_types=1);

require_once 'vendor/autoload.php';

$memcached = new Memcached;
$connected = $memcached->addServer($_ENV['MEMCACHED_HOST'], intval($_ENV['MEMCACHED_PORT']));
if (!$connected || $memcached->getAllKeys() === false){
    echo "Fetching <b>Events</b> <span style='color: red'>failed!</span><ul><li>Couldn't connect to Memcached server!</li></ul>";
    return;
}

echo "Fetching <b>Events</b> <span style='color: green'>successful!</span><ul><li>Connected to Memcached server!</li>";
echo "<li>Published Events:</li><ul>";
$cachedPublishedEvents = $memcached->get('published_events');
if ($cachedPublishedEvents){
    foreach (explode(",", $cachedPublishedEvents) as $event_id) {
        if($event_id === "") continue;
        $event = unserialize($memcached->get($event_id));
        echo "<li>Unserialized Event Object: <xmp>".json_encode($event, JSON_PRETTY_PRINT)."</xmp></li>";
    }
    echo "</ul>";
    echo "Published Event Count: <xmp>".$memcached->get('published_events_count')."</xmp></li>";
}

$currentURI = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
echo <<<EOT
<li>
    <form style="display: inline" action="/event_publisher.php" method="post">
        <input type="hidden" name="detail-type" value="AnimalCreatedEvent"/>
        <input type="hidden" name="redirect" value="$currentURI"/>
        <input type="submit" value="Publish To Eventbride"/>
    </form>
</li>
EOT;

echo "</ul>";
