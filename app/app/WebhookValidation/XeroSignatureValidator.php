<?php


namespace App\WebhookValidation;
use Illuminate\Http\Request;
use Spatie\WebhookClient\Exceptions\WebhookFailed;
use Spatie\WebhookClient\WebhookConfig;

class XeroSignatureValidator implements \Spatie\WebhookClient\SignatureValidator\SignatureValidator
{
    /**
     * Checks the signature header against a base64 encoded hash with raw output as per Xero requirements
     * @param Request $request
     * @param WebhookConfig $config
     * @return bool
     * @throws WebhookFailed
     */
    public function isValid(Request $request, WebhookConfig $config): bool
    {
        $signature = $request->header($config->signatureHeaderName);

        if (! $signature) {
            return false;
        }

        $signingSecret = $config->signingSecret;

        if (empty($signingSecret)) {
            throw WebhookFailed::signingSecretNotSet();
        }

        if ($request->isMethod('POST')){
        $computedSignature = base64_encode(hash_hmac('sha256', $request->getContent(), $signingSecret, true));

        return hash_equals($signature, $computedSignature);}
        return true;
    }
}
