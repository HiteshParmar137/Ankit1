<?php

namespace App\Interfaces;

interface MeetingsRepositoryInterface
{
    public function getAllData($data);
    public function store(array $mettingDetails);
    public function update($mettingId, array $newMettingDetails);
    public function delete($mettingId);
    public function edit($mettingId);
    public function feedBack($data);
}