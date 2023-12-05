<?php

namespace App\Interfaces;

interface CandidateInterviewInterface
{
    public function getAllData($data);
    public function store(array $candidateDetails);
    public function update($candidateId, array $newBookDetails);
    public function delete($candidateId);
    public function edit($candidateId);
    public function interviewAssignedMe($candidateId);
    public function interviewFeedbackStore(array $data);
}