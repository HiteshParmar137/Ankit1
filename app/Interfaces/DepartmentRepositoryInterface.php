<?php

namespace App\Interfaces;

interface DepartmentRepositoryInterface
{
    public function getAllData($data);
    public function store(array $departmentDetails);
    public function update($departmentId, array $newDepartmentDetails);
    public function delete($departmentId);
    public function edit($departmentId);
}
