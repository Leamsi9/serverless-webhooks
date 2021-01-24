# Webhooks Client Prototype

A webhook is a way for an app to provide information to another app about a specific event. The way the two apps communicate is with a simple HTTP request. 

This Laravel app allows you to receive, process , handle and store webhooks.  It uses and customises the following Spatie package:  https://github.com/spatie/laravel-webhook-client

It has support for [verifying signed calls](#verifying-the-signature-of-incoming-webhooks), [storing payloads and processing the payloads](#storing-and-processing-webhooks) in a queued job.

It runs as a Lambda AWS function, stored in an S3 bucket, via the AWS Api Gateway, and is deployed via the [Serverless framework, using a Bref PHP runtime](https://bref.sh/docs/). All files in the `.serverless` directory are auto-generated.


### Configuration

For local development, the app comes with a `lando.yml` configuration file for [Lando docker containers](https://docs.lando.dev/basics/) set to php 7.3 for package compatibility.

This app's configuration file is found at `config/webhook-client.php`, and gets its signature header and secret variables from `.env`.

The config is a nested array supporting multiple webhook-receiving endpoints and associated rules, each with a different named .

Example:

```php
return [
    'configs' => [
        [
            'name' => 'default',

            /*
             * In this example we expect that every webhook call will be signed using a secret. This secret
             * is used to verify that the payload has not been tampered with.
             */
            'signing_secret' => env('WEBHOOK_CLIENT_SECRET'),

            /*
             * The name of the header containing the signature.
             */
            'signature_header_name' => 'Signature',

            /*
             *  This class will verify that the content of the signature header is valid. Different apis will require different validation rules. Currently, each demo api has its own validator class.
             *
             * It should implement \Spatie\WebhookClient\SignatureValidator\SignatureValidator
             */
            'signature_validator' => \Spatie\WebhookClient\SignatureValidator\DefaultSignatureValidator::class,

            /*
             * This class determines if the webhook call should be stored and processed. It can specify exceptions to be ignored, per api requirements, each with its own profile class.
             */
            'webhook_profile' => \Spatie\WebhookClient\WebhookProfile\ProcessEverythingWebhookProfile::class,

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
            'process_webhook_job' => '',
        ],
    ],
```

In the `signing_secret` key of the config file, you should add a valid webhook secret. This value should be provided by the app that will send you webhooks.

This app will try to store and respond to the webhook as fast as possible. Processing the payload of the request is done via a queued job.  It's recommended to not use the `sync` driver but a real queue driver. You should specify the job that will handle processing webhook requests in the `process_webhook_job` of the config file. A valid job is any class that extends `Spatie\WebhookClient\ProcessWebhookJob` and has a `handle` method.

For convenience,  [ngrok](https://ngrok.com/docs)  is included to expose the local server to the internet and access it over http or https.

### Preparing the database

By default, all webhook calls will get saved in the database.

To create the table that holds the webhook calls, you must publish the migration with the Laravel `artisan migrate` command (if using Lando the command would be preceded by `lando` instead of `php`. On Lambda AWS the prefix would use the `bref` cli):

### Routing

At the app that sends webhooks, you probably configure a URL where you want your webhook requests to be sent. In the routes file of the app.

Routing is defined via macros in `app/Providers/RouteServiceProvider.php`. Here's an example:

```
 Route::macro('webhooksGet', function (string $url, string $name) {
            return Route::get($url, '\Spatie\WebhookClient\WebhookController')->name("webhook-client-{$name}");
        });

#  This replaces Spatie controller with a custom Xero one to handle validation requirements
  Route::macro('xeroGet', function (string $url, string $name) {
            return Route::get($url, '\App\Http\Controllers\XeroWebhookController')->name("webhook-client-{$name}");
        });
```

The routes are then invoked in  `routes/web.php`, passing the url endpoint and the applicable webhook config as follows:

```
Route::xeroGet('/xero', 'xero');

```


## Usage

With the installation out of the way, let's take a look at how this app handles webhooks. 

First, it will verify if the signature of the request is valid. If it is not, we'll throw an exception and fire off the `InvalidSignatureEvent` event with a `500` error,, which can bd handled in custom controllers. Requests with invalid signatures will not be stored in the database. 

Next, the request will be passed to a webhook profile. A webhook profile is a class that determines if a request should be stored and processed by the app. It allows you to filter out webhook requests that are of interest to your app..
If the webhook profile determines that request should be stored and processed, we'll first store it in the `webhook_calls` table. After that, we'll pass that newly created `WebhookCall` model to a queued job. Most webhook sending apps expect you to respond very quickly. Offloading the real processing work allows for speedy responses. You can specify which job should process the webhook in the `process_webhook_job` in the `webhook-client` config file. Should an exception be thrown while queueing the job, the package will store that exception in the `exception` attribute on the `WebhookCall` model.

After the job has been dispatched, the controller will respond with a `200` status code  and a success message unless otherwise specified in a custom controller. For example, the Xero api requires a 200 with an empty body on success and a 401 on failure. The `XeroWebhookController` specifies:
```
        try {
            (new WebhookProcessor($request, $config))->process();
        } catch (WebhookFailed $exception) {
            return response(null, 401);
        }

        return response(null, 200);
```

### Verifying the signature of incoming webhooks

The app provides a range of signature validation options, from no signature validation, to base 64 encoded and hash 256 encrypted 
validation, as required by Xero.  It also provides for validation via both, the url query string in the `GET` header, and a signature header in the `POST` body.

The validation classes are found in `app/WebhookValiation/`

Each validator must be registered iunder `signature_validator` in the `webhook-client` config file for the pertinent named webhook config.

### Determining which webhook requests should be stored and processed

After the signature of an incoming webhook request is validated, the request will be passed to a webhook profile. A webhook profile is a class  that implements `\Spatie\WebhookClient\WebhookProfile\WebhookProfile`.  It determines if the request should be stored and processed. If the webhook sending app sends out request where your app isn't interested in, you can use this class to filter out such events.

By default the `\Spatie\WebhookClient\WebhookProfile\ProcessEverythingWebhookProfile` class is used. As its name implies,  A customised example with exceptions is in `app/WebhookProfiles/MailchimpWebhookProfile`. This also must be registered in the `webhook-client` config file.

### Creating your own webhook profile

A webhook profile is any class that implements `\Spatie\WebhookClient\WebhookProfile\WebhookProfile`. This is what that interface looks like:


### Storing and processing webhooks

After the signature is validated and the webhook profile has determined that the request should be processed, the app will store and process the request. 

The request will first be stored in the `webhook_calls` table. This is done using the `WebhookCall` model. 

Should you want to customize the table name or anything on the storage behavior, you can use an alternative model as long as it  extends `Spatie\WebhookClient\Models\WebhookCall`. 

A webhook storing model can be specified in the `webhook-client`  config under `webhook_model`..

You can change how the webhook is stored by overriding the `storeWebhook` method of `WebhookCall`. In the `storeWebhook` method you should return a saved model.

Next, the newly created `WebhookCall` model will be passed to a queued job that will process the request. Any class that extends `\Spatie\WebhookClient\ProcessWebhookJob` is a valid job. Here's an example:

```php
namespace App\Jobs;

use \Spatie\WebhookClient\ProcessWebhookJob as SpatieProcessWebhookJob;

class ProcessWebhookJob extends SpatieProcessWebhookJob
{
    public function handle()
    {
        // $this->webhookCall // contains an instance of `WebhookCall`
    
        // perform the work here
    }
}
```

You should specify the class name of your job in the `process_webhook_job` of the `webhook-client` config file. 

##Serverless deployment
The webhooks client currently lives in an AWS Lamda function at  using the AWS API Gateway to manage http requests. The webhooks client database lives in an S3 bucket at. 
 

AWS Lambda does not natively support a PHP runtime. To both, provide a php 7.4 runtime, and a cli-based deployment infrastructure, the webhooks-client integrates Bref and the Serverless Framework, configured in `serverless.yaml` in the root directory, using an S3 bucket for storage.  Bref is an open source project that brings full support for PHP and its frameworks to AWS Lambda. The Serverless Framework CLI manages our code as well as our infrastructure,  providing support for local testing of serverless applications. 

To interact with Bref and Serverless, you need to install the serverless cli  through `npm install -g serverless` (more details at https://serverless.com/framework/docs/providers/aws/guide/quick-start/). Bref is already required in the `composer.json` file.

Any changes to the codebase will need to be propagated to the Lambda function, which is automated via the `serverless deploy` command, which must be run  from within the `app/` root directory. This will create a set of build files in the `.serverless/` directory, and push them to the Lambda function, ensuring the latest version is reflected there.

Any PHP version changes need to also be reflected in the AWS Lambda PHP runtime, so the `serverless.yaml` file needs to have the same PHP version as that used in `composer.json`

For more information see:

https://docs.aws.amazon.com/lambda/latest/dg/welcome.html

https://docs.aws.amazon.com/lambda/latest/dg/runtimes-custom.html

https://docs.aws.amazon.com/lambda/latest/dg/services-apigateway.html

https://bref.sh/docs/

https://bref.sh/docs/deploy.html


 





