<?php

namespace App\Repositories;

use App\Interfaces\ClientsRepositoryInterface;
use App\Models\Clients;

class ClientsRepository extends BaseRepository implements ClientsRepositoryInterface
{
    // get all Clients
    public function getAllData($data)
    {
        $queryData = Clients::with('countryName:id,name', 'companyCountry:id,name')->GetTextSearch($data['character_search'])->orderBy($data['sort_column'], $data['sort_type']);
        $query = $queryData->paginate(
            $data['record_per_page'] == 'all'
                ? $queryData->count()
                : $data['record_per_page']
        );
        return $query;
    }

    // Store Clients
    public function store($data)
    {
        Clients::create($data);
        return true;
    }

    // editing the specified resource
    public function edit($clientId)
    {
        return Clients::find($clientId);
    }

    // Update the specified resource
    public function update($clientId, array $newClientDetails)
    {
        Clients::find($clientId)->update($newClientDetails);
        return true;
    }

    // Delete specified resource
    public function delete($clientId)
    {
        return Clients::destroy($clientId);
    }

    // editing the specified resource
    public function show($clientId)
    {
        return Clients::find($clientId);
    } 
}