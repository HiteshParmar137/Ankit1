<?php

namespace App\Interfaces;

interface RolesRepositoryInterface
{
    public function getAllData($data);
    public function store(array $roleDetails);
    public function update($roleId, array $newRoleDetails);
    public function delete($roleId);
    public function edit($roleId);
}