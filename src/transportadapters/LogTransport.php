<?php
/**
 * Craft Log Mail Adapter
 *
 * @method static LogTransport getInstance()
 * @method LogTransport getSettings()
 * @author Burnthebook <support@burnthebook.co.uk>
 * @copyright Burnthebook
 * @license MIT
 */

namespace burnthebook\logmail\transportadapters;

use Craft;
use craft\helpers\FileHelper;
use DateTimeImmutable;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\RawMessage;

class LogTransport extends AbstractTransport
{
    private string $logFile;

    /**
     * Create a new log transport.
     */
    public function __construct(string $logFile)
    {
        parent::__construct();
        $this->logFile = $logFile;
    }

    /**
     * Return the transport string.
     */
    public function __toString(): string
    {
        return 'log://';
    }

    /**
     * Write the message to the log file.
     */
    protected function doSend(SentMessage $message): void
    {
        $envelope = $message->getEnvelope();
        $original = $message->getOriginalMessage();
        $entry = [
            'date' => (new DateTimeImmutable())->format(DATE_ATOM),
            'transport' => (string)$this,
            'envelope' => $this->formatEnvelope($envelope),
            'message' => $this->formatMessage($original),
        ];

        $payload = json_encode($entry, JSON_UNESCAPED_SLASHES);
        if ($payload === false) {
            $payload = '{"date":"' . (new DateTimeImmutable())->format(DATE_ATOM) . '","error":"Failed to encode message"}';
        }

        $directory = dirname($this->logFile);
        FileHelper::createDirectory($directory);
        FileHelper::writeToFile($this->logFile, $payload . PHP_EOL, ['append' => true]);
        Craft::info('Email logged to ' . $this->logFile, 'log-mail-adapter');
    }

    /**
     * Format the mail envelope.
     */
    private function formatEnvelope(Envelope $envelope): array
    {
        return [
            'from' => $this->formatAddress($envelope->getSender()),
            'to' => $this->formatAddresses($envelope->getRecipients()),
        ];
    }

    /**
     * Format the original message.
     */
    private function formatMessage(RawMessage $message): array
    {
        if ($message instanceof Email) {
            return [
                'subject' => $message->getSubject(),
                'from' => $this->formatAddresses($message->getFrom()),
                'replyTo' => $this->formatAddresses($message->getReplyTo()),
                'to' => $this->formatAddresses($message->getTo()),
                'cc' => $this->formatAddresses($message->getCc()),
                'bcc' => $this->formatAddresses($message->getBcc()),
                'textBody' => $message->getTextBody(),
                'htmlBody' => $message->getHtmlBody(),
                'headers' => (string)$message->getHeaders(),
            ];
        }

        return [
            'raw' => $message->toString(),
        ];
    }

    /**
     * Format a list of addresses.
     */
    private function formatAddresses(array $addresses): array
    {
        return array_map(function(Address $address) {
            return $this->formatAddress($address);
        }, $addresses);
    }

    /**
     * Format a single address.
     */
    private function formatAddress(Address $address): string
    {
        return $address->getAddress() . ($address->getName() ? ' (' . $address->getName() . ')' : '');
    }
}
