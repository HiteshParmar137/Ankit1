<?php

namespace App\Repositories;

use App\Interfaces\WorkFromHomeRepositoryInterface;
use App\Jobs\WorkFromHomeJob;
use App\Models\WorkFromHome;

class WorkFromHomeRepository extends BaseRepository implements WorkFromHomeRepositoryInterface
{
    // get all Created user work from home 
    public function getAllData($data, $startDate, $endDate)
    {   
        $financialYearStart = $startDate ?? Config::get('constant.CURRENT_FINANCIAL_YEAR_START_DATE');
        $financialYearEnd = $endDate  ?? Config::get('constant.CURRENT_FINANCIAL_YEAR_END_DATE');

        $queryData = WorkFromHome::with('requestToUser')
            ->CreatedByItself()
            ->where(function ($query) use ($financialYearStart, $financialYearEnd) {
                $query->where(function ($query) use ($financialYearStart, $financialYearEnd) {
                    $query->where('start_date', '>=', $financialYearStart)
                        ->where('start_date', '<=', $financialYearEnd);
                })
                ->orWhere(function ($query) use ($financialYearStart, $financialYearEnd) {
                    $query->where('end_date', '>=', $financialYearStart)
                        ->where('end_date', '<=', $financialYearEnd);
                })
                ->orWhere(function ($query) use ($financialYearStart, $financialYearEnd) {
                    $query->where('start_date', '<', $financialYearStart)
                        ->where('end_date', '>', $financialYearEnd);
                });
            })
            ->orderBy($data['sort_column'], $data['sort_type']);
        $workFromHome = $queryData->paginate($data['record_per_page'] == "all" ? $queryData->count() : $data['record_per_page']);
        return $workFromHome;
    }

    // Store work from home data
    public function store($data)
    {   
        $workFromHome = WorkFromHome::create($data);
        $type = WorkFromHome::WFH_MAIL_TYPE;
        $title = "Added";
        dispatch(new WorkFromHomeJob($workFromHome, $title, $type));
        return $workFromHome;
    }

    // editing the specified resource
    public function edit($workFromHomeId)
    {
        return WorkFromHome::find($workFromHomeId);
    }

    // Update the specified resource
    public function update($workFromHomeId, array $newWorkFromHomeDetails)
    {
        $workFromHome = tap(WorkFromHome::find($workFromHomeId)->CreatedByItself()->first())->update($newWorkFromHomeDetails);
        $type = WorkFromHome::WFH_MAIL_TYPE;
        $title = "Updated";
        dispatch(new WorkFromHomeJob($workFromHome, $title, $type));
        return true;
    }

    // Delete specified resource
    public function delete($policyId)
    {
        return WorkFromHome::destroy($policyId);
    }

    // show specified resource
    public function show($leaveId, $requestedUserId)
    {
        return WorkFromHome::with('user')->where('id', $leaveId)->RequestToUser()->first();
    }

    // get only Request To User work from home
    public function teamWorkFromHome($requestToUserId, $data)
    {
        return WorkFromHome::with('user')->RequestToUser()->orderBy($data['sort_column'], $data['sort_type'])->paginate($data['record_per_page']);
    }

    //store work from home feedBack and status
    public function workFromHomeFeedback($workFromHomeID, $workFromHomeData)
    {
        $workFromHome = WorkFromHome::where('id', $workFromHomeID)->where('status', WorkFromHome::PENDING)->RequestToUser()->first();
        $workFromHome->status = $workFromHomeData['status'];
        $workFromHome->feedback = $workFromHomeData['feedback'];
        $workFromHome->update();
        $title = "FeedBack";
        $type = WorkFromHome::WFH_FEED_BACK_MAIL_TYPE;
        dispatch(new WorkFromHomeJob($workFromHome, $title, $type));
        return $workFromHome;
    }

    //Get all Leave data => 
    public function getAllWfhData($data)
    {   
        $queryData = WorkFromHome::with(['user', 'requestToUser'])->GetTextSearch($data)->orderBy($data['sort_column'], $data['sort_type']);
        $allWorkFromHome = $queryData->paginate(
            $data['record_per_page'] == 'all'
                ? $queryData->count()
                : $data['record_per_page']
        );
        return $allWorkFromHome;
    }

    public function financialYearData($data)
    {
        $queryData = WorkFromHome::where('user_id', 1)
            ->whereBetween('start_date', array($data['start_date'], $data['end_date']))
            ->whereBetween('end_date', array($data['start_date'], $data['end_date']))
            ->where('status', WorkFromHome::APPROVED);
        $leaves = $queryData->paginate($data['record_per_page'] == "all" ? $queryData->count() : $data['record_per_page']);
        return $leaves;
    }
}
