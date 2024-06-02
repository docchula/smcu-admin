<?php

namespace App\Console\Commands;

use App\Http\Controllers\GoogleController;
use App\Mail\EmailDeliveryFailureMail;
use App\Mail\SignApprovedMail;
use App\Mail\SignRejectedMail;
use App\Models\Document;
use Carbon\Carbon;
use Crypt;
use Google\Client;
use Google\Service\Gmail;
use Google\Service\PeopleService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Storage;

class FetchEmailCommand extends Command
{
    protected const LAST_FETCH_FILE = 'last_fetch_email.json';
    protected $signature = 'document:fetch-email {max=50}';
    protected $description = 'Retrieve email from Google and save to database (max 1-500)';

    public function handle(): void
    {
        if (!Storage::exists(GoogleController::ADMIN_ACCOUNT_TOKEN_FILE)) {
            $this->error('Admin account token file not found');
            exit(1);
        }

        /** @var array{access_token: string, refresh_token: string, expires_at: \Carbon\Carbon, approved_scopes: string[]} $token */
        $token = Crypt::decrypt(Storage::get(GoogleController::ADMIN_ACCOUNT_TOKEN_FILE));
        if (!in_array(Gmail::GMAIL_READONLY, $token['approved_scopes'])) {
            $this->error('Admin account does not have Gmail read-only permission');
            exit(1);
        }

        // Setup Google client and access token
        $googleClient = $this->getGoogleClient();
        $googleClient->setAccessToken($token['access_token']);
        if ($token['expires_at']->isPast()) {
            // Access token is expired. Refresh it and store to file.
            $googleClient->fetchAccessTokenWithRefreshToken($token['refresh_token']);
            $token['access_token'] = $googleClient->getAccessToken()['access_token'];
            $token['expires_at'] = now()->addSeconds($googleClient->getAccessToken()['expires_in']);
            Storage::put(GoogleController::ADMIN_ACCOUNT_TOKEN_FILE, Crypt::encrypt($token));
        }

        // Restore fetch history

        /** @var array{updated_at: Carbon, processed_messages: \Illuminate\Support\Collection} $fetchHistory */
        $fetchHistory = [
            'updated_at' => Carbon::createFromTimestamp(0),
            'processed_messages' => collect(),
        ];
        if ($fetchHistoryFile = Storage::get(self::LAST_FETCH_FILE)) {
            $fetchHistory = Crypt::decrypt($fetchHistoryFile);
        }

        // Fetch email: list messages
        $gmailService = new Gmail($googleClient);
        $messageListQuery = $gmailService->users_messages->listUsersMessages(GoogleController::ADMIN_ACCOUNT_EMAIL, [
            'includeSpamTrash' => false,
            'q' => 'from:(no-reply@dochub.com) subject:(สพจ)',
            'maxResults' => (int) $this->argument('max'),
        ]);

        // Fetch each message in batch
        $messageList = collect($messageListQuery->getMessages())->reject(function (Gmail\Message $message) use ($fetchHistory) {
            // Skip processed messages
            return $fetchHistory['processed_messages']->contains($message->getId());
        });
        $this->info('Found '.$messageList->count().' new messages');
        foreach ($messageList->chunk(20) as $i => $chunk) {
            $this->line('Fetching message chunk '.$i);
            $batch = $gmailService->createBatch();
            foreach ($chunk as $message) {
                /** @var \Google\Service\Gmail\Message $message */
                $googleClient->setUseBatch(true);
                /** @noinspection PhpParamsInspection */
                $batch->add($gmailService->users_messages->get(GoogleController::ADMIN_ACCOUNT_EMAIL, $message->getId(), [
                    'format' => 'full',
                ]));
            }
            foreach ($batch->execute() as $message) {
                /** @var \Google\Service\Gmail\Message $message */
                $googleClient->setUseBatch(false);
                // Convert header to array
                $headers = [];
                foreach ($message->getPayload()->getHeaders() as $header) {
                    $headers[$header->getName()] = $header->getValue();
                }

                $fetchHistory['processed_messages']->push($message->getId());
                Storage::put(self::LAST_FETCH_FILE, Crypt::encrypt($fetchHistory));

                // Message must be sent from DocHub
                if (!str_contains($headers['From'], 'no-reply@dochub.com')) {
                    continue;
                }

                // Get document number
                $subject = $headers['Subject'];
                $this->line('- Processing message '.$subject);
                $subject = str_replace(['Copy of ', 'สำเนา '], '', $subject);
                $list = [];
                // Regex pattern for document title; e.g.
                // SMCU 623-624_2567 ขอความอนุเคราะห์ใช้สถานที่.pdf OR
                // สพจ. 623-2567 ขอความอนุเคราะห์ใช้สถานที่.pdf
                if (preg_match('/(?:สพจ|SMCU)(?:\.|\s|-){1,2}(\d+)(?:_|-|\/)(?:\d+(?:_|-|\/))?(25\d\d)(?:\s|-).+/i', $subject, $list,
                    PREG_UNMATCHED_AS_NULL)) {
                    $document = Document::where('number', $list[1])->where('year', $list[2])->first();
                    if ($document) {
                        $this->line('-- Found update of document '.$document->id.' ('.$document->number.'/'.$document->year.') '.$document->title);
                        if (str_starts_with($subject, 'FINALIZED:')) {
                            // Skip if document is already approved
                            if ($document->status == Document::STATUS_APPROVED and $document->approved_path and Carbon::parse($headers['Date'])
                                    ->isBefore($document->updated_at)) {
                                $this->line('-- Document is already approved, skipping...');
                                continue;
                            }
                            // Save file
                            $filename = 'documents/'.$document->year.'/'.$document->id.'_'.$document->number.'-'.$document->year.'_Signed.pdf';
                            foreach ($message->getPayload()->getParts() as $part) {
                                if ($part->getMimeType() === 'application/pdf') {
                                    // Retrieve attachment id then fetch file content
                                    $attachmentId = $part->getBody()->getAttachmentId();
                                    $attachmentContent = $gmailService->users_messages_attachments->get(GoogleController::ADMIN_ACCOUNT_EMAIL,
                                        $message->getId(), $attachmentId)->getData();
                                    Storage::put($filename, base64_decode(strtr($attachmentContent, '-_', '+/')));
                                    $document->approved_path = $filename;
                                    $document->status = Document::STATUS_APPROVED;
                                    $document->saveOrFail();
                                    break;
                                }
                            }

                            if ($document->user?->email) {
                                $this->line('-- Sending approval notification to '.$document->user->email);
                                Mail::to($document->user->email)->send(new SignApprovedMail($document));
                            }
                        } elseif (str_starts_with($subject, 'REJECTED:') and $document->status != Document::STATUS_REJECTED) {
                            $document->status = Document::STATUS_REJECTED;
                            $document->save();

                            if ($document->user?->email) {
                                $this->line('-- Sending rejection notification to '.$document->user->email);
                                Mail::to($document->user->email)->send(new SignRejectedMail($document));
                            }
                        } elseif (str_starts_with($subject, 'Email delivery failure:') and $document->status != Document::STATUS_UNDELIVERED) {
                            $document->status = Document::STATUS_UNDELIVERED;
                            $document->save();

                            if ($document->user?->email) {
                                $this->line('-- Sending failure notification to '.$document->user?->email);
                                Mail::to($document->user?->email)->send(new EmailDeliveryFailureMail($document));
                            }
                        }
                    }
                }
            }
        }

        $this->info('Done');
    }

    public function getGoogleClient(): Client
    {
        $client = new Client();
        $client->setApplicationName(config('app.name'));
        $client->setScopes([PeopleService::DIRECTORY_READONLY, PeopleService::USERINFO_PROFILE]);
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setAccessType('offline');

        return $client;
    }
}
