<?php

namespace App\Http\Controllers\Roles;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoleRequest;
use App\Interfaces\RolesRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class RolesController extends Controller
{
    public function __construct(protected RolesRepositoryInterface $roleRepository)
    {
        $this->roleRepository = $roleRepository;
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
                $roles = $this->roleRepository->getAllData($request->all());
                $view = \View::make('role.partials.lists', [
                    'roles' => $roles,
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
                return view('role.index', $query_params);
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
    public function store(RoleRequest $request)
    {
        try {
            $slug = strtolower($request->name);
            $slugName = str_replace(' ', '-', $slug);
            $data = [
                'name' => $request->name,
                'slug' => $slugName,
            ];
            $this->roleRepository->store($data);
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
     * @param  \App\Models\Roles  $holiday
     * @return \Illuminate\Http\Response
     */
    public function show(Roles $roles)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Roles  $holiday
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $roleId = Crypt::decrypt($id);
            $role = $this->roleRepository->edit($roleId);
            $view = \View::make('modal.role-form-edit-data', [
                'role' => $role,
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
     * @param  \App\Models\Roles  $holiday
     * @return \Illuminate\Http\Response
     */
    public function update(RoleRequest $request, $id)
    {
        try {
            $roleId = Crypt::decrypt($id);
            $slug = strtolower($request->name);
            $slugName = str_replace(' ', '-', $slug);
            $data = [
                'name' => $request->name,
                'slug' => $slugName,
            ];
            $this->roleRepository->update($roleId, $data);
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
     * @param  \App\Models\Roles  $holiday
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        try {
            $roleId = Crypt::decrypt($id);
            $this->roleRepository->delete($roleId);
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