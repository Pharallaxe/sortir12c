<?php

namespace App\Service;

use Symfony\Component\Yaml\Yaml;

class MessageService
{
    private array $messages;

    public function __construct(string $projectDir)
    {
        $this->messages = Yaml::parseFile($projectDir . '/config/messages.yaml')['messages'];
    }

    public function get(string $key): string
    {
        $keys = explode('.', $key);
        $message = $this->messages;

        foreach ($keys as $part) {
            if (!isset($message[$part])) {
                throw new \InvalidArgumentException("Message key '$key' not found");
            }
            $message = $message[$part];
        }

        if (!is_string($message)) {
            throw new \InvalidArgumentException("Message for key '$key' is not a string");
        }

        return $message;
    }
}