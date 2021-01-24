<?php


namespace App\WebhookValidation;
use Illuminate\Http\Request;
use Spatie\WebhookClient\Exceptions\WebhookFailed;
use Spatie\WebhookClient\WebhookConfig;

/**
 * @package App\WebhookValidation
 */
class WebhookNoSignatureValidator implements \Spatie\WebhookClient\SignatureValidator\SignatureValidator
{

    /**
     * Validates any request sent to the correct url
     *
     * @param Request $request
     * @param WebhookConfig $config
     * @return bool
     */
    public function isValid(Request $request, WebhookConfig $config): bool
    {
        return true;
    }
}
