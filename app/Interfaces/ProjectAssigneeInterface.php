<?php

namespace App\Interfaces;

interface ProjectAssigneeInterface
{
    public function getAllData($data);
    public function store(array $projectAssigneeDetails);
    public function update($projectAssigneeId, array $newProjectAssigneeDetails);
    public function delete($projectAssigneeId);
    public function edit($projectAssigneeId);
}
