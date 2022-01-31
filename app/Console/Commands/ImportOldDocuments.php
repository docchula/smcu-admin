<?php

namespace App\Console\Commands;

use App\Models\Document;
use App\Models\User;
use Illuminate\Console\Command;

class ImportOldDocuments extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:documents {path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import old documents from json imported from smcu-document-number';

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
    public function handle() {
        /* Sample data
            {
              "-Msh2wZ3R2FXAffM_vOB" : {
                "division" : {
                  "name" : "ชมรมวิจัย",
                  "value" : 29
                },
                "filePath" : "document/2022/1641433513996-26-baramett.som.pdf",
                "name" : "ขอเรียนเชิญเป็นวิทยากรในงานบรรยาย “เปิดโลกงานวิจัย” (Research Talk) ผศ.นพ.ดารุจ อนิวรรตนพงศ์",
                "number" : 1,
                "timestamp" : 1641433516518,
                "user" : {
                  "profile" : {
                    "displayName" : "Baramett Somtha",
                    "email" : "baramett.som@docchula.com",
                    "uid" : "AfEbfI4RB1dmEDSBSWd65WWD2m63"
                  }
                }
              },
            }
         */
        $year = $this->ask('Year', date('Y') + 543);
        $data = json_decode(file_get_contents($this->argument('path')));
        $departmentSeed = json_decode(file_get_contents(database_path('seeders/DepartmentsSeed.json')), true);
        foreach ($data as $docData) {
            if (Document::where('year', $year)->where('number', $docData->number)->exists()) {
                $this->warn('Document ' . $docData->number . '/' . $year . ' already exists!');
            } else {
                $document = new Document([
                    'title' => $docData->name,
                    'department_id' => $departmentSeed['old_to_new_version'][$docData->division->value] ?? 33
                ]);
                $document->year = $year;
                $document->number = $docData->number;
                $user = User::firstOrCreate(['email' => $docData->user->profile->email], ['name' => $docData->user->profile->displayName]);
                $document->user_id = $user->id;
                if (!empty($docData->filePath)) {
                    $document->attachment_path = str_replace('document/20', 'documents/20', $docData->filePath);
                }
                $document->save();
                $this->line('Document ' . $docData->number . '/' . $year . ' added.');
            }
        }

        return 0;
    }
}
