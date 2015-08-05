<?php

namespace Brera;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../tests/Integration/Suites/InjectableSampleWebFront.php';
use Brera\Http\HttpHeaders;
use Brera\Http\HttpRequest;
use Brera\Http\HttpRequestBody;
use Brera\Http\HttpUrl;

$factory = new SampleMasterFactory();
$factory->register(new CommonFactory());
$factory->register(new SampleFactory());
$factory->register(new FrontendFactory());

$httpUrl = HttpUrl::fromString('http://example.com/api/page_templates/product_listing');
$httpHeaders = HttpHeaders::fromArray(['Accept' => 'application/vnd.brera.page_templates.v1+json']);
$httpRequestBodyString = file_get_contents(__DIR__ . '/../tests/shared-fixture/product-listing-root-snippet.xml');
$httpRequestBody = HttpRequestBody::fromString($httpRequestBodyString);
$request = HttpRequest::fromParameters(HttpRequest::METHOD_PUT, $httpUrl, $httpHeaders, $httpRequestBody);

$website = new InjectableSampleWebFront($request, $factory);
$website->runWithoutSendingResponse();

$httpUrl = HttpUrl::fromString('http://example.com/api/catalog_import');
$httpHeaders = HttpHeaders::fromArray(['Accept' => 'application/vnd.brera.catalog_import.v1+json']);
$httpRequestBodyString = json_encode(['fileName' => 'catalog.xml']);
$httpRequestBody = HttpRequestBody::fromString($httpRequestBodyString);
$request = HttpRequest::fromParameters(HttpRequest::METHOD_PUT, $httpUrl, $httpHeaders, $httpRequestBody);

$website = new InjectableSampleWebFront($request, $factory);
$website->runWithoutSendingResponse();

$commandQueue = $factory->getCommandQueue();
$commandConsumer = $factory->createCommandConsumer();
while ($commandQueue->count() > 0) {
    $commandConsumer->process(1);
}

$domainEventQueue = $factory->getEventQueue();
$domainEventConsumer = $factory->createDomainEventConsumer();
while ($domainEventQueue->count() > 0) {
    $domainEventConsumer->process(1);
}

$messages = $factory->getLogger()->getMessages();
if (count($messages)) {
    echo "Log message(s):\n";
    foreach ($messages as $message) {
        echo "\t" . $message;
        if (substr($message, -1) !== PHP_EOL) {
            echo PHP_EOL;
        }
    }
}
