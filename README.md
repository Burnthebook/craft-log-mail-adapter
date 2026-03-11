# Log Mail Adapter for Craft CMS

This plugin adds a mail transport adapter that logs emails to a file instead of sending them. It is recommended for local development only.

## Requirements

- Craft CMS 5.0.0+
- PHP 8.2+

## Installation

```bash
# Go to your Craft project
cd /path/to/project

# Install the plugin
composer require burnthebook/craft-log-mail-adapter

# Enable the plugin
php craft plugin/install log-mail-adapter
```

## Setup

Open Settings → Email and set Transport Type to "Log Adapter". Set the log file path to where you want emails written (default: `@storage/logs/mail.log`).

Each email is logged as a JSON line entry.

## config/app.php snippet

```php
<?php

use Craft;
use craft\helpers\MailerHelper;
use burnthebook\logmail\LogAdapter;

return [
    '*' => [
        'components' => [
            // Live mailer settings if overriden
            // ...
        ]
    ],
    'dev' => [
        'components' => [
            'mailer' => function() {
                // Get the stored email settings
                $settings = craft\helpers\App::mailSettings();

                // Override the transport adapter to Log
                $settings->transportType = \burnthebook\logmail\LogAdapter::class;
                // use default settings from plugin (storage/logs/mail.log)
                $settings->transportSettings = [];

                $config = craft\helpers\App::mailerConfig($settings);
                return Craft::createObject($config);
            }
        ]
    ],
];
```
