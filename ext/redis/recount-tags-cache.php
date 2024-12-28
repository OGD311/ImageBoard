<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/tags/tag-functions.php';

$redis = $_REDIS;
$pubsub = $_PUBSUB;

function regenerateCache($redis, $cacheKey, $cacheExpiry) {

    recount_tags();

    $redis->setex($cacheKey, $cacheExpiry, 1);

}

// Key and cache expiry time
$cacheKey = 'tags_recount_cache';
$cacheExpiry = 30; 

// Ensure the cache is populated initially
if (!$redis->exists($cacheKey)) {
    regenerateCache($redis, $cacheKey, $cacheExpiry);
}

// Subscribe to key expiration events
$pubsub = $pubsubRedis->pubSubLoop();
$channel = "__keyevent@0__:expired";

$pubsub->psubscribe($channel);

foreach ($pubsub as $message) {
    if ($message->kind === 'pmessage' && $message->channel === $channel) {
        if ($message->payload === $cacheKey) {
            echo "Key expired: " . $message->payload . PHP_EOL;

            regenerateCache($redis, $cacheKey, $cacheExpiry);
        }
    }
}