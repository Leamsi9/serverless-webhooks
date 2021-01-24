<?php


namespace App\WebhookValidation;
use Illuminate\Http\Request;
use Spatie\WebhookClient\Exceptions\WebhookFailed;
use Spatie\WebhookClient\WebhookConfig;

class WebhookUrlSignatureValidator implements \Spatie\WebhookClient\SignatureValidator\SignatureValidator
{
    /**
     * A simple signature/secret comparison for signature in url query string
     * @param Request $request
     * @param WebhookConfig $config
     * @return bool
     * @throws WebhookFailed
     */
    public function isValid(Request $request, WebhookConfig $config): bool
    {
        $signingSecret = $config->signingSecret;
        $signature = $_GET[ $config->signatureHeaderName];

        if ( !isset($signature) || $signature !== $signingSecret)
        {
            return false;
        }

        if (empty($signingSecret)) {
            throw WebhookFailed::signingSecretNotSet();
        }
        return true;
    }
}
