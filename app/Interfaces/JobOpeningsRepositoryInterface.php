<?php

namespace App\Interfaces;

interface JobOpeningsRepositoryInterface
{
    public function getAllData($data);
    public function store(array $jobOpeningsDetails);
    public function update($jobOpeningsId, array $newJobOpeningsDetails);
    public function delete($jobOpeningsId);
    public function edit($jobOpeningsId);
}
