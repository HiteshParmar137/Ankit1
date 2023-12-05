<?php

namespace App\Interfaces;

interface DesignationRepositoryInterface
{
    public function getAllData($data);
    public function store(array $designationDetails);
    public function update($designationId, array $newDesignationDetails);
    public function delete($designationId);
    public function edit($designationId);
}
