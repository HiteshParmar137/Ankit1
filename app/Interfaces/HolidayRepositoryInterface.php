<?php

namespace App\Interfaces;

interface HolidayRepositoryInterface
{
    public function getAllData($data);
    public function store(array $holidayDetails);
    public function update($holidayId, array $newHolidayDetails);
    public function delete($holidayId);
    public function edit($holidayId);
}
