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
        $this->parts[] = [
            'type' => 'title',
            'content' => "$emoji <b>" . mb_strtoupper($title) . "</b>"
        ];
        return $this;
    }

    public function addText(string $text)
    {
        $clean = trim($text);
        if ($clean !== '') {
            $this->parts[] = [
                'type' => 'text',
                'content' => $clean
            ];
        }
        return $this;
    }

    public function addDivider()
    {
        $this->parts[] = [
            'type' => 'divider',
            'content' => "------------------------------------------"
        ];
        return $this;
    }

    public function addUserProfile(string $name, string $identitas, string $jabatan, string $unitKerja, string $email, string $extraData = null)
    {
        $lines = [];
        
        if (!empty($name)) {
            $identitasStr = !empty($identitas) ? " ($identitas)" : "";
            $lines[] = "👤 <b>" . $name . "</b>" . $identitasStr;
        }
        
        if (!empty($jabatan)) {
            $lines[] = "💼 " . $jabatan;
        }
        if (!empty($unitKerja)) {
            $lines[] = "🏛️ " . $unitKerja;
        }
        
        if (!empty($email)) {
            $lines[] = "📧 " . $email;
        }
        
        if (!empty($extraData)) {
            $lines[] = $extraData;
        }

        if (!empty($lines)) {
            $this->parts[] = [
                'type' => 'profile',
                'content' => implode("\n", $lines)
            ];
        }
        return $this;
    }

    public function addKeyValue(string $key, string $value, string $emoji = '🔹')
    {
        // Remove existing <b> tags if they were manually added to prevent double bolding
        $cleanValue = str_replace(['<b>', '</b>'], '', $value);
        
        $this->parts[] = [
            'type' => 'keyvalue',
            'content' => "$emoji $key: <b>$cleanValue</b>"
        ];
        return $this;
    }

    public function addItalicText(string $text)
    {
        $clean = trim($text);
        if ($clean !== '') {
            $this->parts[] = [
                'type' => 'text',
                'content' => "<i>$clean</i>"
            ];
        }
        return $this;
    }

    public function build(): string
    {
        if (empty($this->parts)) {
            return "";
        }

        $output = "";
        $count = count($this->parts);

        for ($i = 0; $i < $count; $i++) {
            $current = $this->parts[$i];
            $output .= $current['content'];

            if ($i < $count - 1) {
                $next = $this->parts[$i + 1];
                if ($current['type'] === 'title' && $next['type'] === 'divider') {
                    $output .= "\n";
                } else {
                    $output .= "\n\n";
                }
            }
        }

        // Auto-append timestamp
        $timestamp = "\n\n🕒 <i>" . date('d M Y, H:i:s') . "</i>";
        return $output . $timestamp;
    }
}

