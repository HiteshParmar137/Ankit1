<?php

namespace App\Interfaces;

interface InviteUsersRepositoryInterface
{
    public function getAllData($data);
    public function store(array $inviteUserDeDetails);
    public function update($inviteUserId, array $inviteUserDeDetails);
    public function delete($inviteUserId);
    public function edit($jobOpeningsId);
    public function userRegister($inviteUserId, $inviteUserDeDetails);
}