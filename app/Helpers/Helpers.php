<?php

use App\Mail\Leave;
use App\Models\Holiday;
use App\Models\Leaves;
use App\Models\ProjectTask;
use App\Models\Technology;
use App\Models\WorkFromHome;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use App\Models\User;

function dateFormate($date)
{
    $formattedDate = Carbon::parse($date)->format('Y-m-d');
    return $formattedDate;
}

function formateDateView($date)
{
    $formattedDate = Carbon::parse($date)->format('d-m-Y');
    return $formattedDate;
}

function formateTimeView($time)
{
    $formattedDate = Carbon::parse($time)->format('h:i A');
    return $formattedDate;
}

function formateInputDateView($date)
{   
    $formattedDate = Carbon::parse($date)->format('d/m/Y');
    return $formattedDate;
}

function searchForId($id, $array, $Arkey)
{
    foreach ($array as $key => $val) {
        if ($val[$Arkey] === $id) {
            return $key;
        }
    }
    return null;
}

function getDayDuration($start_date, $end_date)
{
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);

    // otherwise the  end date is excluded (bug?)
    $end->modify('+1 day');

    $interval = $end->diff($start);

    // total days
    $days = $interval->days;

    // create an iterateable period of date (P1D equates to 1 day)
    $period = new DatePeriod($start, new DateInterval('P1D'), $end);
    // best stored as array, so you can add more than one

    $holiday_leave = Holiday::whereBetween('date', [
        $start->format('Y-m-d'),
        $end->format('Y-m-d'),
    ])
        ->select('date')
        ->get()
        ->toArray();
    if (count($holiday_leave) > 0) {
        foreach ($period as $dt) {
            $curr = $dt->format('D');
            $currd = $dt->format('Y-m-d');
            $search_day = searchForId($currd, $holiday_leave, 'date');

            if ($search_day > -1) {
                if ($curr == 'Sat' || $curr == 'Sun') {
                    $days--;
                } else {
                    $days--;
                }
            } else {
                // substract if Saturday or Sunday
                if ($curr == 'Sat' || $curr == 'Sun') {
                    $days--;
                }
            }
        }
    } else {
        foreach ($period as $dt) {
            $curr = $dt->format('D');

            // substract if Saturday or Sunday
            if ($curr == 'Sat' || $curr == 'Sun') {
                $days--;
            }
        }
    }

    return $days;
}

function finacialYearLeave($startDate, $endDate, $id = null)
{
    $userId = $id;
    $financialYearStart = date('Y-m-d', strtotime($startDate)) ?? Config::get('constant.CURRENT_FINANCIAL_YEAR_START_DATE');
    $financialYearEnd = date('Y-m-d', strtotime($endDate))  ?? Config::get('constant.CURRENT_FINANCIAL_YEAR_END_DATE');
    $currentYearLeaves = Leaves::when(isset($userId), function($q) use($userId) {
            $q->where('user_id', $userId);
        })
        ->where('status', Leaves::APPROVED)
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
        ->when(empty($userId), function($q){
            $q->groupBy('user_id');
        })
        ->sum('day_duration');
        
    return $currentYearLeaves;
}

function finacialYearWfh($startDate, $endDate, $id = null)
{
    $userId = $id;
    $financialYearStart = date('Y-m-d', strtotime($startDate)) ?? Config::get('constant.CURRENT_FINANCIAL_YEAR_START_DATE');
    $financialYearEnd = date('Y-m-d', strtotime($endDate))  ?? Config::get('constant.CURRENT_FINANCIAL_YEAR_END_DATE');

    $finacialYearWfh = WorkFromHome::when(isset($userId), function($q) use($userId) {
            $q->where('user_id', $userId);
        })
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
        ->where('status', WorkFromHome::APPROVED)
        ->when(empty($userId), function($q){
            $q->groupBy('user_id');
        })
        ->sum('day_duration');

    return $finacialYearWfh;
}

function getTechnologies($data)
{
    $technologiesData = explode(",", $data);
    $technologies = Technology::all();
    $technologiesName = '';
    foreach ($technologies as $technology) {
        if (in_array($technology->id, $technologiesData)) {
            $technologiesName = $technologiesName . ", " . $technology->name;
        }
    }
    $technologyData = \Illuminate\Support\Str::limit($technologiesName, 100, '...');
    return $technologyData;
}

function meetingPresenterOrGuest($data)
{
    $presenterOrGuest = explode(",", $data);
    $presenterOrGuestDatas =  User::Active()->get();
    $name = '';
    foreach ($presenterOrGuestDatas as $presenterOrGuestData) {
        if (in_array($presenterOrGuestData->id, $presenterOrGuest)) {
            $name = $presenterOrGuestData->full_name;
        }
    }
    return $name;
}

function shortNameGenerate()
{
    $uniq = uniqid();
    $uniq = substr(md5($uniq), 0, 5) . '-' . substr(md5(sha1(time())), 0, 2);
    return strtoupper($uniq);
}

function taskStatusBadgeColorClass($data) 
{    
    $badgeColor = "";
    if($data == ProjectTask::BACKLOG){
        $badgeColor = 'badge-dark';
    } elseif ($data == ProjectTask::COMPLETED) {
        $badgeColor = 'badge-success';
    } elseif ($data == ProjectTask::QA) {
        $badgeColor = 'badge-info';
    } elseif ($data == ProjectTask::CLOSED) {
        $badgeColor = 'bg-danger';
    } else {
        $badgeColor = 'badge-primary';
    }
    return $badgeColor;
}

function taskPriorityBadgeColorClass($data) 
{    
    $badgeColor = "";
    if($data == ProjectTask::CRITICAL){
        $badgeColor = 'badge-danger';
    } elseif ($data == ProjectTask::HIGH) {
        $badgeColor = 'badge-danger';
    } elseif ($data == ProjectTask::MEDIUM) {
        $badgeColor = 'badge-warning';
    } else {
        $badgeColor = 'badge-success';
    }
    return $badgeColor;
}

function taskBillableBadgeColorClass($data) 
{    
    $badgeColor = "";
    if($data == ProjectTask::BILLABLE){
        $badgeColor = 'badge-success';
    } else {
        $badgeColor = 'badge-danger';
    }
    return $badgeColor;
}

function badgeLightColor()
{
    $color = array('#54ba4a26', '#7366ff1a', '#ffecc7', '#d3f4fe', '#ff336414', '#e9e9ee');
    $random_color_key = array_rand($color);
    $random_color_value = $color[$random_color_key];

    return $random_color_value;
}