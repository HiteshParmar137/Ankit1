<?php

namespace App\Interfaces;

interface ProjectTaskInterface
{
    public function getAllData($data);
    public function store(array $projectTaskDetails);
    public function update($projectTaskId, array $newProjectTaskDetails);
    public function delete($projectTaskId);
    public function edit($projectTaskId);
    public function show($projectTaskId);
}
