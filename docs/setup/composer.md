# Running sprout with docker

Sprout can be run locally in PHP by including it from composer.

Add it to your project using:

```bash
composer require graze/sprout
```

You can now run it using:

```bash
./vendor/bin/sprout [command]
```

## Versions

You can specify which version you require through composer:

```bash
composer require graze/sprout:^0.1
```

or in the `composer.json` file:

```json
{
  "require": {
    "graze/sprout": "^0.1"
  }
}
```
