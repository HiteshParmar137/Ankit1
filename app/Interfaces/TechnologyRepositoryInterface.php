<?php

namespace App\Interfaces;

interface TechnologyRepositoryInterface
{
    public function getAllData($data);
    public function store(array $technologyDetails);
    public function update($technologyId, array $newTechnologyDetails);
    public function delete($technologyId);
    public function edit($technologyId);
}