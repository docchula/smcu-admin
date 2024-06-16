<?php

namespace App\Checks;

use Illuminate\Support\Facades\Storage;
use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

class StorageCheck extends Check {
    public function run(): Result {
        $documentDirectoryCount = $this->checkDocumentStorage();

        $result = Result::make();

        if ($documentDirectoryCount == null) {
            return $result->failed("The document storage directory does not exist. Check if the storage disk is mounted correctly.");
        }

        if ($documentDirectoryCount < 1) {
            return $result->warning("The document storage is empty.");
        }

        return $result->ok();
    }

    protected function checkDocumentStorage(): ?int {
        return Storage::directoryExists('documents') ? count(Storage::directories('documents')) : null;
    }
}
