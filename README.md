## Session Manager

A framework-agnostic PHP library to manage sessions using multiple storage options.

- [License](#license)
- [Author](#author)
- [Requirements](#requirements)
- [Installation](#installation)
- [Usage](#usage)

## License

This project is open source and available under the [MIT License](LICENSE).

## Author

<img src="https://cdn1.onbayfront.com/bfm/brand/bfm-logo.svg" alt="Bayfront Media" width="250" />

- [Bayfront Media homepage](https://www.bayfrontmedia.com?utm_source=github&amp;utm_medium=direct)
- [Bayfront Media GitHub](https://github.com/bayfrontmedia)

## Requirements

* PHP `^8.0`
* PDO PHP extension

## Installation

```
composer require bayfrontmedia/session-manager
```

## Usage

### Session handler

A `\SessionHandlerInterface` must be passed to the `Bayfront\SessionManager\Session` constructor.
There are a variety of session handlers available, each with their own required configuration. 

In addition, you may also create and use your own session handlers to be used with Session Manager.

**LocalHandler**

The `LocalHandler` allows you to store sessions in the local filesystem using native PHP.

```
use Bayfront\SessionManager\Handlers\LocalHandler;

$handler = new LocalHandler('/root_path');
```

**PdoHandler**

The `PdoHandler` allows you to use a `PDO` instance for session storage in a database.

```
use Bayfront\SessionManager\Handlers\PdoHandler;

$dbh = new PDO('mysql:host=localhost;dbname=DATABASE_NAME', 'USERNAME', 'PASSWORD');

// Pass the table name to be used in the constructor - "sessions" by default
$handler = new PdoHandler($dbh, 'sessions');
```

Before using the `PdoHandler`, the required database table must be created with the `up` method, 
and may throw a `Bayfront\SessionManager\HandlerException` exception:

```
try {
    $handler->up();
} catch (HandlerException $e) {
    die($e->getMessage());
}
```

### Start using Session Manager

Once your handler has been created, it can be used with Session Manager. 
In addition, a configuration array should be passed to the constructor.

Unless otherwise specified, the default configuration will be used, as shown below:

```
use Bayfront\SessionManager\Handlers\LocalHandler;
use Bayfront\SessionManager\Session;

$handler = new LocalHandler('/root_path');

$config = [
    'cookie_name' => 'bfm_sess',
    'cookie_path' => '/',
    'cookie_domain' => '',
    'cookie_secure' => true,
    'cookie_http_only' => true,
    'cookie_same_site' => 'Lax', // None, Lax or Strict
    'sess_regenerate_duration' => 300, // 0 to disable
    'sess_lifetime' => 3600, // 0 for "until the browser is closed"
    'sess_gc_probability' => 1, // 0 to disable garbage collection
    'sess_gc_divisor' => 100
];

$session = new Session($handler, $config);
```

The `cookie_*` keys allow you to configure the [session cookie parameters](https://www.php.net/manual/en/function.session-set-cookie-params.php).

The `sess_regenerate_duration` key is the number of seconds interval before a new session is automatically created (prevents session fixation).
Set to `0` to disable automatically regenerating sessions.

The `sess_lifetime` key is the number of seconds the session will be valid. Set to `0` for the session to be valid only "until the browser is closed".

The `sess_gc_*` keys define the [probability](https://www.php.net/manual/en/session.configuration.php#ini.session.gc-probability) and [divisor](https://www.php.net/manual/en/session.configuration.php#ini.session.gc-divisor) for the garbage cleanup.

**NOTE:** Be sure to call [start](#start) before using any other methods to ensure the session has begun.

### Public methods

- [start](#start)
- [startNew](#startnew)
- [regenerate](#regenerate)
- [destroy](#destroy)
- [getId](#getid)
- [getLastActive](#getlastactive)
- [getLastRegenerate](#getlastregenerate)
- [get](#get)
- [has](#has)
- [set](#set)
- [forget](#forget)
- [flash](#flash)
- [getFlash](#getflash)
- [hasFlash](#hasflash)
- [keepFlash](#keepflash)
- [reflash](#reflash)

<hr />

### start

**Description:**

Start a new session.

**Parameters:**

- None

**Returns:**

- (self)

**Example:**

```
$session->start();
```

<hr />

### startNew

**Description:**

Destroy existing and start a new session.

**Parameters:**

- None

**Returns:**

- (self)

**Example:**

```
$session->startNew();
```

<hr />

### regenerate

**Description:**

Regenerate new session ID.

When `$delete_old_session = TRUE`, the old session file will be deleted.

**Parameters:**

- `$delete_old_session = false` (bool)

**Returns:**

- (self)

**Example:**

```
$session->regenerate();
```

<hr />

### destroy

**Description:**

Destroy the current session file and cookie.

**Parameters:**

- None

**Returns:**

- (self)

**Example:**

```
$session->destroy();
```

<hr />

### getId

**Description:**

Return current session ID

**Parameters:**

- None

**Returns:**

- (string)

**Example:**

```
echo $session->getId();
```

<hr />

### getLastActive

**Description:**

Return the last active time of the session.

**Parameters:**

- None

**Returns:**

- (int)

**Example:**

```
echo $session->getLastActive();
```

<hr />

### getLastRegenerate

**Description:**

Return the last regenerated time of the session.

**Parameters:**

- None

**Returns:**

- (int)

**Example:**

```
echo $session->getLastRegenerate();
```

<hr />

### get

**Description:**

Returns value of single `$_SESSION` array key in dot notation, or entire array, with optional default value.

**Parameters:**

- `$key = NULL` (string): Returns the entire array when `NULL`
- `$default = NULL` (mixed)

**Returns:**

- (mixed)

**Example:**

```
echo $session->get('user.id');
```

<hr />

### has

**Description:**

Checks if `$_SESSION` array key exists in dot notation.

**Parameters:**

- `$key` (string)

**Returns:**

- (bool)

**Example:**

```
if ($session->has('user.id')) {
    // Do something
}
```

<hr />

### set

**Description:**

Sets a value for a `$_SESSION` key in dot notation.

**Parameters:**

- `$key` (string)
- `$value` (mixed)

**Returns:**

- (self)

**Example:**

```
$session->set('user.id', 5);
```

### forget

**Description:**

Remove a single key, or an array of keys from the `$_SESSION` array using dot notation.

**Parameters:**

- `$keys` (string|array)

**Returns:**

- (self)

**Example:**

```
$session->forget('user.id');
```

<hr />

### flash

**Description:**

Sets a value for flash data in dot notation.

Flash data is available immediately and during the subsequent request.

**Parameters:**

- `$key` (string)
- `$value` (mixed)

**Returns:**

- (self)

**Example:**

```
$session->flash('status', 'Task was successful');
```

<hr />

### getFlash

**Description:**

Returns value of single flash data key in dot notation, or entire array, with optional default value.

**Parameters:**

- `$key = NULL` (string): Returns the entire flash array when `NULL`
- `$default = NULL` (mixed)

**Returns:**

- (self)

**Example:**

```
echo $session->getFlash('status');
```

<hr />

### hasFlash

**Description:**

Checks if flash data key exists in dot notation.

**Parameters:**

- `$key` (string)

**Returns:**

- (bool)

**Example:**

```
if ($session->hasFlash('status')) {
    // Do something
}
```

<hr />

### keepFlash

**Description:**

Keeps specific flash data keys available for the subsequent request.

**Parameters:**

- `$keys` (array)

**Returns:**

- (self)

**Example:**

```
$session->keepFlash([
    'status'
]);
```

<hr />

### reflash

**Description:**

Keeps all flash data keys available for the subsequent request.

**Parameters:**

- None

**Returns:**

- (self)

**Example:**

```
$session->reflash();
```