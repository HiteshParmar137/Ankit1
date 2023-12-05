<?php

namespace App\Http\Controllers\Books;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookRequest;
use App\Interfaces\BooksRepositoryInterface;
use App\Models\NewBookRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class NewBookRequestController extends Controller
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
                $books = $this->bookRepository->newBookGetAllData($request->all());
                $view = \View::make('book.partials.new-book-request-lists', [
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
                return view('book.new-book-request', $query_params);
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
            $data = [
                'name' => $request->name,
                'author' => $request->author,
                'request_by' => auth()->user()->id,
                'comment' => $request->comment
            ];
            $this->bookRepository->newBookStore($data);
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
     * @param  \App\Models\NewBookRequest  $newBookRequest
     * @return \Illuminate\Http\Response
     */
    public function show(NewBookRequest $newBookRequest)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\NewBookRequest  $newBookRequest
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $bookId = Crypt::decrypt($id);
            $newBook = $this->bookRepository->newBookedit($bookId);
            $view = \View::make('modal.new-book-request-form-edit-data', [
                'newBook' => $newBook,
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
     * @param  \App\Models\NewBookRequest  $newBookRequest
     * @return \Illuminate\Http\Response
     */
    public function update(BookRequest $request, $id)
    {
        try {
            $bookId = Crypt::decrypt($id);

            $data = [
                'name' => $request->name,
                'author' => $request->author,
                'request_by' => auth()->user()->id,
                'comment' => $request->comment
            ];

            $this->bookRepository->newBookUpdate($bookId, $data);
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
     * @param  \App\Models\NewBookRequest  $newBookRequest
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        try {
            $bookId = Crypt::decrypt($id);
            $this->bookRepository->newBookdelete($bookId);
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
}
