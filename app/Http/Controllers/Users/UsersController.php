<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Interfaces\UsersRepositoryInterface;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function __construct(protected UsersRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
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
                $users = $this->userRepository->getAllData($request->all());
                $view = \View::make('user.partials.lists', [
                    'users' => $users,
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
                return view('user.index', $query_params);
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
}
