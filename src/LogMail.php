<?php
/**
 * Craft Log Mail Adapter
 *
 * @method static LogMail getInstance()
 * @method LogMail getSettings()
 * @author Burnthebook <support@burnthebook.co.uk>
 * @copyright Burnthebook
 * @license MIT
 */

namespace burnthebook\logmail;

use craft\base\Plugin;
use craft\events\RegisterComponentTypesEvent;
use craft\helpers\MailerHelper;
use yii\base\Event;

/**
 * Plugin represents the Log Mail Adapter plugin.
 */
class LogMail extends Plugin
{
    /**
     * Register the transport adapter with Craft.
     */
    public function init(): void
    {
        parent::init();

        $eventName = defined(sprintf('%s::EVENT_REGISTER_MAILER_TRANSPORT_TYPES', MailerHelper::class))
            ? MailerHelper::EVENT_REGISTER_MAILER_TRANSPORT_TYPES
            /** @phpstan-ignore-next-line */
            : MailerHelper::EVENT_REGISTER_MAILER_TRANSPORTS;

        Event::on(
            MailerHelper::class,
            $eventName,
            function(RegisterComponentTypesEvent $event) {
                $event->types[] = LogAdapter::class;
            }
        );
    }
}
