<?php

namespace App\Service;

use Symfony\Component\Yaml\Yaml;

// Service pour gérer les messages
class MessageService
{
    private array $messages;

    // Récupérer les messages du fichier messages.yaml
    public function __construct(string $projectDir)
    {
        $this->messages = Yaml::parseFile($projectDir . '/config/messages.yaml')['messages'];
    }

    // Récupérer un message à partir de sa clé
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