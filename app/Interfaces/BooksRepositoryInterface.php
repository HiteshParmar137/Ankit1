<?php

namespace App\Interfaces;

interface BooksRepositoryInterface
{
    public function getAllData($data);
    public function store(array $bookDetails);
    public function update($bookId, array $newBookDetails);
    public function delete($bookId);
    public function edit($bookId);
    public function requestToReadBook(array $bookReadRequest);
    public function requestToReadBookLists($data);
    public function requestToReadBookStatus($bookId, array $newBookReadRequestDetails);
    public function newBookGetAllData($data);
    public function newBookStore(array $bookDetails);
    public function newBookUpdate($bookId, array $newBookDetails);
    public function newBookDelete($bookId);
    public function newBookEdit($bookId);
}