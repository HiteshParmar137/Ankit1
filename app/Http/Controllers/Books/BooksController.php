<?php

namespace App\Http\Controllers\Books;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookRequest;
use App\Interfaces\BooksRepositoryInterface;
use App\Models\BookReadRequest;
use App\Models\Books;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class BooksController extends Controller
{   
    public function __construct(protected BooksRepositoryInterface $bookRepository)
    {
        $this->bookRepository = $bookRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            if ($request->ajax()) {
                $books = $this->bookRepository->getAllData($request->all());
                $view = \View::make('book.partials.lists', [
                    'books' => $books,
                    'return_back_handle' => http_build_query($request->all()),
                ]);
                $html = $view->render();
                return response()->json([
                    'html' => $html,
                    'message' => 'Data Fetched Successfully!',
                    'success' => true,
                ]);
            } else {
                $query_params = $request->all();
                return view('book.index', $query_params);
            }
        } catch (\Exception $e) {
            \Log::error($e);
            if ($request->ajax()) {
                return response()->json(
                    [
                        'message' => 'Something is wrong',
                        'success' => false,
                        'error_msg' => $e->getMessage(),
                    ],
                    500
                );
            } else {
                return redirect()
                    ->back()
                    ->with([
                        'error' => 'Something is wrong',
                        'error_msg' => $e->getMessage(),
                    ]);
            }
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(BookRequest $request)
    {
        try {
            $this->bookRepository->store($request->all());
            return response()->json(
                ['message' => 'Data store successfully.', 'success' => true],
                200
            );
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return response()->json(
                [
                    'message' => 'Something is wrong',
                    'success' => false,
                    'error_msg' => $e->getMessage(),
                ],
                500
            );
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Books  $books
     * @return \Illuminate\Http\Response
     */
    public function show(Books $books)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Books  $books
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $bookId = Crypt::decrypt($id);
            $book = $this->bookRepository->edit($bookId);
            $view = \View::make('modal.book-form-edit-data', [
                'book' => $book,
            ]);
            $html = $view->render();
            return response()->json([
                'html' => $html,
                'message' => 'Data Fetched Successfully!',
                'success' => true,
            ]);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return response()->json(
                [
                    'message' => 'Something is wrong',
                    'success' => false,
                    'error_msg' => $e->getMessage(),
                ],
                500
            );
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Books  $books
     * @return \Illuminate\Http\Response
     */
    public function update(BookRequest $request, $id)
    {
        try {
            $bookId = Crypt::decrypt($id);
            $data = [
                'name' => $request->name,
                'author' => $request->author,
                'isbn_number' => $request->isbn_number,
                'status' => $request->status,
            ];
            $this->bookRepository->update($bookId, $data);
            return response()->json(
                ['message' => 'Data store successfully.', 'success' => true],
                200
            );
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return response()->json(
                [
                    'message' => 'Something is wrong',
                    'success' => false,
                    'error_msg' => $e->getMessage(),
                ],
                500
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Books  $books
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        try {
            $bookId = Crypt::decrypt($id);
            $this->bookRepository->delete($bookId);
            return response()->json(
                ['message' => 'Data delete successfully.', 'success' => true],
                200
            );
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return response()->json(
                [
                    'message' => 'Something is wrong',
                    'success' => false,
                    'error_msg' => $e->getMessage(),
                ],
                500
            );
        }
    }

    public function requestToReadBook(BookRequest $request, $id)
    {
        try {
            $bookId = Crypt::decrypt($id);
            $tentativeReturnDate = dateFormate($request->tentative_return_date);
            $data = [
                'book_id' => $bookId,
                'user_id' => Auth::id(),
                'tentative_return_date' => $tentativeReturnDate,
                'status' => BookReadRequest::PENDING,
                'comment' => $request->comment,
            ];
            $this->bookRepository->requestToReadBook($data);
            return response()->json(
                ['message' => 'Data store successfully.', 'success' => true],
                200
            );
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return response()->json(
                [
                    'message' => 'Something is wrong',
                    'success' => false,
                    'error_msg' => $e->getMessage(),
                ],
                500
            );
        }
    }

    public function requestToReadBookLists(Request $request) 
    {
        try {
            if ($request->ajax()) {
                $requestToReadBook = $this->bookRepository->requestToReadBookLists($request->all());
                $view = \View::make('book.partials.request-read-book-lists', [
                    'requestToReadBook' => $requestToReadBook,
                    'return_back_handle' => http_build_query($request->all()),
                ]);
                $html = $view->render();
                return response()->json([
                    'html' => $html,
                    'message' => 'Data Fetched Successfully!',
                    'success' => true,
                ]);
            } else {
                $query_params = $request->all();
                return view('book.request-read-book', $query_params);
            }
        } catch (\Exception $e) {
            \Log::error($e);
            if ($request->ajax()) {
                return response()->json(
                    [
                        'message' => 'Something is wrong',
                        'success' => false,
                        'error_msg' => $e->getMessage(),
                    ],
                    500
                );
            } else {
                return redirect()
                    ->back()
                    ->with([
                        'error' => 'Something is wrong',
                        'error_msg' => $e->getMessage(),
                    ]);
            }
        }
    }

    public function requestToReadBookStatus(BookRequest $request, $id) 
    {
        try {
                $bookId = Crypt::decrypt($id);
                $data = [
                    'status' => $request->status,
                    'status_by' => auth()->user()->id,
                ];
                $this->bookRepository->requestToReadBookStatus($bookId, $data);
                return response()->json(
                    ['message' => 'Data store successfully.', 'success' => true],
                    200
                );
        } catch (\Exception $e) {
            \Log::error($e);
            return response()->json(
                [
                    'message' => 'Something is wrong',
                    'success' => false,
                    'error_msg' => $e->getMessage(),
                ],
                500
            );
        }
    }
}
