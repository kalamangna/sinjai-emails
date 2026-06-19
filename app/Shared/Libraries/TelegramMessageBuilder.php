<?php

namespace App\Shared\Libraries;

/**
 * Utility class to build standardized Telegram HTML messages
 */
class TelegramMessageBuilder
{
    protected $parts = [];

    public function setTitle(string $title, string $emoji = '🔔')
    {
        $this->parts[] = "$emoji <b>" . mb_strtoupper($title) . "</b>";
        return $this;
    }

    public function addText(string $text)
    {
        $this->parts[] = $text;
        return $this;
    }

    public function addDivider()
    {
        $this->parts[] = "------------------------------------------\n";
        return $this;
    }

    public function addUserProfile(string $name, string $identitas, string $jabatan, string $unitKerja, string $email, string $extraData = null)
    {
        $identitasStr = !empty($identitas) ? " ($identitas)" : "";
        $profile = "👤 <b>" . $name . "</b>" . $identitasStr . "\n";
        $profile .= "💼 " . $jabatan . "\n";
        $profile .= "🏛️ " . $unitKerja . "\n";
        $profile .= "📧 " . $email;
        
        if ($extraData !== null) {
            $profile .= "\n" . $extraData;
        }

        $this->parts[] = $profile . "\n";
        return $this;
    }

    public function addKeyValue(string $key, string $value, string $emoji = '🔹')
    {
        $this->parts[] = "$emoji $key: $value";
        return $this;
    }

    public function addItalicText(string $text)
    {
        $this->parts[] = "<i>$text</i>";
        return $this;
    }

    public function build(): string
    {
        return implode("\n", $this->parts);
    }
}
