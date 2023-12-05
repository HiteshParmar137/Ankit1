<?php

namespace App\Http\Controllers\Policies;

use App\Http\Controllers\Controller;
use App\Http\Requests\PolicyRequest;
use App\Interfaces\PoliciesRepositoryInterface;
use App\Models\Policies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class PoliciesController extends Controller
{

    public function __construct(protected PoliciesRepositoryInterface $policyRepository)
    {
        $this->policyRepository = $policyRepository;
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
                $policies =  $this->policyRepository->getAllData($request->all());
                $view = \View::make('policy.partials.lists', [
                    'policies' => $policies,
                    'return_back_handle' => http_build_query($request->all()),
                ]);
                $html = $view->render();
                return response()->json(['html' => $html, 'message' => 'Data Fetched Successfully!', 'success' =>  true]);
            } else {
                $query_params = $request->all();
                return view('policy.index', $query_params);
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
       
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PolicyRequest $request)
    {
        try {
            $data = $request->all();
            $this->policyRepository->store($data);
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
     * @param  \App\Models\Policies  $policies
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $policyId      = Crypt::decrypt($id);
            $policy        = $this->policyRepository->show($policyId);
            $policyDetails = $policy->description;
            $policyTitle   = $policy->title;
            return response()->json(['policyDetails' => $policyDetails, 'policyTitle' => $policyTitle, 'message' => 'Data Fetched Successfully!', 'success' =>  true]);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return response()->json(['message' => 'Something is wrong', 'success' => false, 'error_msg' => $e->getMessage()], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Policies  $policies
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $policyId = Crypt::decrypt($id);
            $policy = $this->policyRepository->edit($policyId);
            $view = \View::make('modal.policy-form-edit-data', [
                'policy' => $policy,
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
     * @param  \App\Models\Policies  $policies
     * @return \Illuminate\Http\Response
     */
    public function update(PolicyRequest $request, $id)
    {
        try {
            $policyId = Crypt::decrypt($id);
            $data = $request->only('title', 'description');
            $this->policyRepository->update($policyId, $data);
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
     * @param  \App\Models\Policies  $policies
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        try {
            $policyId = Crypt::decrypt($id);
            $this->policyRepository->delete($policyId);
            return response()->json(['message' => 'Data delete successfully.', 'success' => true], 200);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return response()->json(['message' => 'Something is wrong', 'success' => false, 'error_msg' => $e->getMessage()], 500);
        }
    }
}
