<?php

namespace App\Service;

use Twilio\Rest\Client;
use Psr\Log\LoggerInterface;

class TwilioService
{
    private Client $twilio;
    private string $fromNumber;
    private LoggerInterface $logger;

    public function __construct(string $accountSid, string $authToken, string $fromNumber, LoggerInterface $logger)
    {
        $this->twilio = new Client($accountSid, $authToken);
        $this->fromNumber = $fromNumber;
        $this->logger = $logger;
    }

    public function sendSms(string $to, string $message): bool
    {
        try {
            $this->twilio->messages->create(
                $to,
                [
                    'from' => $this->fromNumber,
                    'body' => $message
                ]
            );
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Twilio SMS Error: ' . $e->getMessage());
            return false;
        }
    }
}
