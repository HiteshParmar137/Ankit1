<?php

namespace App\Http\Controllers\Designation;

use App\Http\Controllers\Controller;
use App\Http\Requests\DesignationRequest;
use App\Interfaces\DesignationRepositoryInterface;
use App\Models\Department;
use App\Models\Designation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class DesignationController extends Controller
{
    public function __construct(protected DesignationRepositoryInterface $designationRepository)
    {
        $this->designationRepository = $designationRepository;
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
                $designations =  $this->designationRepository->getAllData($request->all());
                // $departments = Department::all();
                $view = \View::make('designation.partials.lists', [
                    'designations' => $designations,
                    'return_back_handle' => http_build_query($request->all()),
                ]);
                $html = $view->render();
                return response()->json(['html' => $html, 'message' => 'Data Fetched Successfully!', 'success' =>  true]);
            } else {
                $query_params = $request->all();
                $departments = Department::all();
                return view('designation.index', compact('query_params', 'departments'));
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
    public function store(DesignationRequest $request)
    {
        try {
            $data = $request->all();
            $this->designationRepository->store($data);
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
     * @param  \App\Models\Designation  $designation
     * @return \Illuminate\Http\Response
     */
    public function show(Designation $designation)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Designation  $designation
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $designationsId = Crypt::decrypt($id);
            $designation = $this->designationRepository->edit($designationsId);
            $departments = Department::all();
            $view = \View::make('modal.designation-form-edit-data', [
                'departments' => $departments,
                'designation' => $designation,
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
     * @param  \App\Models\Designation  $designation
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $designationsId = Crypt::decrypt($id);
            $data = $request->only(['title', 'department_id']);
            $this->designationRepository->update($designationsId, $data);
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
     * @param  \App\Models\Designation  $designation
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        try {
            $designationsId = Crypt::decrypt($id);
            $this->designationRepository->delete($designationsId);
            return response()->json(['message' => 'Data delete successfully.', 'success' => true], 200);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return response()->json(['message' => 'Something is wrong', 'success' => false, 'error_msg' => $e->getMessage()], 500);
        }
    }
}
