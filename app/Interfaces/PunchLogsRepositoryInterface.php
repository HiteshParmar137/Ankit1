<?php

namespace App\Interfaces;

interface PunchLogsRepositoryInterface
{
    public function getAllData($data);
    public function store($data);
    public function edit($punchLogId);
    public function update($punchLogId, array $punchLogDetails);
    public function delete($punchLogId);
    public function bulkUpload($data);
}