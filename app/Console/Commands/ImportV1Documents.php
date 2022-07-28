<?php

namespace App\Console\Commands;

use App\Models\Document;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ImportV1Documents extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:v1';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import old documents from v1.json exported from V1 database & emails.json';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int {
        /* Sample v1.json file
            [{
                "id": 1,
                "name": "...",
                "number": 819,
                "division_id": 5,
                "buddhist_year": 2560,
                "user_name": "Name Name"
            }, ...]
         */
        $data = json_decode(file_get_contents('v1.json'));
        /* Sample emails.json file
            [{
                "name": "Name Name",
                "email": "name@docchula.com"
            }, ...]
         */
        $emails = collect(json_decode(file_get_contents('emails.json')));
        $departmentSeed = json_decode(file_get_contents(database_path('seeders/DepartmentsSeed.json')))->v1_to_new_version;
        foreach ($data as $docData) {
            if (Document::query()->where('year', $docData->buddhist_year)->where('number', $docData->number)->exists()) {
                $this->warn($docData->id . '. Document ' . $docData->number . '/' . $docData->buddhist_year . ' already exists!');
            } else {
                $document = new Document([
                    'title' => Str::limit($docData->name, 230),
                    'department_id' => $departmentSeed->{$docData->division_id} ?? 33
                ]);
                $document->year = $docData->buddhist_year;
                $document->number = $docData->number;
                if ($email = $emails->where('name', $docData->user_name)->first()?->email) {
                    $user = User::firstOrCreate(['email' => $email], ['name' => $docData->user_name]);
                    $document->user_id = $user->id;
                }
                if (!empty($docData->created_at)) {
                    $document->created_at = Carbon::parse($docData->created_at);
                }
                $document->save();
                $this->line($docData->id . '. Document ' . $docData->number . '/' . $docData->buddhist_year . ' by ' . ($email ?? '?') . ' added.');
            }
        }

        return 0;
    }
}
