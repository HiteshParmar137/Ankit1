<?php

namespace App\Repositories;

use App\Interfaces\MeetingsRepositoryInterface;
use App\Models\MeetingFeedBacks;
use App\Models\Meetings;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class MeetingsRepository extends BaseRepository implements MeetingsRepositoryInterface
{
    // get all Meeting
    public function getAllData($data)
    {
        // return Roles::paginate($data['record_per_page']);

        $queryData = Meetings::GetTextSearch($data)
        ->where(function ($q) use ($data) {
            if (isset($data['status']) && !empty($data['status'])) {
                $q->where('status', $data['status']);
            }
        })
        ->orderBy('date_time', 'desc');
        $query = $queryData->paginate(
            $data['record_per_page'] == 'all'
                ? $queryData->count()
                : $data['record_per_page']
        );
        return $query;
    }

    // Store Meeting
    public function store($data)
    {
        return Meetings::create($data);
    }

    // editing the specified resource
    public function edit($meetingId)
    {
        return Meetings::find($meetingId);
    }

    // Update the specified resource
    public function update($meetingId, array $newMeetingDetails)
    {
        return Meetings::find($meetingId)->update($newMeetingDetails);
    }

    // Delete specified resource
    public function delete($meetingId)
    {
        return Meetings::destroy($meetingId);
    }

    public function feedBack($data)
    {   
        $meeting = Meetings::where('id', $data['id'])->first();
        if ($data['type'] == 'cancel') {
            $meeting->status = Meetings::CANCELLED;
            $meeting->cancelled_on = Carbon::now();
            $meeting->cancelled_by = auth()->id();
            $meeting->save();
        } else {
            $meeting->status = $data['isComplete'];
            $meeting->save();
        }
        $meetingFeedback = new MeetingFeedBacks();
        $meetingFeedback->user_id = Auth::id();
        $meetingFeedback->meeting_id = $data['id'];
        $meetingFeedback->feedback = $data['feedback'];
        $meetingFeedback->save();

        return true;
    }
}