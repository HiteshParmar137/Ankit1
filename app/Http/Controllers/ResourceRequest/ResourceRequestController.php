<?php

namespace App\Http\Controllers\ResourceRequest;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResourceRequest as RequestsResourceRequest;
use App\Interfaces\ResourceRequestInterface;
use App\Models\ResourceRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class ResourceRequestController extends Controller
{   
    public function __construct(protected ResourceRequestInterface $resourceRequestRepository)
    {
        $this->resourceRequestRepository = $resourceRequestRepository;
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
                $resourceRequest = $this->resourceRequestRepository->getAllData($request->all());
                $view = \View::make('resource-request.partials.lists', [
                    'resourceRequest' => $resourceRequest,
                    'return_back_handle' => http_build_query($request->all()),
                ]);
                $html = $view->render();
                return response()->json([
                    'html' => $html,
                    'message' => 'Data Fetched Successfully!',
                    'success' => true,
                ]);
            } else {
                $users = User::with('roles')->where('id','!=',Auth::id())
                            ->Active()
                            ->select('first_name', 'last_name' ,'id')
                            ->get();
                $query_params = $request->all();
                return view('resource-request.index', compact('users', 'query_params'));
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
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(RequestsResourceRequest $request)
    {
        try {

            $data = [
                "user_id" => Auth::id(),
                "request_to" => $request->request_to,
                "name" => $request->name,
            ];
            $this->resourceRequestRepository->store($data);
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
     * @param  \App\Models\ResourceRequest  $resourceRequest
     * @return \Illuminate\Http\Response
     */
    public function show(ResourceRequest $resourceRequest)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ResourceRequest  $resourceRequest
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $resourceRequestId = Crypt::decrypt($id);
            $resourceRequest = $this->resourceRequestRepository->edit($resourceRequestId);
            $users = User::with('roles')->where('id','!=',Auth::id())
                        ->Active()
                        ->select('first_name', 'last_name' ,'id')
                        ->get();
            $view = \View::make('modal.resource-requests-form-edit-data', [
                'resourceRequest' => $resourceRequest,
                'users' => $users,
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
     * @param  \App\Models\ResourceRequest  $resourceRequest
     * @return \Illuminate\Http\Response
     */
    public function update(RequestsResourceRequest $request, $id)
    {
        try {
            $resourceRequestId = Crypt::decrypt($id);

            $data = [
                "user_id" => Auth::id(),
                "request_to" => $request->request_to,
                "name" => $request->name,
            ];
            $this->resourceRequestRepository->update($resourceRequestId, $data);
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
     * @param  \App\Models\ResourceRequest  $resourceRequest
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        try {
            $resourceRequestId = Crypt::decrypt($id);
            $this->resourceRequestRepository->delete($resourceRequestId);
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
     * Display a listing of the requested resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function requestedResource(Request $request)
    {
        try {
            if ($request->ajax()) {
                $resourceRequest = $this->resourceRequestRepository->getRequestedResourceAllData($request->all());
                $view = \View::make('resource-request.partials.requested-resource-lists', [
                    'resourceRequest' => $resourceRequest,
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
                return view('resource-request.requested-resource', $query_params);
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

    public function requesteResourceFeedBack(RequestsResourceRequest $request)
    {   
        try {
            $resourceRequestId = $request->id;
            $data = [
                'status' => $request->status,
                'reason' => $request->reason,
            ];
            if($request->status == ResourceRequest::REJECTED){
                $validator = \Validator::make($request->all(), [
                    'reason' => 'required|min:3|max:255',
                ]);
                if ($validator->fails()) {
                    return response()->json(['message' => $validator->errors()->first(), 'success' => false]);
                }
            }
            $this->resourceRequestRepository->requestedResourceFeedback($resourceRequestId, $data);
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
}
