<?php

namespace App\Repositories;

use App\Interfaces\UsersRepositoryInterface;
use App\Models\User;

class UsersRepository extends BaseRepository implements UsersRepositoryInterface
{
    // get all Monthly Events.
    public function getAllData($data)
    {
        return User::paginate($data['record_per_page']);
    }
}