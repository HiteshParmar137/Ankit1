<?php

namespace App\Imports;

use App\Models\PunchLogs;
use App\Models\PunchLogTimes;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Str;

class PunchLogsImport implements ToCollection
{
    public function  __construct($date)
    {
        $this->date = $date;
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function collection(Collection $rows)
    {	
		try{
			foreach ($rows as $row) 
			{
				$row = $row->toArray();
				$empCode =  $row[1];
				$userId = User::where('emp_code',$empCode)->pluck('id')->toArray();

				if (isset($userId) && !empty($userId)) 
				{
					//check if record already exist for same user/date 
					$logDate = date('Y-m-d',strtotime($this->date));
					$existData = PunchLogs::where('user_id',$userId[0])->where('date',$logDate)->first();
					$inOutTimeDatas = explode(',', $row[5]);
					if(!$existData)
					{	
						$punchLog = PunchLogs::create([
							'user_id' => $userId[0],
							'date' => $logDate,
						]);
						$n = 0; 
						$punchLlog = 0; 
						$finalRecords =  $punchData = [];
						$totalCount = count($inOutTimeDatas) - 1;
						foreach ($inOutTimeDatas as $key => $userData) 
						{
							$userData = substr($userData, 0, strrpos($userData, ':'));
							if($userData != '')
							{
								$punchData[] = $userData;
								$punchLlog++;
								
								if($punchLlog > 1){
									$punchLlog = 0;
									$finalRecords[] = $punchData;
									$punchData = [];
								}
							}
							if($totalCount == $n && count($punchData) == 1)
							{
								$punchData[] = $punchData[0];
								$finalRecords[] = $punchData;
							}
							$n++;
						}  
						if(isset($finalRecords) && !empty($finalRecords))
						{
							//Now insert in database 
							foreach ($finalRecords as $key1 => $log) {
								PunchLogTimes::create([
									'punch_logs_id' => $punchLog->id,
									'in_time' => $log[0],
									'out_time' => $log[1],
								]);
							}
						}
					}	
				}
			}
			return true;
		}catch (\Exception $e) {
            \Log::error($e->getMessage());
        }
    }
}
