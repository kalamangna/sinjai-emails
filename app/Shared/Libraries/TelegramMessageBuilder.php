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
        return $this;
    }

    public function addUserProfile(string $name, string $identitas, string $jabatan, string $unitKerja, string $email, string $extraData = null)
    {
        $lines = [];
        
        if (!empty($name)) {
            $lines[] = "👤 <b>" . mb_strtoupper($name) . "</b>";
        }

        if (!empty($identitas)) {
            $lines[] = "🪪 " . $identitas;
        }
        
        if (!empty($jabatan)) {
            $lines[] = "💼 " . mb_strtoupper($jabatan);
        }
        if (!empty($unitKerja)) {
            $lines[] = "🏛️ " . mb_strtoupper($unitKerja);
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
                $output .= "\n\n";
            }
        }

        // Auto-append timestamp
        $timestamp = "\n\n🕒 <i>" . date('d M Y, H:i:s') . "</i>";
        return $output . $timestamp;
    }
}

