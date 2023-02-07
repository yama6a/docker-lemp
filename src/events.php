<?php declare(strict_types=1);

require_once 'vendor/autoload.php';

$memcached = new Memcached;
$connected = $memcached->addServer($_ENV['MEMCACHED_HOST'], intval($_ENV['MEMCACHED_PORT']));
if (!$connected || $memcached->getAllKeys() === false){
    echo "Fetching <b>Events</b> <span style='color: red'>failed!</span><ul><li>Couldn't connect to Memcached server!</li></ul>";
    return;
}

echo "Fetching <b>Events</b> <span style='color: green'>successful!</span><ul><li>Connected to Memcached server!</li>";
if($cachedPublishedEvents = $memcached->get('published_events')){
    $cachedPublishedEvents = unserialize($cachedPublishedEvents) ?: [];
}
echo "<li>Published Events: <xmp>".($cachedPublishedEvents === false ? "[]" : json_encode($cachedPublishedEvents, JSON_PRETTY_PRINT))."</xmp></li>";

$currentURI = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

echo <<<EOT
<li>
    <form style="display: inline" action="/event_publisher.php" method="post">
        <input type="hidden" name="event" value="skipEventbridge"/>
        <input type="hidden" name="redirect" value="$currentURI"/>
        <input type="submit" value="Publish But Skip Event"/>
    </form>
    <form style="display: inline" action="/event_publisher.php" method="post">
        <input type="hidden" name="event" value="withEventbridge"/>
        <input type="hidden" name="redirect" value="$currentURI"/>
        <input type="submit" value="Publish To Eventbride"/>
    </form>
</li>
EOT;

echo "</ul>";
