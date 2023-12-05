<?php

namespace App\Http\Controllers\InviteUser;

use App\Models\InviteUser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\InviteUserRequest;
use App\Interfaces\InviteUsersRepositoryInterface;
use App\Models\Roles;
use Illuminate\Support\Facades\Crypt;

class InviteUserController extends Controller
{   

    public function __construct(protected InviteUsersRepositoryInterface $inviteUserRepository)
    {
        $this->inviteUserRepository = $inviteUserRepository;
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
                $inviteUser =  $this->inviteUserRepository->getAllData($request->all());
                $view = \View::make('invite-user.partials.lists', [
                    'inviteUsers' => $inviteUser,
                    'return_back_handle' => http_build_query($request->all()),
                ]);
                $html = $view->render();
                return response()->json(['html' => $html, 'message' => 'Data Fetched Successfully!', 'success' =>  true]);
            } else {
                $roles = Roles::all();
                $query_params = $request->all();
                return view('invite-user.index', compact('roles','query_params'));
            }
        } catch (\Exception $e) {
            \Log::error($e);
            if ($request->ajax()) {
                return response()->json(['message' => 'Something is wrong', 'success' => false, 'error_msg' => $e->getMessage()], 500);
            } else {
                return redirect()->back()->with(['error' => 'Something is wrong', 'error_msg' => $e->getMessage()]);
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
        $roles = Roles::all();
        return view('invite-user.form', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(InviteUserRequest $request)
    {
        try { 

            $data = [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'role' => $request->role,
                'sent_by' => auth()->user()->id,
            ];
            $this->inviteUserRepository->store($data);
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
     * @param  \App\Models\InviteUser  $inviteUser
     * @return \Illuminate\Http\Response
     */
    public function show(InviteUser $inviteUser)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\InviteUser  $inviteUser
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {   
        try{
            $inviteUserId = Crypt::decrypt($id);
            $roles = Roles::all();
            $inviteUser = $this->inviteUserRepository->edit($inviteUserId);
            $view = \View::make('modal.invite-user-form-edit-data', [
                'inviteUser' => $inviteUser,
                'roles' => $roles,
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
     * @param  \App\Models\InviteUser  $inviteUser
     * @return \Illuminate\Http\Response
     */
    public function update(InviteUserRequest $request, $id)
    {
        try {
            $inviteUserId = Crypt::decrypt($id);
            $data = [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'role' => $request->role,
                'sent_by' => auth()->user()->id,
            ];
            $this->inviteUserRepository->update($inviteUserId, $data);
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
     * @param  \App\Models\InviteUser  $inviteUser
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        try {
            $inviteUserId = Crypt::decrypt($id);
            $this->inviteUserRepository->delete($inviteUserId);
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

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function register($data)
    {   
        if (!$data = InviteUser::where('id', Crypt::decrypt($data))->first()) {
	        abort(404);
	    }
	    else {
            return view('invite-user.register', compact('data'));
	    }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function userRegister(InviteUserRequest $request, $id)
    {
        try {
            $inviteUserId = Crypt::decrypt($id);
            $data = [
                'name' => $request->first_name. ' ' .$request->last_name,
                'email' => $request->email,
                'password' =>  \Hash::make($request->password),
                'role' => $request->role,
            ];
            $this->inviteUserRepository->userRegister($inviteUserId,$data);
            return redirect()
                ->route('login')
                ->with(['success' => 'Data store successfully.']);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return redirect()
                ->back()
                ->with([
                    'error' => 'Something is wrong',
                    'error_msg' => $e->getMessage(),
                ]);
        }
    }
}
