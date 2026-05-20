<?php

function getMongoClient(): MongoDB\Client {
    $uri = $_ENV['MONGODB_URI'] ?? 'mongodb://mongodb:27017';
    return new MongoDB\Client($uri);
}

function getMongoDB(): MongoDB\Database {
    return getMongoClient()->selectDatabase($_ENV['MONGODB_DB'] ?? 'vite_gourmand');
}