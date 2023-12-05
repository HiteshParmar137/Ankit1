<?php

namespace App\Interfaces;

interface CandidateDetailsInterface
{
    public function getAllData($data);
    public function store(array $candidateDetails);
    public function update($candidateId, array $newBookDetails);
    public function delete($candidateId);
    public function edit($candidateId);
    public function show($candidateId);
}