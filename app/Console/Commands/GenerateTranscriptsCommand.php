<?php

namespace App\Console\Commands;

use App\Helper;
use App\Models\Project;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

class GenerateTranscriptsCommand extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transcript:generate
        {student?* : Student IDs to generate transcripts for}
        {--file= : Path to a text/CSV file with one student ID per line}
        {--output= : Output directory (default: storage/app/transcripts/<date>)}
        {--draft : Generate unsigned draft copies with no signature, QR code or public link}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate activity transcript PDFs for a batch of students. Without --draft, '
        .'this assigns a public_identifier to any matched student who does not already have one.';

    public function handle(): int {
        if (!Str::startsWith(config('app.url'), 'http')) {
            $this->components->error('config(\'app.url\') / APP_URL is not set correctly. The transcript QR code and '
                .'verification link would be broken. Fix APP_URL before running this command.');

            return Command::FAILURE;
        }

        $ids = $this->collectStudentIds();
        if (empty($ids)) {
            $this->components->error('No student IDs provided. Pass them as arguments or via --file=.');

            return Command::FAILURE;
        }

        $users = User::whereIn('student_id', $ids)
            ->with(['participants.project' => fn (MorphTo $morphTo) => $morphTo->morphWith([Project::class => ['department']])])
            ->get();

        $unmatched = array_diff($ids, $users->pluck('student_id')->all());
        foreach ($unmatched as $id) {
            $this->components->warn("No student found with student_id {$id}");
        }

        if ($users->isEmpty()) {
            $this->components->error('None of the provided student IDs matched a user.');

            return Command::FAILURE;
        }

        $outputDir = $this->option('output') ?: storage_path('app/transcripts/'.now()->format('Y-m-d'));
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $tmpDir = storage_path('app/transcripts-tmp-'.Str::random(8));
        mkdir($tmpDir, 0755, true);

        try {
            $jobs = $this->renderJobs($users, $tmpDir, $outputDir);
            $results = $this->runRenderer($jobs, $tmpDir);
        } finally {
            $this->cleanupDir($tmpDir);
        }

        $failed = array_filter($results, fn ($r) => !$r['ok']);
        foreach ($failed as $result) {
            $this->components->error("Failed to render transcript for {$result['id']}: {$result['error']}");
        }

        $this->newLine();
        $this->components->info(sprintf(
            'Generated %d transcript(s) in %s (%d failed, %d student ID(s) not found).',
            count($results) - count($failed),
            $outputDir,
            count($failed),
            count($unmatched),
        ));

        return empty($failed) && empty($unmatched) ? Command::SUCCESS : Command::FAILURE;
    }

    /**
     * @return string[]
     */
    private function collectStudentIds(): array {
        $ids = $this->argument('student');

        if ($file = $this->option('file')) {
            if (!is_file($file) || !is_readable($file)) {
                $this->components->error("Cannot read file: {$file}");
                exit(Command::FAILURE);
            }
            $lines = preg_split('/[\r\n,]+/', file_get_contents($file));
            $ids = array_merge($ids, $lines);
        }

        return collect($ids)
            ->map(fn ($id) => trim($id))
            ->filter(fn ($id) => $id !== '' && ctype_digit($id))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, User>  $users
     * @return array<int, array{id: string, route: string, htmlPath: string, outputPath: string, footerLeft: string}>
     */
    private function renderJobs($users, string $tmpDir, string $outputDir): array {
        $draft = $this->option('draft');
        $jobs = [];

        foreach ($users as $index => $user) {
            $viewData = $draft
                ? ['user' => $user, 'draft' => true]
                : ['user' => $user, 'link' => $link = $user->getTranscriptLink(), 'qrCode' => Helper::transcriptQrCode($link)];

            $html = view('my-projects', $viewData)->render();
            $htmlPath = $tmpDir.'/'.$index.'.html';
            file_put_contents($htmlPath, $html);

            $filename = ($user->student_id ?: 'user-'.$user->id).($draft ? '-draft' : '').'.pdf';

            $jobs[] = [
                'id' => $user->student_id ?: (string) $user->id,
                'route' => '/'.$index.'.html',
                'htmlPath' => $htmlPath,
                'outputPath' => $outputDir.'/'.$filename,
                'footerLeft' => 'ระเบียนประวัติการเข้าร่วมกิจกรรมนอกหลักสูตร '.$user->name,
            ];
        }

        return $jobs;
    }

    /**
     * @param  array<int, array{id: string, route: string, htmlPath: string, outputPath: string, footerLeft: string}>  $jobs
     * @return array<int, array{id: string, ok: bool, error?: string}>
     */
    private function runRenderer(array $jobs, string $tmpDir): array {
        $manifest = [
            'jobs' => $jobs,
            'assetsDir' => public_path('assets'),
            'margin' => ['top' => '0.7in', 'right' => '0.9in', 'bottom' => '0.9in', 'left' => '0.7in'],
            'fontBase64' => base64_encode(file_get_contents(public_path('assets/THSarabunNew.ttf'))),
            'footerDate' => now()->addYears(543)->translatedFormat('j F Y'),
        ];
        $manifestPath = $tmpDir.'/manifest.json';
        file_put_contents($manifestPath, json_encode($manifest));

        $bar = $this->output->createProgressBar(count($jobs));
        $bar->start();
        $results = [];

        $process = Process::path(base_path())->forever()->run(
            ['node', 'scripts/render-transcript-pdf.mjs', $manifestPath],
            function ($type, $line) use (&$results, $bar) {
                foreach (explode("\n", trim($line)) as $entry) {
                    if ($entry === '') {
                        continue;
                    }
                    $decoded = json_decode($entry, true);
                    if (is_array($decoded) && isset($decoded['id'])) {
                        $results[] = $decoded;
                        $bar->advance();
                    } elseif (is_array($decoded) && isset($decoded['fatal'])) {
                        $this->components->error($decoded['fatal']);
                    }
                }
            }
        );
        $bar->finish();
        $this->newLine();

        if ($process->failed() && empty($results)) {
            $this->components->error('The PDF rendering process failed to start. Is Playwright installed? '
                .'Run: npx playwright install chromium');
        }

        return $results;
    }

    private function cleanupDir(string $dir): void {
        foreach (glob($dir.'/*') as $file) {
            unlink($file);
        }
        rmdir($dir);
    }
}
