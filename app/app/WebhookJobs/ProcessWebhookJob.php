<?php


namespace App\WebhookJobs;

use \Spatie\WebhookClient\ProcessWebhookJob as SpatieProcessWebhookJob;
class ProcessWebhookJob extends SpatieProcessWebhookJob
{
    public function handle()
    {
         $this->webhookCall;
    }
}
