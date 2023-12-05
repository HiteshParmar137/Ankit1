<?php

namespace App\Interfaces;

interface ResourceRequestInterface
{
    public function getAllData($data);
    public function store(array $resourceRequestDetails);
    public function update($resourceRequestId, array $newResourceRequestDetails);
    public function delete($resourceRequestId);
    public function edit($resourceRequestId);
    public function getRequestedResourceAllData($data);
    public function requestedResourceFeedback($resourceRequesID, array $resourceRequestData);
}
