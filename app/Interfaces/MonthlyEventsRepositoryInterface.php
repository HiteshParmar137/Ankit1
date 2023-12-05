<?php

namespace App\Interfaces;

interface MonthlyEventsRepositoryInterface
{
    public function getAllData($data);
    public function store(array $monthlyEventsDetails);
    public function update($monthlyEventsId, array $newMonthlyEventsDetails);
    public function delete($monthlyEventsId);
    public function edit($monthlyEventsId);
}
