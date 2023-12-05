<?php

namespace App\Interfaces;

interface WorkFromHomeRepositoryInterface
{
    public function getAllData($data, $finacialYearStartDate, $finacialYearEndDate);
    public function store(array $wfhDetails);
    public function update($wfhId, array $newWfhDetails);
    public function delete($wfhId);
    public function edit($wfhId);
    public function show($wfhId, $requestToUserId);
    public function teamWorkFromHome($requestToUserId, $data);
    public function workFromHomeFeedback($wfhId, $wfhData);
    public function getAllWfhData($data);
    public function financialYearData($data);
}
