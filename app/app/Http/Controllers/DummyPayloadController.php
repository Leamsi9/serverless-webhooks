<?php


namespace App\Http\Controllers;

use Spatie\WebhookServer\WebhookCall as WHServer;
use Illuminate\Http\Request;

class DummyPayloadController
{
    /**
     Testing endpoint.  Fires a post request to lambda webhooks client. The body of the request will be JSON encoded version of the array passed to payload. The request will have a header called Signature that will contain a signature the receiving app can use to verify the payload hasn't been tampered with.

    If the receiving app doesn't respond with a response code starting with 2, the package will retry calling the webhook after 10 seconds. If that second attempt fails, the package will attempt to call the webhook a final time after 100 seconds. Should that attempt fail, the FinalWebhookCallFailedEvent will be raised.
     */
    public function payload(Request $request)
    {

        $payload = ['dummyControllerpayload' => $request->input()];
        WHServer::create()
            ->url('YOUR AWS LAMBDA URL')
            ->payload(['webhooks server payload' => 'queued content here'])
            ->useSecret('sign-using-this-secret')
            ->dispatchNow();

    }
}
