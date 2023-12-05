<?php

namespace App\Repositories;

use App\Interfaces\ResourceRequestInterface;
use App\Jobs\ResourceRequestJob;
use App\Models\ResourceRequest;

class ResourceRequestRepository extends BaseRepository implements ResourceRequestInterface
{
    // get all Resource Request
    public function getAllData($data)
    {
        $queryData = ResourceRequest::GetTextSearch($data)->CreatedByItself()->orderBy($data['sort_column'], $data['sort_type']);
        $query = $queryData->paginate(
            $data['record_per_page'] == 'all'
                ? $queryData->count()
                : $data['record_per_page']
        );
        return $query;
    }

    // Store Resource Request
    public function store($data)
    {   
        $resourceRequest = ResourceRequest::create($data);
        $type = ResourceRequest::MAIL_TYPE;
        dispatch(new ResourceRequestJob($resourceRequest, $type));
        return true;
    }

    // editing the specified resource
    public function edit($resourceRequestId)
    {
        return ResourceRequest::find($resourceRequestId);
    }

    // Update the specified resource
    public function update($resourceRequestId, array $newResourceRequestDetails)
    {   
        $resourceRequest = tap(ResourceRequest::find($resourceRequestId))->update($newResourceRequestDetails);
        $type = ResourceRequest::MAIL_TYPE;
        dispatch(new ResourceRequestJob($resourceRequest, $type));
        return true;
    }

    // Delete Resource Request
    public function delete($resourceRequestId)
    {
        return ResourceRequest::destroy($resourceRequestId);
    }

    // get all Resource Request
    public function getRequestedResourceAllData($data)
    {
        $queryData = ResourceRequest::GetTextSearch($data)
        ->when(!auth()->user()->isAdmin(),function($q){
            $q->RequestToUser();
        })->orderBy($data['sort_column'], $data['sort_type']);
        $query = $queryData->paginate(
            $data['record_per_page'] == 'all'
                ? $queryData->count()
                : $data['record_per_page']
        );
        return $query;
    }

    public function requestedResourceFeedback($resourceRequestId, array $newResourceRequestDetails)
    {
        $resourceRequest = tap(ResourceRequest::find($resourceRequestId))->update($newResourceRequestDetails);
        $type = ResourceRequest::FEEDBACK_MAIL_TYPE;
        dispatch(new ResourceRequestJob($resourceRequest, $type));
        return true;
    }
}