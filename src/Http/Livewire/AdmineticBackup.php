<?php

namespace Adminetic\Backup\Http\Livewire;

use Carbon\Carbon;
use Livewire\Component;
use Adminetic\Backup\Rules\PathToZip;
use Adminetic\Backup\Rules\BackupDisk;
use Spatie\Backup\Helpers\Format;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;
use Spatie\Backup\BackupDestination\Backup;
use Illuminate\Validation\ValidationException;
use Spatie\Backup\Tasks\Backup\BackupJobFactory;
use Spatie\Backup\BackupDestination\BackupDestination;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Spatie\Backup\Tasks\Monitor\BackupDestinationStatus;
use PavelMironchik\LaravelBackupPanel\Jobs\CreateBackupJob;
use Spatie\Backup\Tasks\Monitor\BackupDestinationStatusFactory;

class AdmineticBackup extends Component
{
    public $backupStatuses = [];

    public $activeDisk = null;

    public $disks = [];

    public $files = [];

    public $deletingFile = null;

    /*
    |--------------------------------------------------------------------------
    | Menu Management
    |--------------------------------------------------------------------------
    |
    | 1 => Home
    | 2 => Backups
    | 3 => Backups
    | 
    | 
    */
    public $menu = 1;


    public function mount()
    {
        // Get Backup Statuses
        $this->getBackupStatuses();
        // Get Files
        $this->getFiles();
    }

    public function updated()
    {
        $this->getFiles();
    }

    /*
    |--------------------------------------------------------------------------
    | Get Backup Statuses
    |--------------------------------------------------------------------------
    | This PHP function retrieves backup statuses from cache and formats them for display.
    | @return an array of backup statuses for each configured backup destination. The array includes
    | information such as the backup name, disk name, whether the destination is reachable, whether
    | the backup is healthy, the number of backups, the date of the newest backup, and the amount of
    | used storage.
    | - Name
    | - Disk
    | - Reachable
    | - Healthy
    | - Healthy
    | - Backup Count
    | - Backup Usage
    | 
    | 
    */

    public function getBackupStatuses()
    {
        $this->backupStatuses = Cache::remember('backup-statuses', now()->addSeconds(4), function () {
            return BackupDestinationStatusFactory::createForMonitorConfig(config('backup.monitor_backups'))
                ->map(function (BackupDestinationStatus $backupDestinationStatus) {
                    return [
                        'name' => $backupDestinationStatus->backupDestination()->backupName(),
                        'disk' => $backupDestinationStatus->backupDestination()->diskName(),
                        'reachable' => $backupDestinationStatus->backupDestination()->isReachable(),
                        'healthy' => $backupDestinationStatus->isHealthy(),
                        'amount' => $backupDestinationStatus->backupDestination()->backups()->count(),
                        'newest' => $backupDestinationStatus->backupDestination()->newestBackup()
                            ? $backupDestinationStatus->backupDestination()->newestBackup()->date()->diffForHumans()
                            : 'No backups present',
                        'usedStorage' => Format::humanReadableSize($backupDestinationStatus->backupDestination()->usedStorage()),
                    ];
                })
                ->values()
                ->toArray();
        });

        if (!$this->activeDisk and count($this->backupStatuses)) {
            $this->activeDisk = $this->backupStatuses[0]['disk'];
        }

        $this->disks = collect($this->backupStatuses)
            ->map(function ($backupStatus) {
                return $backupStatus['disk'];
            })
            ->values()
            ->all();

        $this->emitSelf('backupStatusesUpdated');
    }

    public function getFiles(string $disk = '')
    {
        if ($disk) {
            $this->activeDisk = $disk;
        }

        $this->validateActiveDisk();

        $backupDestination = BackupDestination::create($this->activeDisk, config('backup.backup.name'));

        $this->files = Cache::remember("backups-{$this->activeDisk}", now()->addSeconds(4), function () use ($backupDestination) {
            return $backupDestination
                ->backups()
                ->map(function (Backup $backup) {
                    $size = method_exists($backup, 'sizeInBytes') ? $backup->sizeInBytes() : $backup->size();

                    return [
                        'name' => explode('/', $backup->path())[count(explode('/', $backup->path())) - 1],
                        'path' => $backup->path(),
                        'date' => Carbon::create($backup->date()),
                        'size' => Format::humanReadableSize($size),
                    ];
                })
                ->toArray();
        });
    }



    public function deleteFile($index)
    {
        $deletingFile = $this->files[$index];

        $this->emitSelf('hideDeleteModal');

        $this->validateActiveDisk();
        $this->validateFilePath($deletingFile ? $deletingFile['path'] : '');

        $backupDestination = BackupDestination::create($this->activeDisk, config('backup.backup.name'));

        $backupDestination
            ->backups()
            ->first(function (Backup $backup) use ($deletingFile) {
                return $backup->path() === $deletingFile['path'];
            })
            ->delete();

        $this->files = collect($this->files)
            ->reject(function ($file) use ($deletingFile) {
                return $file['path'] === $deletingFile['path']
                    && $file['date'] === $deletingFile['date']
                    && $file['size'] === $deletingFile['size'];
            })
            ->values()
            ->all();

        $this->getFiles();

        $this->emit('backup_success', 'Backup deleted successfully');
    }

    public function downloadFile(string $filePath)
    {
        $this->validateActiveDisk();
        $this->validateFilePath($filePath);

        $backupDestination = BackupDestination::create($this->activeDisk, config('backup.backup.name'));

        $backup = $backupDestination->backups()->first(function (Backup $backup) use ($filePath) {
            return $backup->path() === $filePath;
        });

        if (!$backup) {
            $this->emit('backup_error', 'Backup not found');
        }

        // Get Files
        $this->getFiles();
        return $this->respondWithBackupStream($backup);
    }

    public function respondWithBackupStream(Backup $backup): StreamedResponse
    {
        $fileName = pathinfo($backup->path(), PATHINFO_BASENAME);
        $size = method_exists($backup, 'sizeInBytes') ? $backup->sizeInBytes() : $backup->size();

        $downloadHeaders = [
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Content-Type' => 'application/zip',
            'Content-Length' => $size,
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Pragma' => 'public',
        ];
        $this->emit('backup_success', 'Backup downloaded successfully');
        return response()->stream(function () use ($backup) {
            $stream = $backup->stream();

            fpassthru($stream);

            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, $downloadHeaders);
    }

    public function createBackup()
    {
        Artisan::call('backup:run', ['--only-db' => true]);
        $this->getFiles();

        $this->emit('backup_success', 'Backup created successfully');
    }

    public function render()
    {
        return view('backup::livewire.adminetic-backup');
    }

    protected function validateActiveDisk()
    {
        try {
            Validator::make(
                ['activeDisk' => $this->activeDisk],
                [
                    'activeDisk' => ['required', new BackupDisk()],
                ],
                [
                    'activeDisk.required' => 'Select a disk',
                ]
            )->validate();
        } catch (ValidationException $e) {
            $message = $e->validator->errors()->get('activeDisk')[0];
            $this->emitSelf('showErrorToast', $message);

            throw $e;
        }
    }

    protected function validateFilePath(string $filePath)
    {
        try {
            Validator::make(
                ['file' => $filePath],
                [
                    'file' => ['required', new PathToZip()],
                ],
                [
                    'file.required' => 'Select a file',
                ]
            )->validate();
        } catch (ValidationException $e) {
            $message = $e->validator->errors()->get('file')[0];
            $this->emitSelf('showErrorToast', $message);

            throw $e;
        }
    }
}
