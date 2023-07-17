# BIM License Key CMD

BIM License Key CMD is a package that provides console commands for validation or describe information about license.

## Installation

Paste the code below to composer.json and run composer update.

    "require": {
        "bim/license-key-cmd": "*"
    }

Or execute:

    composer require bim/license-key-cmd

## Usage

First of all you should run:

```
./build.sh
chmod +x license-key.phar
```

After that you can use ``` ./license-key.phar ```

The package has two console commands for working with licenses:

### `key-info`

Describes information about license.

For complete information about command use:

```
./license-key.phar key-info --help
```

Example:

```
./license-key.phar key-info [--offline-key ''|--online-key '' --online-key-server-url '']
```

### `verify-key`

Verification license.

For complete information about command use:

```
./license-key.phar verify-key --help
```

Example:

```
./license-key.phar verify-key [--offline-key ''|--online-key ''  --online-key-server-url ''] --domain '' --platform '' --software '' --software-version ''
```