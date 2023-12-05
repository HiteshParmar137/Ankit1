<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use App\Http\Requests\CandidateInterviewRequest;
use App\Interfaces\CandidateInterviewInterface;
use App\Models\CandidateDetail;
use App\Models\InterviewStage;
use App\Models\JobOpenings;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CandidateInterviewController extends Controller
{   
    public function __construct(protected CandidateInterviewInterface $candidateInterviewRepository)
    {   
        $this->candidateInterviewRepository = $candidateInterviewRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $id)
    {
        try {
            if ($request->ajax()) {
                $candidateDetailsId = decrypt($id); 
                $candidateInterviews =  $this->candidateInterviewRepository->getAllData($candidateDetailsId);
                $view = \View::make('recruitment.partials.candidate_interview_lists', [
                    'candidateInterviews' => $candidateInterviews,
                ]);
                $html = $view->render();
                return response()->json(['html' => $html, 'message' => 'Data Fetched Successfully!', 'success' =>  true]);
            } else {
                return view('recruitment.partials.details');
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
    public function store(CandidateInterviewRequest $request)
    {
        try {
            $date = Carbon::parse($request->date)->format('Y-m-d');
            $request->merge(['date' => $date]);
            $this->candidateInterviewRepository->store($request->all());
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
    public function show(CandidateDetail $candidateDetail)
    {
        //
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
            $candidateInterviewId = decrypt($id);
            $candidateInterview = $this->candidateInterviewRepository->edit($candidateInterviewId);
            $users = User::select('first_name', 'last_name' ,'id')->get();
            $interviewStages = InterviewStage::all();
            $view = \View::make('modal.candidate-interview-form-edit-data', [
                'candidateInterview' => $candidateInterview,
                'users' => $users,
                'interviewStages' => $interviewStages,
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
    public function update(CandidateInterviewRequest $request, $id)
    {
    
        try {
            $date = Carbon::parse($request->date)->format('Y-m-d');
            $request->merge(['date' => $date]);
            $candidateInterviewId = decrypt($id);
            $this->candidateInterviewRepository->update($candidateInterviewId, $request->all());
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
            $candidateInterviewId = decrypt($id);
            $this->candidateInterviewRepository->delete($candidateInterviewId);
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

    public function interviewAssignedMe(Request $request)
    {   

        try {
            if ($request->ajax()) {
                $candidateInterviews =  $this->candidateInterviewRepository->interviewAssignedMe($request->all());
                $view = \View::make('recruitment.partials.my_interview_lists', [
                    'candidateInterviews' => $candidateInterviews,
                ]);
                $html = $view->render();
                return response()->json(['html' => $html, 'message' => 'Data Fetched Successfully!', 'success' =>  true]);
            } else {
                $positions = JobOpenings::all();
                return view('recruitment.my_interview', compact('positions'));
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

    public function interviewFeedbackStore(CandidateInterviewRequest $request)
    {
        try {
            DB::beginTransaction();
            $this->candidateInterviewRepository->interviewFeedbackStore($request->all());
            DB::commit();
            return response()->json(
                ['message' => 'Data store successfully.', 'success' => true],
                200
            );
        } catch (\Exception $e) {
            DB::rollBack();
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
