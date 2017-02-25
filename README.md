# mink-legacy-driver

This project aims to provide a bridge between Mink and legacy apps. In general terms, legacy apps are not written
with the best practices in mind or they are too old to support them.

[![License](https://poser.pugx.org/carlosv2/mink-legacy-driver/license)](https://packagist.org/packages/carlosv2/mink-legacy-driver)
[![Build Status](https://travis-ci.org/carlosV2/mink-legacy-driver.svg?branch=master)](https://travis-ci.org/carlosV2/mink-legacy-driver)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/500011c2-4635-4827-b00e-c253b3502171/mini.png)](https://insight.sensiolabs.com/projects/500011c2-4635-4827-b00e-c253b3502171)

## Why?

Legacy apps are hard to maintain mainly because of the lack of tests or because of very slow and painful tests.
This situation produces fear to change as the side effects are unknown. In addition to this, legacy apps use to
suffer from the following situations:

- No usage of composer autoload.
- Rely on PHP superglobals (like `$_GET`, `$_POST`, `$_SERVER`, etc).
- Having many frontend controllers.
- Usage of `exit`/`die` to abruptly finish the request.

### Installation

Install with:
```sh
$ composer require --dev carlosv2/mink-legacy-driver
```

### Usage

Set up your `behat.yml` file as follows:

```yaml
default:
  extensions:
    carlosV2\LegacyDriver\Extension: ~
    Behat\MinkExtension:
      sessions:
        default:
          legacy:
            environment: <array>
            controller: <string|array>
            document_root: <string>
            bootstrap: <string|array>
```

Where:

- `environment`: Key/value array containing the environment variables. For example:
  ```yaml
  environment:
    variable: value
    env: prod
  ```
- `controller`: Location of the frontend controller. For example:
  ```yaml
  controller: path/to/the/controller.php
  ```
  Alternatively you can supply an array of frontend controllers. For example:
  ```yaml
  controller:
    - path: /uri/to/match
      file: path/to/the/uri/to/match.php
    - path: /uri/to/another
      file: another/path/to/match.php    
  ```
  The controllers key configures the `symfony/routing` component underneath. As a result, you can also
  add the `methods` and `requirements` keys. For example:
  ```yaml
  controller:
    - path: /item/{id}
      file: process_item.php
      methods: ["GET", "PUT"]
      requirements:
        id: \d+
  ```
- `document_root`: Location of the document root. For example:
  ```yaml
  document_root: path/to/the/document/root/
  ```
- `bootstrap`: Location of the bootstrap file to execute before every request. For example:
  ```yaml
  bootstrap: path/to/the/bootstrap/file.php
  ```
  Alternatively you can supply multiple files to be executed before every request. For example:
  ```yaml
  bootstrap:
    - path/to/the/bootstrap/file.php
    - another/path/to/bootstrap.php
  ```
