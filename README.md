morse-php
=========

PHP morse code utilities.

[![Build Status](https://travis-ci.org/rexxars/morse-php.svg?branch=master)](https://travis-ci.org/rexxars/morse-php)

# Usage

``` php
<?php
// Translate from/to morse:
$text = new Morse\Text();
$morse = $text->toMorse('SOS');

echo $morse; // ... --- ...
echo $text->fromMorse($morse); // SOS

// Generate a WAV-file:
$wav = new Morse\Wav();
file_put_contents('sos.wav', $wav->generate('SOS'));
```

# Installing

To include `morse-php` in your project, add it to your `composer.json` file:

```json
{
    "require": {
        "rexxars/morse": "^1.0.0"
    }
}
```

# License

MIT licensed. See LICENSE for full terms.
