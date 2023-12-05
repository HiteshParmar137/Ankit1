<?php

namespace App\Interfaces;

interface PoliciesRepositoryInterface
{
    public function getAllData($data);
    public function store(array $policyDetails);
    public function update($policyId, array $newPolicyDetails);
    public function delete($policyId);
    public function edit($policyId);
    public function show($policyId);
}
