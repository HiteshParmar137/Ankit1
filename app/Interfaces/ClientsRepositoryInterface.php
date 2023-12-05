<?php

namespace App\Interfaces;

interface ClientsRepositoryInterface
{
    public function getAllData($data);
    public function store(array $roleDetails);
    public function update($clientId, array $newClientDetails);
    public function delete($clientId);
    public function edit($clientId);
    public function show($clientId);
}