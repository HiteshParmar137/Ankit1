<?php

namespace App\Repositories;

use App\Models\PunchLogs;
use App\Models\PunchLogTimes;
use Illuminate\Support\Facades\DB;
use App\Interfaces\PunchLogsRepositoryInterface;
use App\Jobs\PunchLogsJob;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class PunchLogsRepository extends BaseRepository implements PunchLogsRepositoryInterface
{
    // get all Monthly Events.
    public function getAllData($data)
    {    
        if (!empty($data['year']) && !empty($data['month'])) {
            $month = date('Y-m', strtotime($data['year'] . '-' . $data['month']));
        } else {
            $month = date('Y-m');
        }

        $monthStartDate = Carbon::parse($month)->startOfMonth()->format('Y-m-d');
        $monthEndDate = Carbon::parse($month)->endOfMonth()->format('Y-m-d');
 
        $queryData = PunchLogs::withCount([
            'punchLogTimes as total_duration' => function($query) {
                $query->select(DB::raw('SEC_TO_TIME(SUM(TIME_TO_SEC(TIMEDIFF(out_time, in_time))))'));
            },
            'punchLogTimes as break_times' => function($query) {
                $query->select(DB::raw('SEC_TO_TIME(MAX(TIME_TO_SEC(out_time)) - MIN(TIME_TO_SEC(in_time)) - SUM(TIME_TO_SEC(TIMEDIFF(out_time, in_time))))'));
            }
        ])
        ->with([
            'user',
            'punchLogTimes' => function($query) {
                $query->select('*', DB::raw('TIMEDIFF(out_time, in_time) as time_difference'));
            }
        ])
        ->GetTextSearch($data['character_search'])
        ->where(function ($q) use ($data) {
            if (isset($data['user']) && !empty($data['user'])) {
                $q->where('user_id', $data['user']);
            }
        })
        ->where(function ($q) use ($monthStartDate, $monthEndDate) {
            $q->whereDate('date', '>=', $monthStartDate);
            $q->whereDate('date', '<=', $monthEndDate);
        })
        ->orderBy($data['sort_column'], $data['sort_type']);
        $query = $queryData->paginate(
            $data['record_per_page'] == 'all'
                ? $queryData->count()
                : $data['record_per_page']
        );
        return $query;
    }

    // Store Punch log and punch log time data
    public function store($data)
    {  
        $punchLogs = New PunchLogs();
        $punchLogs->user_id = $data['user_id'];
        $punchLogs->date = $data['date'];
        $punchLogs->reason = $data['reason'];
        $punchLogs->save();

        foreach($data['log_data'] as $userLogData){
            $punchLogTimes = new PunchLogTimes();    
            $punchLogTimes->punch_logs_id = $punchLogs->id;
            $punchLogTimes->in_time = $userLogData['in_time'];
            $punchLogTimes->out_time = $userLogData['out_time'];
            $punchLogTimes->save();            
        }

        return true;
    }

    // editing the specified resource
    public function edit($punchLogId)
    {
        return PunchLogs::with(['punchLogTimes'])->find($punchLogId);
    }

    // Store Punch log and punch log time data
    public function update($punchLogId,$punchLogDetails)
    {   
        $punchLogs = PunchLogs::where('id', $punchLogId)->where('user_id', $punchLogDetails['user_id'])->where('date', $punchLogDetails['date'])->first();
        $punchLogs->user_id = $punchLogDetails['user_id'];
        $punchLogs->date = $punchLogDetails['date'];
        $punchLogs->reason = $punchLogDetails['reason'];
        $punchLogs->update();

        $punchLogTimeIds = !empty($punchLogDetails['log_data'])  ? array_column($punchLogDetails['log_data'],'punch_log_token') :  [];

        $deletingPunchTimes = PunchLogTimes::where('punch_logs_id',$punchLogTimeIds)
        ->whereNotIn('id',$punchLogTimeIds)->pluck('id');

        PunchLogTimes::whereIn('id',$deletingPunchTimes)->delete();

        foreach($punchLogDetails['log_data'] as $userLogData){
            
            PunchLogTimes::updateOrCreate([
                'id' => $userLogData['punch_log_token']
            ],[
                'in_time' => $userLogData['in_time'],
                'out_time' => $userLogData['out_time'],
            ]);          
        }
        return true;
    }

    // Delete specified resource
    public function delete($punchlogId)
    {
        return PunchLogs::destroy($punchlogId);
    }

    // add bulk upload punch log data
    public function bulkUpload($data)
    {
        $sheet = $data['file'];
        Storage::disk('importPunchLogBulkUpload')->put('', $sheet);
        $fileName = $sheet->hashName();
        dispatch(new PunchLogsJob($data['date'], $fileName));
        return true;
    }
}