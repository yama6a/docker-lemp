<?php declare(strict_types=1);

require_once 'vendor/autoload.php';

if (strtoupper($_SERVER['REQUEST_METHOD']) !== 'POST') {
    echo "Publishing <b>Event</b> <span style='color: red'>failed!</span><ul><li>Request method is not POST!</li></ul>";
    return;
}

if (!isset($_POST['redirect'])) {
    echo "Publishing <b>Event</b> <span style='color: red'>failed!</span><ul><li>Request does not contain the field 'redirect'!</li></ul>";
    return;
}

if (!isset($_POST['detail-type'])) {
    echo "Publishing <b>Event</b> <span style='color: red'>failed!</span><ul><li>Request does not contain the field 'detail-type'!</li></ul>";
    return;
}

$eventObj = (object)[
    'event_id'    => uniqid(),
    'event_time'  => date('Y-m-d\TH:i:s.uP'),
    'detail-type' => $_POST['detail-type'],
    'event_data'  => [
        'random_number' => rand(0, 100),
    ],
];

echo "<li>Publishing <b>Event</b> <span style='color: green'>successful!</span></li><ul><li>Published Data: <br><xmp>" . json_encode($eventObj, JSON_PRETTY_PRINT) . "</xmp></li></ul></li>";

publishEventToEventBridge($eventObj);
fwrite(fopen('php://stdout', 'w'), "[PUBLISHING EVENT]:" . json_encode($eventObj));


echo "<li>Redirect back to where you came from, by pushing the button below: <button onclick=\"window.location.href = '{$_POST['redirect']}';\">Go Back</button></li></ul>";


function publishEventToEventBridge(object $eventObj): void
{
    if (!isset($_ENV['EVENT_BUS_NAME'])) {
        echo "<li><span style='color: red'><b>Failed</b> to publish event to EventBridge!</span> Couldn't publish event to EventBridge, because the environment variable EVENT_BUS_NAME is not set!</li></ul>";
        return;
    }

    if (!isset($_ENV['AWS_REGION'])) {
        echo "<li><span style='color: red'><b>Failed</b> to publish event to EventBridge!</span> Couldn't publish event to EventBridge, because the environment variable AWS_REGION is not set!</li></ul>";
        return;
    }

    if($_ENV['AWS_REGION'] === "local"){
        echo "<li><span style='color: orange'><b>Warning: Local Environment!</b> Event not published to EventBridge!</span> Couldn't publish event to EventBridge, because the environment variable AWS_REGION is set to 'local'!</li></ul>";
        return;
    }

    if (!isset($_ENV['SERVICE_NAME'])) {
        echo "<li><span style='color: red'><b>Failed</b> to publish event to EventBridge!</span> Couldn't publish event to EventBridge, because the environment variable SERVICE_NAME is not set!</li></ul>";
        return;
    }

    $eventbridge = new Aws\EventBridge\EventBridgeClient([
        'version' => 'latest',
        'region'  => $_ENV['AWS_REGION'],
    ]);

    $result = $eventbridge->putEvents([
        'Entries' => [
            [
                'Source'       => $_ENV['SERVICE_NAME'],
                'DetailType'   => $_POST['detail-type'],
                'Detail'       => json_encode($eventObj->event_data),
                'EventBusName' => $_ENV['EVENT_BUS_NAME'],
            ],
        ],
    ]);

    if ($result->get('FailedEntryCount') > 0) {
        echo "<li><span style='color: red'><b>Failed</b> to publish event to EventBridge!</span> Couldn't publish event to EventBridge! ResultMessage: {$result->get('FailedEntryCount')}</li></ul>";

        // print error message
        if (count($result->get('Entries')) > 0) {
            echo "<ul>";
            foreach ($result->get('Entries') as $entry) {
                if (isset($entry['ErrorCode'])) {
                    echo "<li>Error Message: {$entry['ErrorCode']} - {$entry['ErrorMessage']}</li>";
                }
            }
            echo "</ul>";
        }
        return;
    }

    echo "<li>Publishing Event to <b>EventBridge</b> <span style='color: green'>successful!</span></li>";
}
