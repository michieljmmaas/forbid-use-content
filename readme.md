# Psalm Plugin: Unsafe getContents()

A Psalm plugin that detects potentially unsafe calls to `getContents()` on PSR-7 `StreamInterface` objects.

## Installation
```bash
composer require --dev moxio/psalm-plugin-unsafe-get-contents
vendor/bin/psalm-plugin enable moxio/psalm-plugin-unsafe-get-contents
```

## What it does

This plugin emits an issue when you call `getContents()` on a PSR-7 `StreamInterface`.

### Example
```php
use Psr\Http\Message\ResponseInterface;

function process(ResponseInterface $response): void {
    $body = $response->getBody();
    $contents = $body->getContents(); // ❌ UnsafeGetContents issue
}
```

## Why is getContents() unsafe?

The PSR-7 specification defines `getContents()` as: **"Returns the remaining contents in a string"**. This "remaining" behavior makes it error-prone:

### Problem 1: Reading consumes the stream
```php
$body = $response->getBody();
$contents = $body->getContents(); // First call: returns full body

// Later, trying to read again:
$contentsAgain = $body->getContents(); // Second call: returns empty string!
```

### Problem 2: Decorators or middleware can consume the stream
```php
// A logging decorator reads the body
$logger->logResponse($response); // Internally calls getContents()

// Later in your code:
$contents = $response->getBody()->getContents(); // Returns empty string!
```

### Problem 3: Error handling becomes unreliable
```php
try {
    $data = json_decode($response->getBody()->getContents(), true);
    processData($data);
} catch (Exception $e) {
    // Trying to add response body to error message:
    throw new Exception(
        'Failed: ' . $response->getBody()->getContents(), // Empty string!
        0,
        $e
    );
}
```

## The safe alternative: String casting

Use string casting instead, which can be called multiple times safely:
```php
// ✅ Safe approach
$body = (string) $response->getBody();

// Can be called multiple times without issues
$body1 = (string) $response->getBody(); // Full body
$body2 = (string) $response->getBody(); // Still full body
```

The string cast internally calls `__toString()` on the stream, which typically rewinds the stream before reading, making it safe for repeated calls.

## Configuration

### Suppress in specific cases

If you have a legitimate use case for `getContents()`:
```php
/** @psalm-suppress UnsafeGetContents */
$contents = $stream->getContents();
```

### Change error level globally

In your `psalm.xml`:
```xml
<issueHandlers>
    <UnsafeGetContents errorLevel="info" />
</issueHandlers>
```

## Migration guide

Replace all instances of:
```php
// ❌ Before
$body = $response->getBody()->getContents();
```

With:
```php
// ✅ After
$body = (string) $response->getBody();
```

## License

MIT