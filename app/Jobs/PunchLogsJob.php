<?php

namespace App\Jobs;

use App\Imports\PunchLogsImport;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Excel;
use Illuminate\Support\Facades\Storage;

class PunchLogsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $date;
    protected $fileName;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($date, $fileName)
    {
        $this->date  = $date;
        $this->fileName = $fileName;  
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {   
        try {
            $date = $this->date;
            $fileName = $this->fileName;
            $file = Storage::path('importPunchLogBulkUpload/' . $fileName);
            $productImport = new PunchLogsImport($date);
            Excel::import($productImport, $file);
        }catch (\Exception $e) {
            \Log::error($e->getMessage());
        }
    }
}
