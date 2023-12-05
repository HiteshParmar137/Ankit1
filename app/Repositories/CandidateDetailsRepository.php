<?php

namespace App\Repositories;

use App\Interfaces\CandidateDetailsInterface;
use App\Models\CandidateDetail;

class CandidateDetailsRepository extends BaseRepository implements CandidateDetailsInterface
{
    // get all Candidate Details
    public function getAllData($data)
    {
        $queryData = CandidateDetail::with('interviews.interviewstage')->GetTextSearch($data['character_search'])->orderBy($data['sort_column'], $data['sort_type']);

        if(isset($data['eligible_for_future_hiring_filter'])) {
            $queryData->where('eligible_for_future_hiring', $data['eligible_for_future_hiring_filter']);
        }
        
        if(isset($data['position_filter'])) {
            $queryData->where('position_id', $data['position_filter']);
        }

        $query = $queryData->paginate(
            $data['record_per_page'] == 'all'
                ? $queryData->count()
                : $data['record_per_page']
        );
        return $query;
    }

    // Store CandidateDetail
    public function store($data)
    {
        CandidateDetail::create($data);
        return true;
    }

    // editing the specified resource
    public function edit($candidateDetailId)
    {
        $candidateDetail = CandidateDetail::findOrFail($candidateDetailId);
        return $candidateDetail;
    }


    // show the specified resource
    public function show($candidateDetailId)
    {   
        $candidateDetail = CandidateDetail::findOrFail($candidateDetailId);
        return $candidateDetail;
    }

    // Update the specified resource
    public function update($candidateDetailId, array $newcandidateDetails)
    {   
        tap(CandidateDetail::find($candidateDetailId))->update($newcandidateDetails);
        return true;
    }

    // Delete specified resource
    public function delete($candidateDetailId)
    {
        return CandidateDetail::destroy($candidateDetailId);
    }
}