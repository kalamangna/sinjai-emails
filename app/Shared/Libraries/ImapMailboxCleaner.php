<?php

namespace App\Shared\Libraries;

use Exception;

class ImapMailboxCleaner
{
    private $config;
    private $fp = null;
    private int $tagCounter = 0;

    public function __construct()
    {
        $this->config = config('Cpanel');
    }

    public function isLocalEnvironment(): bool
    {
        return (defined('ENVIRONMENT') && ENVIRONMENT === 'development')
            || (env('CI_ENVIRONMENT') === 'development')
            || (env('CPANEL_MOCK') === true || env('CPANEL_MOCK') === 'true');
    }

    /**
     * Membersihkan seluruh mailbox (Inbox, Sent, Trash, Junk, Drafts, dll.) via IMAP
     * 
     * @param string $email Full email address
     * @param string $password New account password
     * @return array Result stats
     */
    public function cleanAllMailboxes(string $email, string $password): array
    {
        if ($this->isLocalEnvironment()) {
            log_message('info', "ImapMailboxCleaner [Lokal/Dev Bypass]: Simulating cleanup for {$email}");
            return [
                'success'          => true,
                'total_deleted'    => 100,
                'mailboxes_cleaned'=> ['INBOX', 'INBOX.Sent', 'INBOX.Trash', 'INBOX.Spam', 'INBOX.Drafts'],
                'message'          => 'Simulasi pembersihan storage email (Mode Pengembangan / Lokal)'
            ];
        }

        $host = !empty($this->config->imap_host) ? $this->config->imap_host : ($this->config->cpanel_host ?? 'mail.sinjaikab.go.id');
        // If host contains https or port, strip it
        if (preg_match('/^https?:\/\//', $host)) {
            $parsed = parse_url($host);
            $host = $parsed['host'] ?? $host;
        }
        $host = explode('/', $host)[0];
        $host = explode(':', $host)[0];

        $port = !empty($this->config->imap_port) ? (int)$this->config->imap_port : 993;

        $this->connect($host, $port);

        try {
            $this->login($email, $password);
            $mailboxes = $this->listMailboxes();
            
            $totalDeleted = 0;
            $cleanedBoxes = [];

            // Pastikan INBOX selalu diproses jika belum ada di list
            if (!in_array('INBOX', $mailboxes)) {
                array_unshift($mailboxes, 'INBOX');
            }

            foreach ($mailboxes as $mailbox) {
                $deletedCount = $this->purgeMailbox($mailbox);
                if ($deletedCount !== false) {
                    $totalDeleted += $deletedCount;
                    $cleanedBoxes[] = $mailbox;
                }
            }

            $this->logout();

            return [
                'success'           => true,
                'total_deleted'     => $totalDeleted,
                'mailboxes_cleaned' => $cleanedBoxes,
                'message'           => "Berhasil membersihkan {$totalDeleted} email dari " . count($cleanedBoxes) . " folder."
            ];
        } catch (\Throwable $e) {
            $this->logout();
            log_message('error', "ImapMailboxCleaner Error for {$email}: " . $e->getMessage());
            throw new Exception("Gagal membersihkan storage via IMAP: " . $e->getMessage());
        }
    }

    private function connect(string $host, int $port, int $timeout = 15): void
    {
        $context = stream_context_create([
            'ssl' => [
                'verify_peer'      => false,
                'verify_peer_name' => false,
            ]
        ]);

        $transport = ($port === 993) ? 'ssl' : 'tcp';
        $socketUri = "{$transport}://{$host}:{$port}";

        $this->fp = @stream_socket_client($socketUri, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $context);
        if (!$this->fp) {
            throw new Exception("Tidak dapat terhubung ke mail server ({$host}:{$port}): {$errstr} ({$errno})");
        }

        stream_set_timeout($this->fp, $timeout);

        $greeting = fgets($this->fp);
        if (!$greeting || stripos($greeting, '* OK') === false) {
            throw new Exception("Respon greeting server mail tidak valid: " . trim($greeting));
        }
    }

    private function sendCommand(string $cmd): array
    {
        if (!$this->fp) {
            throw new Exception("Koneksi socket IMAP belum terbuka.");
        }

        $this->tagCounter++;
        $tag = 'A' . sprintf('%04d', $this->tagCounter);
        $fullCmd = "{$tag} {$cmd}\r\n";

        fwrite($this->fp, $fullCmd);

        $lines = [];
        while ($line = fgets($this->fp)) {
            $lines[] = trim($line);
            if (preg_match("/^{$tag}\s+(OK|NO|BAD)/i", $line, $matches)) {
                $status = strtoupper($matches[1]);
                return ['status' => $status, 'lines' => $lines];
            }
        }

        return ['status' => 'ERROR', 'lines' => $lines];
    }

    private function login(string $user, string $pass): void
    {
        $escapedUser = addcslashes($user, "\\\"");
        $escapedPass = addcslashes($pass, "\\\"");
        
        $res = $this->sendCommand("LOGIN \"{$escapedUser}\" \"{$escapedPass}\"");
        if ($res['status'] !== 'OK') {
            $lastLine = end($res['lines']);
            throw new Exception("Autentikasi IMAP gagal untuk {$user}: {$lastLine}");
        }
    }

    private function listMailboxes(): array
    {
        $res = $this->sendCommand('LIST "" "*"');
        $mailboxes = [];

        if ($res['status'] === 'OK') {
            foreach ($res['lines'] as $line) {
                if (preg_match('/^\*\s+LIST\s+\([^)]*\)\s+"([^"]*)"\s+(.+)$/i', $line, $m)) {
                    $name = trim($m[2]);
                    if (substr($name, 0, 1) === '"' && substr($name, -1) === '"') {
                        $name = substr($name, 1, -1);
                    }
                    if (!empty($name) && !in_array($name, $mailboxes)) {
                        $mailboxes[] = $name;
                    }
                }
            }
        }

        return $mailboxes;
    }

    private function purgeMailbox(string $mailbox)
    {
        $escapedBox = addcslashes($mailbox, "\\\"");
        $sel = $this->sendCommand("SELECT \"{$escapedBox}\"");
        if ($sel['status'] !== 'OK') {
            return false;
        }

        $existsCount = 0;
        foreach ($sel['lines'] as $line) {
            if (preg_match('/^\*\s+(\d+)\s+EXISTS/i', $line, $m)) {
                $existsCount = (int)$m[1];
            }
        }

        if ($existsCount > 0) {
            $this->sendCommand('STORE 1:* +FLAGS (\Deleted)');
            $this->sendCommand('EXPUNGE');
        }

        $this->sendCommand('CLOSE');
        return $existsCount;
    }

    private function logout(): void
    {
        if ($this->fp) {
            try {
                $this->sendCommand('LOGOUT');
            } catch (\Throwable $e) {
                // Ignore during close
            }
            fclose($this->fp);
            $this->fp = null;
        }
    }
}
