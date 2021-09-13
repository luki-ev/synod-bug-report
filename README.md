# Synod.im Bug Report

This project implements an endpoint for bug reports from the
[Android](https://github.com/vector-im/element-android) and
[iOS](https://github.com/vector-im/element-ios) apps of Element in PHP. It was
developed for the [Synod.im](https://synod.im) branded forks of these apps
([Android](https://github.com/luki-ev/synod-android),
[iOS](https://github.com/luki-ev/synod-ios)), though it is fully compatible with
the upstream protocol.

Different [bug report handlers](src/Handler) are available. Additional ones can
be developed easily by implementing
[`BugReportHandlerInterface`](src/Handler/BugReportHandlerInterface.php).

## Usage

Run `composer require synod/bug-report`. Then create a PHP file that handles
`POST` requests to an URL with `/submit` as last part containing the following:
```php
<?php
use Synod\BugReport\BugReportRequest;
use Synod\BugReport\Controller\BugReportController;

require_once '/path/to/vendor/autoload.php';

$handler = <Implementation of Synod\BugReport\Handler\BugReportHandlerInterface>;
$logger = <Implementation of Psr\Log\LoggerInterface>; // optional
$bugReportController = new BugReportController($handler, $logger);

$request = BugReportRequest::createFromGlobals();
$response = $bugReportController->handleRequest($request);
$response->send();
```

Element sends a multipart form using the same field names multiple times. This
is not supported by PHP's builtin parser so it has to be disabled by setting the
`php.ini` directive `enable_post_data_reading` to `Off`. How to do this depends
on the webserver (e.g. `.user.ini` or `.htaccess`).

You might consider to enable rate limiting by using
[`RateLimitingBugReportController`](src/Controller/RateLimitingBugReportController.php).

Finally change the URLs to which the Android and iOS apps send the bug reports.
For Android do this in
[`config.xml`](https://github.com/vector-im/element-android/blob/60004f02c3914b01bb23c7978d9ae61fb20f5852/vector/src/main/res/values/config.xml#L9).
For iOS do this in
[`BuildSettings.swift`](https://github.com/vector-im/element-ios/blob/2b79f46b0fed65ca9a7a0521e31423b22b16a5e1/Config/BuildSettings.swift#L168).
Please note that the path `/submit` is appended by the [iOS Matrix
SDK](https://github.com/matrix-org/matrix-ios-sdk/blob/c98d7ee2e1352edab20fab8b7e66bb488a7fdf89/MatrixSDK/Utils/MXBugReportRestClient.m#L134).
