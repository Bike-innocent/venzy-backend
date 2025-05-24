<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class BackupDatabase extends Command
{
    protected $signature = 'db:backup';
    protected $description = 'Backup the database';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $filename = 'backup-' . date('Y-m-d_H-i-s') . '.sql';
        $path = storage_path('app/' . $filename);
        $command = "mysqldump -u username -p'password' database_name > $path";

        $process = new \Symfony\Component\Process\Process($command);
        $process->run();

        if ($process->isSuccessful()) {
            $this->info('Database backup was successful!');
        } else {
            $this->error('Database backup failed.');
        }
    }
}
