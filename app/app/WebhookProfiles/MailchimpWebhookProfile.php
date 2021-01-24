<?php

namespace App\WebhookProfiles;

use Illuminate\Http\Request;

class MailchimpWebhookProfile implements  \Spatie\WebhookClient\WebhookProfile\WebhookProfile
{
    /**
     * Designates filters for webhooks to exclude
     *
     * @param Request $request
     * @return bool
     */
    public function shouldProcess(Request $request): bool
    {
        return !($request->input('type') === 'unsubscribe');
    }
}
