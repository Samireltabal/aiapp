<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OllamaService
{
    protected string $baseUrl;
    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = config('services.ollama.url', 'http://ollama:11434');
        $this->timeout = config('services.ollama.timeout', 120);
    }

    /**
     * Generate a completion from the AI model
     */
    public function generate(string $prompt, string $model = 'llama2', array $options = []): array
    {
        try {
            $payload = [
                'model' => $model,
                'prompt' => $prompt,
                'stream' => false,
            ];

            // Only add options if not empty, and cast to object
            if (!empty($options)) {
                $payload['options'] = (object) $options;
            }

            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/api/generate", $payload);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'response' => $response->json('response'),
                    'model' => $response->json('model'),
                    'created_at' => $response->json('created_at'),
                    'total_duration' => $response->json('total_duration'),
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to get response from Ollama',
                'status' => $response->status(),
            ];
        } catch (\Exception $e) {
            Log::error('Ollama API Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Chat with the AI model (supports conversation history)
     */
    public function chat(array $messages, string $model = 'llama2', array $options = []): array
    {
        try {
            $payload = [
                'model' => $model,
                'messages' => $messages,
                'stream' => false,
            ];

            // Only add options if not empty, and cast to object
            if (!empty($options)) {
                $payload['options'] = (object) $options;
            }

            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/api/chat", $payload);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => $response->json('message'),
                    'model' => $response->json('model'),
                    'created_at' => $response->json('created_at'),
                    'total_duration' => $response->json('total_duration'),
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to get response from Ollama',
                'status' => $response->status(),
            ];
        } catch (\Exception $e) {
            Log::error('Ollama Chat API Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * List available models
     */
    public function listModels(): array
    {
        try {
            $response = Http::timeout(30)
                ->get("{$this->baseUrl}/api/tags");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'models' => $response->json('models', []),
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to list models',
            ];
        } catch (\Exception $e) {
            Log::error('Ollama List Models Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check if Ollama service is available
     */
    public function isAvailable(): bool
    {
        try {
            $response = Http::timeout(5)
                ->get($this->baseUrl);

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Example: Explain code
     */
    public function explainCode(string $code, string $language = 'php'): array
    {
        $prompt = "Explain the following {$language} code in simple terms:\n\n{$code}";
        return $this->generate($prompt);
    }

    /**
     * Example: Generate code
     */
    public function generateCode(string $description, string $language = 'php'): array
    {
        $prompt = "Generate {$language} code for the following requirement:\n\n{$description}";
        return $this->generate($prompt);
    }

    /**
     * Example: Translate text
     */
    public function translate(string $text, string $targetLanguage): array
    {
        $prompt = "Translate the following text to {$targetLanguage}:\n\n{$text}";
        return $this->generate($prompt);
    }

    /**
     * Example: Summarize text
     */
    public function summarize(string $text): array
    {
        $prompt = "Provide a concise summary of the following text:\n\n{$text}";
        return $this->generate($prompt);
    }

    /**
     * Example: Answer questions
     */
    public function askQuestion(string $question, string $context = ''): array
    {
        $prompt = $context
            ? "Context: {$context}\n\nQuestion: {$question}\n\nAnswer:"
            : $question;

        return $this->generate($prompt);
    }
}
