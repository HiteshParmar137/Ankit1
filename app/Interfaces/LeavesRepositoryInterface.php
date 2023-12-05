<?php

namespace App\Interfaces;

interface LeavesRepositoryInterface
{
    public function getAllData($data, $finacialYearStartDate, $finacialYearEndDate);
    public function store(array $leaveDetails);
    public function update($leaveId, array $newLeaveDetails);
    public function delete($leaveId);
    public function edit($leaveId);
    public function show($leaveId, $requestToUserId);
    public function teamLeaves($requestToUserId, $data);
    public function leaveFeedback($leaveID, $leaveData);
    public function getAllLeaveData($data);
    public function leaveDebited($data, $isDebited);
    public function financialYearData($data);
}
