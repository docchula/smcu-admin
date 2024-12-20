<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Spatie\Health\Commands\RunHealthChecksCommand;
use Spatie\Health\Enums\Status;
use Spatie\Health\ResultStores\ResultStore;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

class SimpleHealthCheckController extends \Spatie\Health\Http\Controllers\SimpleHealthCheckController {
    public function __invoke(Request $request, ResultStore $resultStore): Response {
        if ($request->has('fresh') || config('health.oh_dear_endpoint.always_send_fresh_results')) {
            Artisan::call(RunHealthChecksCommand::class);
        }

        // Modified behavior: check only for failed checks but not warning checks
        if (!($resultStore->latestResults()?->containsCheckWithStatus(Status::failed()))) {
            throw new ServiceUnavailableHttpException(message: 'Application not healthy');
        }

        return response([
            'healthy' => true,
        ])
            ->header('Content-Type', 'application/json')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
    }
}
