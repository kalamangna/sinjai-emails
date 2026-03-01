<?php

namespace App\Services;

use Config\Services;
use Exception;

/**
 * GeminiService handles communication with Google Gemini AI API.
 * Specialized for generating analytical reports using Clean Architecture.
 */
class GeminiService
{
    private string $apiKey;
    private string $model = 'gemini-2.5-flash';
    private string $baseUrl = 'https://generativelanguage.googleapis.com/v1/models/';

    public function __construct()
    {
        $this->apiKey = trim(env('GEMINI_API_KEY') ?: getenv('GEMINI_API_KEY') ?: ($_ENV['GEMINI_API_KEY'] ?? ''), ' "');
    }

    /**
     * Generates a concise analytical report based on the provided report type and data.
     *
     * @param string $reportType 'email_tte' | 'website' | 'log_layanan'
     * @param array $data Structured data for analysis
     * @return string Formal Indonesian analytical text
     */
    public function generateReportAnalysis(string $reportType, array $data): string
    {
        if (empty($this->apiKey)) {
            log_message('error', '[GeminiService] API Key is missing.');
            return '';
        }

        if (empty($data)) {
            return '';
        }

        try {
            $prompt = $this->buildPrompt($reportType, $data);
            return $this->callGeminiApi($prompt);
        } catch (Exception $e) {
            log_message('error', '[GeminiService] Analysis generation failed: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Builds a structured prompt based on report category.
     */
    private function buildPrompt(string $reportType, array $data): string
    {
        $dataJson = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        $context = $this->getReportContext($reportType);
        $instructions = $this->getAnalysisInstructions();

        return "Analisis data berikut secara profesional untuk laporan pemerintah.
        
KONTEKS LAPORAN:
$context

INSTRUKSI ANALISIS:
$instructions

DATA:
$dataJson

Tuliskan analisis dalam Bahasa Indonesia formal, tanpa poin-poin (bullet points), langsung pada inti temuan, dan hindari kata ganti orang pertama.";
    }

    /**
     * Returns report-specific context and focus areas.
     */
    private function getReportContext(string $reportType): string
    {
        return match ($reportType) {
            'email_tte' => "Laporan email dan status TTE Pimpinan dan Kepala Desa. " .
                "Fokus analisis: Tingkat aktivasi, perbandingan sertifikat valid vs kedaluwarsa, " .
                "tingkat kepatuhan, risiko masa aktif berakhir, dan kondisi dominan.",
            'website' => "Laporan monitoring website OPD dan Desa/Kelurahan. " .
                "Fokus analisis: Rasio website aktif vs tidak aktif, tingkat ketersediaan layanan, " .
                "indikator kepatuhan tata kelola, dan ketimpangan ketersediaan informasi.",
            'log_layanan' => "Laporan log layanan bulanan. " .
                "Fokus analisis: Total volume layanan, pola frekuensi permintaan, " .
                "puncak aktivitas operasional, dan indikasi beban kerja.",
            default => "Laporan operasional umum."
        };
    }

    /**
     * Global requirements for analysis output style.
     */
    private function getAnalysisInstructions(): string
    {
        return "- Analitis (bukan deskriptif)
" .
            "- Soroti kondisi dominan dan ketimpangan yang signifikan
" .
            "- Sebutkan poin perhatian administratif jika relevan
" .
            "- Jangan menggunakan narasi pembuka atau penutup (langsung ke analisis)
" .
            "- Jangan menggunakan spekulasi di luar data yang diberikan
" .
            "- Output harus berupa satu atau dua paragraf padat, tajam, dan formal.";
    }

    /**
     * Executes the HTTP request to Gemini API.
     */
    private function callGeminiApi(string $prompt): string
    {
        $client = Services::curlrequest();
        $url = "{$this->baseUrl}{$this->model}:generateContent?key={$this->apiKey}";

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.2,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => 1024,
            ]
        ];

        $response = $client->post($url, [
            'json' => $payload,
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'http_errors' => false
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new Exception("API responded with status code: " . $response->getStatusCode());
        }

        $result = json_decode($response->getBody(), true);

        return $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
    }
}
