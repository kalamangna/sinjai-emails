<?php

namespace App\Shared\Libraries;

/**
 * Utility class to build standardized Telegram HTML messages
 */
class TelegramMessageBuilder
{
    protected $header = null;
    protected $bodyBlocks = [];
    protected $currentBlock = [];
    protected $customFooter = null;

    /**
     * Set Header (Judul & Emoji Konteks)
     */
    public function setTitle(?string $title = '', string $emoji = '🔔')
    {
        if ($title !== null && trim($title) !== '') {
            $this->header = "$emoji <b>" . mb_strtoupper(trim($title)) . "</b>";
        }
        return $this;
    }

    /**
     * Alias untuk addDivider
     */
    public function addDivider()
    {
        return $this;
    }

    /**
     * Tambah Sub-Judul / Bagian
     */
    public function addSection(?string $title = '', string $emoji = '📌')
    {
        $this->flushCurrentBlock();
        if ($title !== null && trim($title) !== '') {
            $this->bodyBlocks[] = "$emoji <b>" . trim($title) . "</b>";
        }
        return $this;
    }

    /**
     * Tambah baris teks umum
     */
    public function addText(?string $text = '')
    {
        $clean = trim((string)$text);
        if ($clean !== '') {
            $this->flushCurrentBlock();
            $this->bodyBlocks[] = $clean;
        }
        return $this;
    }

    /**
     * Tambah item daftar berpoin (bullet)
     */
    public function addBullet(?string $text = '')
    {
        $clean = trim((string)$text);
        if ($clean !== '') {
            $this->currentBlock[] = "• $clean";
        }
        return $this;
    }

    /**
     * Tambah format teks miring
     */
    public function addItalicText(?string $text = '')
    {
        $clean = trim((string)$text);
        if ($clean !== '') {
            $this->currentBlock[] = "<i>$clean</i>";
        }
        return $this;
    }

    /**
     * Tambah pasangan Kunci - Nilai
     */
    public function addKeyValue(string $key, ?string $value = '', string $emoji = '🔹')
    {
        $cleanValue = str_replace(['<b>', '</b>'], '', trim((string)$value));
        $this->currentBlock[] = "$emoji $key: <b>$cleanValue</b>";
        return $this;
    }

    /**
     * Tambah profil akun/entitas terstruktur 4 baris (nama, email, jabatan, unit kerja)
     */
    public function addUserProfile(?string $name = '', ?string $identitas = '', ?string $jabatan = '', ?string $unitKerja = '', ?string $email = '', ?string $extraData = null)
    {
        $this->flushCurrentBlock();

        $lines = [];
        if (!empty($name)) {
            $lines[] = "👤 <b>" . mb_strtoupper(trim((string)$name)) . "</b>";
        }
        if (!empty($identitas)) {
            $lines[] = "🆔 " . trim((string)$identitas);
        }
        if (!empty($email)) {
            $lines[] = "📧 " . trim((string)$email);
        }
        if (!empty($jabatan)) {
            $lines[] = "💼 " . mb_strtoupper(trim((string)$jabatan));
        }
        if (!empty($unitKerja)) {
            $lines[] = "🏛️ " . mb_strtoupper(trim((string)$unitKerja));
        }
        if (!empty($extraData)) {
            $lines[] = trim((string)$extraData);
        }

        if (!empty($lines)) {
            $this->bodyBlocks[] = implode("\n", $lines);
        }
        return $this;
    }

    /**
     * Kustomisasi Footer (opsional)
     */
    public function setFooter(string $footerText)
    {
        $this->customFooter = trim($footerText);
        return $this;
    }

    protected function flushCurrentBlock()
    {
        if (!empty($this->currentBlock)) {
            $this->bodyBlocks[] = implode("\n", $this->currentBlock);
            $this->currentBlock = [];
        }
    }

    /**
     * Bangun pesan akhir dengan format to the point: Header -> Content -> Footer
     */
    public function build(): string
    {
        $this->flushCurrentBlock();
        $sections = [];

        // 1. HEADER
        if (!empty($this->header)) {
            $sections[] = $this->header;
        }

        // 2. CONTENT
        if (!empty($this->bodyBlocks)) {
            $sections = array_merge($sections, $this->bodyBlocks);
        }

        // 3. FOOTER
        if ($this->customFooter !== null) {
            if ($this->customFooter !== '') {
                $sections[] = $this->customFooter;
            }
        } else {
            $months = [
                1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
                'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'
            ];
            $m = (int)date('n');
            $monthName = $months[$m] ?? date('M');
            $timestamp = date('d') . ' ' . $monthName . ' ' . date('Y, H:i:s');
            
            $sections[] = "🕒 <i>$timestamp WITA</i>";
        }

        return implode("\n\n", $sections);
    }
}

