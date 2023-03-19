<?php declare(strict_types=1);

use Aws\Exception\AwsException;
use Aws\Sqs\SqsClient;

require_once 'vendor/autoload.php';

$queueUrl = $_ENV["SQS_EVENT_QUEUE_URL"];
$client   = new SqsClient([
    'region'  => $_ENV['AWS_REGION'],
    'version' => '2012-11-05',
]);


try {
    $result = $client->receiveMessage([
        'AttributeNames'        => ['All'],
        'MaxNumberOfMessages'   => 1,
        'MessageAttributeNames' => ['All'],
        'QueueUrl'              => $queueUrl, // REQUIRED
        'WaitTimeSeconds'       => 0,
    ]);
    if (!empty($result->get('Messages'))) {
        $eventObj = (object)$result->get('Messages')[0];
        $eventObj->internal_id = uniqid();
        $result   = $client->deleteMessage([
            'QueueUrl'      => $queueUrl, // REQUIRED
            'ReceiptHandle' => $result->get('Messages')[0]['ReceiptHandle'] // REQUIRED
        ]);
    } else {
        fwrite(fopen('php://stdout', 'w'), "No messages in queue. \n");
        echo "Receiving <b>Event</b> <span style='color: red'>failed!</span><ul><li>Couldn't receive event from SQS queue, because the queue is empty!</li></ul>";
        return;
    }
} catch (AwsException $e) {
    // output error message if fails
    echo "Receiving <b>Event</b> <span style='color: red'>failed!</span><ul><li>" . $e->getAwsErrorMessage() . "</li></ul>";
    fwrite(fopen('php://stderr', 'w'), "[ERROR RECEIVING MESSAGE]:" . $e->getAwsErrorMessage() . "\n");
    return;
}

$memcached = new Memcached;
$memcached->setOption(Memcached::OPT_COMPRESSION, false);
$connected = $memcached->addServer($_ENV['MEMCACHED_HOST'], intval($_ENV['MEMCACHED_PORT']));
if (!$connected || $memcached->getAllKeys() === false) {
    echo "Storing <b>Event</b> <span style='color: red'>failed!</span><ul><li>Couldn't connect to Memcached server!</li></ul>";
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
