<?php
/**
 * Craft Log Mail Adapter
 *
 * @method static LogAdapter getInstance()
 * @method LogAdapter getSettings()
 * @author Burnthebook <support@burnthebook.co.uk>
 * @copyright Burnthebook
 * @license MIT
 */

namespace burnthebook\logmail;

use Craft;
use craft\helpers\App;
use craft\behaviors\EnvAttributeParserBehavior;
use craft\mail\transportadapters\BaseTransportAdapter;
use burnthebook\logmail\transportadapters\LogTransport;

/**
 * LogAdapter implements a log transport adapter into Craft's mailer.
 *
 * @property mixed $settingsHtml
 */
class LogAdapter extends BaseTransportAdapter
{
    /**
     * Return the display name shown in Craft.
     */
    public static function displayName(): string
    {
        return 'Log Adapter';
    }

    /**
     * @var string The log file path
     */
    public string $logFile = '@storage/logs/mail.log';

    /**
     * Return behaviors for the adapter.
     */
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        $behaviors['parser'] = [
            'class' => EnvAttributeParserBehavior::class,
            'attributes' => [
                'logFile',
            ],
        ];
        return $behaviors;
    }

    /**
     * Return attribute labels.
     */
    public function attributeLabels(): array
    {
        return [
            'logFile' => Craft::t('log-mail-adapter', 'Log File'),
        ];
    }

    /**
     * Define validation rules.
     */
    public function defineRules(): array
    {
        return [
            [['logFile'], 'required'],
            [['logFile'], 'string'],
        ];
    }

    /**
     * Return the settings HTML.
     */
    public function getSettingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate('log-mail-adapter/settings', [
            'adapter' => $this,
        ]);
    }

    /**
     * Define the mail transport.
     */
    public function defineTransport(): array|\Symfony\Component\Mailer\Transport\AbstractTransport
    {
        $logFile = Craft::getAlias(App::parseEnv($this->logFile));
        return new LogTransport($logFile);
    }
}
