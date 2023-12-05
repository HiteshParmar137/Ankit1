<?php

namespace App\Repositories;

use App\Interfaces\BooksRepositoryInterface;
use App\Jobs\BookJob;
use App\Models\BookReadRequest;
use App\Models\Books;
use App\Models\NewBookRequest;
use App\Models\User;

class BooksRepository extends BaseRepository implements BooksRepositoryInterface
{
    // get all books
    public function getAllData($data)
    {
        $queryData = Books::with(
            ['requestToReadBook' => function($query) {
                $query->where('user_id',  auth()->user()->id);
            }])
            ->GetTextSearch($data['character_search'])->orderBy($data['sort_column'], $data['sort_type']);
        $query = $queryData->paginate(
            $data['record_per_page'] == 'all'
                ? $queryData->count()
                : $data['record_per_page']
        );
        return $query;
    }

    // Store book
    public function store($data)
    {
        return Books::create($data);
    }

    // editing the specified resource
    public function edit($bookId)
    {
        return Books::find($bookId);
    }

    // Update the specified resource
    public function update($bookId, array $newbookDetails)
    {
        return Books::find($bookId)->update($newbookDetails);
    }

    // Delete specified resource
    public function delete($bookId)
    {
        return Books::destroy($bookId);
    }

    public function requestToReadBook($data)
    {
        $bookReadRequest = BookReadRequest::create($data);
        $type = BookReadRequest::TYPE;
        dispatch(new BookJob($bookReadRequest, $type));         
        return true;
    }

    public function requestToReadBookLists($data)
    {
        $queryData = BookReadRequest::with(['book:id,name,author', 'user:id,first_name,last_name'])
            ->GetTextSearch($data['character_search'])
            ->orderBy($data['sort_column'], $data['sort_type']);
        $query = $queryData->paginate(
            $data['record_per_page'] == 'all'
                ? $queryData->count()
                : $data['record_per_page']
        );

        return $query;
    }

    public function requestToReadBookStatus($bookId, array $newbookDetails)
    {   
        $bookReadRequest = BookReadRequest::where('book_id', $bookId)
            ->where('status', BookReadRequest::PENDING)
            ->first();
        $bookReadRequest->status = $newbookDetails['status'];
        $bookReadRequest->status_by = $newbookDetails['status_by'];
        $bookReadRequest->update();
        return $bookReadRequest;
    }

    // get all books
    public function newBookGetAllData($data)
    {
        $queryData = NewBookRequest::with('user')
            ->CreatedByItself()
            ->orderBy($data['sort_column'], $data['sort_type']);
        $query = $queryData->paginate(
            $data['record_per_page'] == 'all'
                ? $queryData->count()
                : $data['record_per_page']
        );
        return $query;
    }

    // Store book
    public function newBookStore($data)
    {
        $newBookData = NewBookRequest::create($data);
        $type = NewBookRequest::TYPE;
        dispatch(new BookJob($newBookData, $type));  
        return true;
    }

    // editing the specified resource
    public function newBookEdit($bookId)
    {
        $newBookData = NewBookRequest::find($bookId);
        return $newBookData;
    }

    // Update the specified resource
    public function newBookUpdate($bookId, array $newbookDetails)
    {
        $newBookData = NewBookRequest::whereId($bookId)->update($newbookDetails);
        $type = NewBookRequest::TYPE;
        dispatch(new BookJob($newBookData, $type));  
        return true;
    }

    // Delete specified resource
    public function newBookDelete($bookId)
    {
        return NewBookRequest::destroy($bookId);
    }
}