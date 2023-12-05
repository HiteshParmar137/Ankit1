<?php

namespace App\Interfaces;

interface FreelancerInterface
{   
    public function getAllData($data);
    public function store(array $freelancerDetails);
    public function update($freelancerId, array $freelancerDetails);
    public function delete($freelancerId);
    public function edit($freelancerId);
}