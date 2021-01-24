<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\WebhookClient\Exceptions\WebhookFailed;
use Spatie\WebhookClient\WebhookConfig;
use Spatie\WebhookClient\WebhookProcessor;

class XeroWebhookController
{
    /**
     * Xero requires a 200 response without body on success and a 401 on failure. This overrides the Spatie Exception
     * (500 error) and removes the success message
     * @param Request $request
     * @param WebhookConfig $config
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function __invoke(Request $request, WebhookConfig $config)
    {
        try {
            (new WebhookProcessor($request, $config))->process();
        } catch (WebhookFailed $exception) {
            return response(null, 401);
        }

        return response(null, 200);
    }
}
