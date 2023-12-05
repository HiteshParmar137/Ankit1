<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use App\Http\Requests\CandidateDetailsRequest;
use App\Http\Requests\JobOpeningsRequest;
use App\Interfaces\CandidateDetailsInterface;
use App\Interfaces\JobOpeningsRepositoryInterface;
use App\Models\CandidateDetail;
use App\Models\InterviewStage;
use App\Models\JobOpenings;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CandidateDetailController extends Controller
{   
    public function __construct(protected CandidateDetailsInterface $candidateDetailsRepository, protected JobOpeningsRepositoryInterface $jobOpeningsRepository)
    {   
        $this->candidateDetailsRepository = $candidateDetailsRepository;
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
                $candidateDetails =  $this->candidateDetailsRepository->getAllData($request->all());
                $view = \View::make('recruitment.partials.lists', [
                    'candidateDetails' => $candidateDetails,
                    'return_back_handle' => http_build_query($request->all()),
                ]);
                $html = $view->render();
                return response()->json(['html' => $html, 'message' => 'Data Fetched Successfully!', 'success' =>  true]);
            } else {
                $queryParams = $request->all();
                $jobOpenings = JobOpenings::all();
                return view('recruitment.index', compact('jobOpenings', 'queryParams'));
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
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CandidateDetailsRequest $request)
    {
        try {
            
            $this->candidateDetailsRepository->store($request->all());
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
     * @param  \App\Models\CandidateDetail  $candidateDetail
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request,$id)
    {
        try {
            if ($request->ajax()) {
                $candidateDetailId = decrypt($id);
                $candidateDetails = $this->candidateDetailsRepository->show($candidateDetailId);
                $view = \View::make('recruitment.partials.details_data', [
                    'candidateDetails' => $candidateDetails,
                ]);
                $html = $view->render();
                return response()->json([
                    'html' => $html,
                    'message' => 'Data Fetched Successfully!',
                    'success' => true,
                ]);
            } else {
                $users = User::select('first_name', 'last_name' ,'id')->get();
                $interviewStages = InterviewStage::all();
                $candidateDetailId = $id;
                return view('recruitment.partials.details', compact('candidateDetailId', 'users', 'interviewStages'));
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
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CandidateDetail  $candidateDetail
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $candidateDetailsId = decrypt($id);
            $candidateDetails = $this->candidateDetailsRepository->edit($candidateDetailsId);
            $jobOpenings = JobOpenings::all();
            $view = \View::make('modal.candidate-details-form-edit-data', [
                'candidateDetails' => $candidateDetails,
                'jobOpenings' => $jobOpenings,
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
     * @param  \App\Models\CandidateDetail  $candidateDetail
     * @return \Illuminate\Http\Response
     */
    public function update(CandidateDetailsRequest $request, $id)
    {
    
        try {
            $candidateDetailsId = decrypt($id);
            $this->candidateDetailsRepository->update($candidateDetailsId, $request->all());
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
     * @param  \App\Models\CandidateDetail  $candidateDetail
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        try {
            $candidateDetailsId = decrypt($id);
            $this->candidateDetailsRepository->delete($candidateDetailsId);
            return response()->json(
                ['message' => 'Data deleted successfully.', 'success' => true],
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

    public function jobOpeningStore(JobOpeningsRequest $request){
        try {
            $jobOpening = $this->jobOpeningsRepository->store($request->all());
            $jobOpening_id = $jobOpening->id;
            $jobOpening_name = $jobOpening->name;
            return response()->json([
                'success' => "New jobOpening added successfully.",
                'id' => $jobOpening_id,
                'name' => $jobOpening_name
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
}
