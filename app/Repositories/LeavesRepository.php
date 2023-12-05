<?php

namespace App\Repositories;

use App\Interfaces\LeavesRepositoryInterface;
use App\Jobs\LeaveJob;
use App\Models\Leaves;
use App\Models\User;
use Illuminate\Support\Facades\Config;

class LeavesRepository extends BaseRepository implements LeavesRepositoryInterface
{
    // get all Created user leave
    public function getAllData($data, $startDate, $endDate)
    {   
        $financialYearStart = $startDate ?? Config::get('constant.CURRENT_FINANCIAL_YEAR_START_DATE');
        $financialYearEnd = $endDate  ?? Config::get('constant.CURRENT_FINANCIAL_YEAR_END_DATE');

        $queryData = Leaves::with('requestToUser')
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
            
        $leaves = $queryData->paginate(
            $data['record_per_page'] == 'all'
                ? $queryData->count()
                : $data['record_per_page']
        );
        return $leaves;
    }

    // Store Leave data
    public function store($data)
    {
        $leave = Leaves::create($data);
        $type = Leaves::LEAVE_MAIL_TYPE;
        $title = 'Added';
        dispatch(new LeaveJob($leave, $title, $type));
        return $leave;
    }

    // editing the specified resource
    public function edit($leaveId)
    {
        return Leaves::find($leaveId);
    }

    // Update the specified resource
    public function update($leaveId, array $newLeaveDetails)
    {
        $leave = tap(Leaves::find($leaveId)->CreatedByItself())->update($newLeaveDetails);
        $type = Leaves::LEAVE_MAIL_TYPE;
        $title = 'Updated';
        dispatch(new LeaveJob($leave, $title, $type));
        return true;
    }

    // Delete specified resource
    public function delete($leaveId)
    {
        return Leaves::destroy($leaveId);
    }

    // show specified resource
    public function show($leaveId, $requestedUserId)
    {
        return Leaves::with('user')
            ->where('id', $leaveId)
            ->RequestToUser()
            ->first();
    }

    // get only Request To User leave
    public function teamLeaves($requestToUserId, $data)
    {
        return Leaves::with('user')
            ->RequestToUser()
            ->orderBy($data['sort_column'], $data['sort_type'])
            ->paginate($data['record_per_page']);
    }

    //store leave feedBack and status
    public function leaveFeedback($leaveID, $leaveData)
    {
        $leave = Leaves::where('id', $leaveID)
            ->where('status', Leaves::PENDING)
            ->RequestToUser()
            ->first();
        $leave->status = $leaveData['status'];
        $leave->feedback = $leaveData['feedback'];
        $leave->update();
        $title = 'FeedBack';
        $type = Leaves::LEAVE_FEED_BACK_MAIL_TYPE;
        dispatch(new LeaveJob($leave, $title, $type));
        return $leave;
    }

    //Get all Leave data =>
    public function getAllLeaveData($data)
    {   
        $queryData = Leaves::with(['user', 'requestToUser'])->GetTextSearch($data)->orderBy($data['sort_column'], $data['sort_type']);
        $allLeaves = $queryData->paginate(
            $data['record_per_page'] == 'all'
                ? $queryData->count()
                : $data['record_per_page']
        );
        return $allLeaves;
    }

    public function leaveDebited($leaveId, $isDebited)
    {
        $leave = Leaves::whereId($leaveId)->first();
        $leave->debited_for_salary = $isDebited;
        $leave->update();
        return $leave;
    }

    public function financialYearData($data)
    {
        // $queryData = Leaves::where('user_id', 1)
        //     ->where('status', Leaves::APPROVED)
        //     ->where(function ($query) use ($financialYearStart, $financialYearEnd) {
        //         $query->where(function ($query) use ($financialYearStart, $financialYearEnd) {
        //             $query->where('start_date', '>=', $financialYearStart)
        //                 ->where('start_date', '<=', $financialYearEnd);
        //         })
        //         ->orWhere(function ($query) use ($financialYearStart, $financialYearEnd) {
        //             $query->where('end_date', '>=', $financialYearStart)
        //                 ->where('end_date', '<=', $financialYearEnd);
        //         })
        //         ->orWhere(function ($query) use ($financialYearStart, $financialYearEnd) {
        //             $query->where('start_date', '<', $financialYearStart)
        //                 ->where('end_date', '>', $financialYearEnd);
        //         });
        //     });
        // $leaves = $queryData->paginate(
        //     $data['record_per_page'] == 'all'
        //         ? $queryData->count()
        //         : $data['record_per_page']
        // );
        // return $leaves;
    }
}