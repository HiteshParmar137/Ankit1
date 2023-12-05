<?php

namespace App\Http\Controllers\JobOpenings;

use App\Http\Controllers\Controller;
use App\Http\Requests\JobOpeningsRequest;
use App\Interfaces\JobOpeningsRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class JobOpeningsController extends Controller
{

    public function __construct(protected JobOpeningsRepositoryInterface $jobOpeningsRepository)
    {
        $this->jobOpeningsRepository = $jobOpeningsRepository;
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
                $jobOpenings =  $this->jobOpeningsRepository->getAllData($request->all());
                $view = \View::make('job-openings.partials.lists', [
                    'jobOpenings' => $jobOpenings,
                    'return_back_handle' => http_build_query($request->all()),
                ]);
                $html = $view->render();
                return response()->json(['html' => $html, 'message' => 'Data Fetched Successfully!', 'success' =>  true]);
            } else {
                $query_params = $request->all();
                return view('job-openings.index', $query_params);
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
    public function store(JobOpeningsRequest $request)
    {
        try {
            $this->jobOpeningsRepository->store($request->all());
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
     * @param  \App\Models\JobOpenings  $jobOpenings
     * @return \Illuminate\Http\Response
     */
    public function show(JobOpenings $jobOpenings)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\JobOpenings  $jobOpenings
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $jobOpeningId = Crypt::decrypt($id);
            $jobOpening = $this->jobOpeningsRepository->edit($jobOpeningId);
            $view = \View::make('modal.job-opening-form-edit-data', [
                'jobOpening' => $jobOpening,
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
     * @param  \App\Models\JobOpenings  $jobOpenings
     * @return \Illuminate\Http\Response
     */
    public function update(JobOpeningsRequest $request, $id)
    {
        try {
            $jobOpeningId = Crypt::decrypt($id);
            $data = $request->only(['name', 'number_of_position', 'description']);
            $this->jobOpeningsRepository->update($jobOpeningId, $data);
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
     * @param  \App\Models\JobOpenings  $jobOpenings
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        try {
            $jobOpeningId = Crypt::decrypt($id);
            $this->jobOpeningsRepository->delete($jobOpeningId);
            return response()->json(['message' => 'Data delete successfully.', 'success' => true], 200);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return response()->json(['message' => 'Something is wrong', 'success' => false, 'error_msg' => $e->getMessage()], 500);
        }
    }
}
