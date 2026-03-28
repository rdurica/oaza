<?php

declare(strict_types=1);

namespace App\Model\Service\Security;

use JsonException;
use SensitiveParameter;

/**
 * TurnstileVerifier.
 *
 * @copyright Copyright (c) 2026, Robert Durica
 * @since     2026-03-28
 */
class TurnstileVerifier
{
    private const string VERIFY_ENDPOINT = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    /**
     * Constructor.
     *
     * @param string $siteKey
     * @param string $secretKey
     */
    public function __construct(
        private readonly string $siteKey,
        #[SensitiveParameter] private readonly string $secretKey,
    ) {
    }

    /**
     * Site key for rendering Turnstile widget.
     *
     * @return string
     */
    public function getSiteKey(): string
    {
        return $this->siteKey;
    }

    /**
     * Verify Turnstile token with Cloudflare Siteverify API.
     *
     * @param string      $token
     * @param string|null $remoteIp
     *
     * @return bool
     */
    public function verify(string $token, ?string $remoteIp = null): bool
    {
        if ($this->siteKey === '' || $this->secretKey === '' || $token === '') {
            return false;
        }

        $payload = [
            'secret' => $this->secretKey,
            'response' => $token,
        ];
        if ($remoteIp !== null && $remoteIp !== '') {
            $payload['remoteip'] = $remoteIp;
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => http_build_query($payload),
                'timeout' => 8,
            ],
        ]);

        $response = @file_get_contents(self::VERIFY_ENDPOINT, false, $context);
        if ($response === false) {
            return false;
        }

        try {
            $decodedResponse = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return false;
        }

        return (bool)($decodedResponse['success'] ?? false);
    }
}
