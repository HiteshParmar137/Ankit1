<?php

namespace App\Interfaces;

interface ProjectInterface
{
    public function getAllData($data);
    public function store(array $projectDetails);
    public function update($projectId, array $newProjectDetails);
    public function delete($projectId);
    public function edit($projectId);
    public function show($projectId);
    public function myProjectData($data);
}
