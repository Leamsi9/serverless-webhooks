<?php

return array(
    'configs' => array(

//

        [
            /*
             * This package support multiple webhook receiving endpoints. If you only have
             * one endpoint receiving webhooks, you can use 'default'.
             */
            'name' => 'xero',

            /*
             * We expect that every webhook call will be signed uhttp://apidocs.mailchimp.com/webhooks/downloads/webhooks.phpssing a secret. This secret
             * is used to verify that the payload has not been tampered with.
             */
            'signing_secret' => env('XERO_CLIENT_SECRET'),

            /*
             * The name of the header containing the signature.
             */
            'signature_header_name' => 'x-xero-signature',

            /*
             *  This class will verify that the content of the signature header is valid.
             *
             * It should implement \Spatie\WebhookClient\SignatureValidator\SignatureValidator
             */
            'signature_validator' => \App\WebhookValidation\XeroSignatureValidator::class,

            /*
             * This class determines if the webhook call should be stored and processed.
             */
            'webhook_profile' =>  \Spatie\WebhookClient\WebhookProfile\ProcessEverythingWebhookProfile::class,

            /*
             * The classname of the model to be used to store call. The class should be equal
             * or extend Spatie\WebhookClient\Models\WebhookCall.
             */
            'webhook_model' => \Spatie\WebhookClient\Models\WebhookCall::class,

            /*
             * The class name of the job that will process the webhook request.
             *
             * This should be set to a class that extends \Spatie\WebhookClient\ProcessWebhookJob.
             */
            'process_webhook_job' => App\WebhookJobs\ProcessWebhookJob::class,
        ],

        [
            /*
             * This package support multiple webhook receiving endpoints. If you only have
             * one endpoint receiving webhooks, you can use 'default'.
             */
            'name' => 'mailchimp',

            /*
             * We expect that every webhook call will be signed uhttp://apidocs.mailchimp.com/webhooks/downloads/webhooks.phpssing a secret. This secret
             * is used to verify that the payload has not been tampered with.
             */
            'signing_secret' => env('MAILCHIMP_CLIENT_SECRET'),

            /*
             * MailChimp has no signature header
             */
            'signature_header_name' => 'signature',

            /*
             *  This class will verify that the content of the signature header is valid.
             *
             * It should implement \Spatie\WebhookClient\SignatureValidator\SignatureValidator
             */
            'signature_validator' => \App\WebhookValidation\WebhookUrlSignatureValidator::class,

            /*
             * This class determines if the webhook call should be stored and processed.
             */
            'webhook_profile' => \App\WebhookProfiles\MailchimpWebhookProfile::class,

            /*
             * The classname of the model to be used to store call. The class should be equal
             * or extend Spatie\WebhookClient\Models\WebhookCall.
             */
            'webhook_model' => \Spatie\WebhookClient\Models\WebhookCall::class,

            /*
             * The class name of the job that will process the webhook request.
             *
             * This should be set to a class that extends \Spatie\WebhookClient\ProcessWebhookJob.
             */
            'process_webhook_job' => App\WebhookJobs\ProcessWebhookJob::class,
        ],

        /*
          * The default config involves no signature validation, and will return a 200 for every request to the designated endpoint'.
          */
        [
            'name' => 'default',

            'signature_validator' => \App\WebhookValidation\WebhookNoSignatureValidator::class,

            /*
             * This class determines if the webhook call should be stored and processed.
             */
            'webhook_profile' =>  \Spatie\WebhookClient\WebhookProfile\ProcessEverythingWebhookProfile::class,

            /*
             * The classname of the model to be used to store call. The class should be equal
             * or extend Spatie\WebhookClient\Models\WebhookCall.
             */
            'webhook_model' => \Spatie\WebhookClient\Models\WebhookCall::class,

            /*
             * The class name of the job that will process the webhook request.
             *
             * This should be set to a class that extends \Spatie\WebhookClient\ProcessWebhookJob.
             */
            'process_webhook_job' => App\WebhookJobs\ProcessWebhookJob::class,
        ],
    ),
);
