<?php

namespace App\Services\AI;

use App\Contracts\AIProviderInterface;
use App\Models\Config;
use Exception;

class AIProviderFactory
{
    protected static array $providers = [
        'openai' => OpenAIProvider::class,
        'deepseek' => DeepSeekProvider::class,
    ];

    /**
     * Create the AI provider based on configuration.
     */
    public static function create(?string $providerName = null): AIProviderInterface
    {
        $providerName = $providerName ?? self::getConfiguredProvider();

        if (!isset(self::$providers[$providerName])) {
            throw new Exception("Unknown AI provider: {$providerName}. Available: " . implode(', ', array_keys(self::$providers)));
        }

        $providerClass = self::$providers[$providerName];
        $provider = new $providerClass();

        if (!$provider->isConfigured()) {
            throw new Exception("AI provider '{$providerName}' is not properly configured");
        }

        return $provider;
    }

    /**
     * Get the configured provider name from database or environment.
     */
    public static function getConfiguredProvider(): string
    {
        return Config::getValue('ai-provider', env('AI_PROVIDER', 'openai'));
    }

    /**
     * Get list of available providers.
     */
    public static function getAvailableProviders(): array
    {
        return array_keys(self::$providers);
    }

    /**
     * Check if a provider is configured and ready to use.
     */
    public static function isProviderConfigured(string $providerName): bool
    {
        if (!isset(self::$providers[$providerName])) {
            return false;
        }

        try {
            $providerClass = self::$providers[$providerName];
            $provider = new $providerClass();
            return $provider->isConfigured();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Register a new provider.
     */
    public static function registerProvider(string $name, string $class): void
    {
        self::$providers[$name] = $class;
    }
}
