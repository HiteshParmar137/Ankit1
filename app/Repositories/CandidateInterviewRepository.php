<?php

namespace App\Repositories;

use App\Interfaces\CandidateInterviewInterface;
use App\Models\CandidateDetail;
use App\Models\CandidateInterview;
use Illuminate\Support\Facades\Auth;

class CandidateInterviewRepository extends BaseRepository implements CandidateInterviewInterface
{
    // get all Candidate Details
    public function getAllData($candidateDetailId)
    {
        $queryData = CandidateInterview::where('candidate_id', $candidateDetailId)->orderBy('date', 'desc')->get();

        return $queryData;
    }

    // Store CandidateDetail
    public function store($data)
    {   
        CandidateInterview::create($data);
        return true;
    }

    // editing the specified resource
    public function edit($candidateInterviewId)
    {
        $candidateInterview = CandidateInterview::find($candidateInterviewId);
        return $candidateInterview;
    }

    // Update the specified resource
    public function update($candidateInterviewId, array $newcandidateInterview)
    {   
        tap(CandidateInterview::find($candidateInterviewId))->update($newcandidateInterview);
        return true;
    }

    // Delete specified resource
    public function delete($candidateInterviewId)
    {
        return CandidateInterview::destroy($candidateInterviewId);
    }

    // get all Candidate Details
    public function interviewAssignedMe($data)
    {
        $queryData = CandidateInterview::with(['candidateDetails', 'candidateDetails.jobOpening' ,'user', 'interviewstage'])
            ->when(!auth()->user()->isAdmin() && !auth()->user()->isHr(), function($query){
                $query->where('user_id', auth::user()->id);
            })
        ->orderBy($data['sort_column'], $data['sort_type']);
        if(isset($data['position'])) {
            $position = $data['position'];
            $queryData->whereHas('candidateDetails', function($query) use($position){
                $query->where('position_id', $position);
            });
        }

        $query = $queryData->paginate(
            $data['record_per_page'] == 'all'
                ? $queryData->count()
                : $data['record_per_page']
        );
        return $query;
    }

    public function interviewFeedbackStore($data)
    {
        $candidateInterview = CandidateInterview::find($data['candidate_interview_id']);
        $candidateInterview->status = $data['status'];
        $candidateInterview->feedback = $data['feedback'];
        $candidateInterview->update();

        $candidateDetail = CandidateDetail::find($candidateInterview->candidate_id);
        $candidateDetail->eligible_for_future_hiring = $data['eligible_for_future_hiring'];
        $candidateDetail->update();
        return true;
    }
}