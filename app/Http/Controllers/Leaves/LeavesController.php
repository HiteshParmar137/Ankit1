<?php

namespace App\Http\Controllers\Leaves;

use App\Http\Controllers\Controller;
use App\Http\Requests\LeaveRequest;
use App\Interfaces\LeavesRepositoryInterface;
use App\Models\Leaves;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Config;
use Illuminate\Support\Facades\Crypt;

class LeavesController extends Controller
{
    public function __construct(protected LeavesRepositoryInterface $leaveRepository)
    {
        $this->leaveRepository = $leaveRepository;
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
                $dateRange = explode('-', $request->financial_year);
                $finacialYearStartDate = $dateRange[0].'-04-01';
                $finacialYearEndDate = $dateRange[1].'-03-31';
                $leaves = $this->leaveRepository->getAllData($request->all(), $finacialYearStartDate, $finacialYearEndDate);
                $financialYearLeave = finacialYearLeave($finacialYearStartDate, $finacialYearEndDate, Auth::id());

                $view = \View::make('leave.partials.lists', [
                    'leaves' => $leaves,
                    'return_back_handle' => http_build_query($request->all()),
                ]);
                $html = $view->render();
                return response()->json([
                    'html' => $html,
                    'message' => 'Data Fetched Successfully!',
                    'success' => true,
                    'totalFinacialyearleave' => $financialYearLeave,
                ]);
            } else {
                $users = User::with('roles')->where('id','!=',Auth::id())
                        ->whereHas('roles', function($query) {
                            $query->where('slug', '!=', User::SUPER_ADMIN_ROLE_SLUG);
                        })
                        ->Active()
                        ->select('first_name', 'last_name' ,'id')
                        ->get();
                $query_params = $request->all();
                return view('leave.index', compact('users', 'query_params'));
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
    public function create(Request $request)
    {   
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(LeaveRequest $request)
    {
        try {
            if ($request->leave_type == Leaves::FULL_DAY) {
                $halfDay = null;
                $dayDuration = getDayDuration(
                    $request->start_date,
                    $request->end_date
                );
            } else {
                $halfDay = $request->half_day;
                $dayCount = getDayDuration(
                    $request->start_date,
                    $request->end_date
                );
                $dayDuration = $dayCount / 2;
            }
            if (isset($request->user_id)) {    
                $userId = $request->user_id;
                $requestTo = Auth::id();
                $status = Leaves::APPROVED;
                $feedback = " Yes Approved.";
            } else {
                $userId = Auth::id();
                $requestTo = $request->request_to;
                $status = Leaves::PENDING;
                $feedback = Null;
            }
            $startDate = dateFormate($request->start_date);
            $endDate = dateFormate($request->end_date);
            $requestDate = dateFormate(Carbon::now());
            $data = [
                'request_to' => $requestTo,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'requested_date' => $requestDate,
                'type' => $request->leave_type,
                'half_day' => $halfDay,
                'contact_number' => $request->contact_number,
                'reason' => $request->reason,
                'day_duration' => $dayDuration,
                'user_id' => $userId,
                'status' => $status,
                'feedback' => $feedback,
            ];
            $getExistingLeave = Leaves::whereRaw('("' . $startDate . '" between start_date and end_date)')
                ->where('user_id', $userId)
                ->count();
            if ($getExistingLeave > 0) {
           
                return response()->json(
                    ['error_msg' => 'You have alreday leave on this day duration.', 'success' => false],
                    200
                );
            } else {
                $this->leaveRepository->store($data);
            }
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
     * @param  \App\Models\Leaves  $leaves
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $leaveId = Crypt::decrypt($id);
            $requestedUserId = Auth::id();
            $leave = $this->leaveRepository->show($leaveId, $requestedUserId);
            $view = \View::make('modal.leave-details-data', [
                'leave' => $leave,
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
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Leaves  $leaves
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        try {
            $leaveId = Crypt::decrypt($id);
            $leave = $this->leaveRepository->edit($leaveId);
            $users = User::with('roles')->where('id','!=',Auth::id())
                        ->whereHas('roles', function($query) {
                            $query->where('slug', '!=', User::SUPER_ADMIN_ROLE_SLUG);
                        })
                        ->Active()
                        ->select('first_name', 'last_name' ,'id')
                        ->get();
                        
            if (isset($request->type) && !empty($request->type)) {
                $view = \View::make('modal.leave-form-edit-data', [
                    'leave' => $leave,
                    'users' => $users,
                ]);
                $html = $view->render();
                return response()->json([
                    'html' => $html,
                    'message' => 'Data Fetched Successfully!',
                    'success' => true,
                ]);
            } else {
                $users = User::pluck('name', 'id')->toArray();
                $view = \View::make('modal.all-leave-form-edit-data', [
                    'leave' => $leave,
                    'users' => $users,
                ]);
                $html = $view->render();
                return response()->json([
                    'html' => $html,
                    'message' => 'Data Fetched Successfully!',
                    'success' => true,
                ]);
                return view('leave.form', compact('leave', 'users'));
            }
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
     * @param  \App\Models\Leaves  $leaves
     * @return \Illuminate\Http\Response
     */
    public function update(LeaveRequest $request, $id)
    {
        try {
            $leaveId = Crypt::decrypt($id);

            if ($request->leave_type == Leaves::FULL_DAY) {
                $halfDay = null;
                $dayDuration = getDayDuration(
                    $request->start_date,
                    $request->end_date
                );
            } else {
                $halfDay = $request->half_day;
                $dayCount = getDayDuration(
                    $request->start_date,
                    $request->end_date
                );
                $dayDuration = $dayCount / 2;
            }

            $startDate = dateFormate($request->start_date);
            $endDate = dateFormate($request->end_date);
            $requestDate = dateFormate(Carbon::now());

            if (isset($request->user_id)) {    
                $userId = $request->user_id;
                $requestTo = Auth::id();
                $status = Leaves::APPROVED;
                $feedback = " Yes Approved.";
            } else {
                $userId = Auth::id();
                $requestTo = $request->request_to;
                $status = Leaves::PENDING;
                $feedback = Null;
            }

            
            $data = [
                'request_to' => $requestTo, // Add auth
                'start_date' => $startDate,
                'end_date' => $endDate,
                'requested_date' => $requestDate,
                'type' => $request->leave_type,
                'half_day' => $halfDay,
                'contact_number' => $request->contact_number,
                'reason' => $request->reason,
                'day_duration' => $dayDuration,
                'user_id' => $userId,
                'status' => $status,
                'feedback' => $feedback,
            ];
            $getExistingLeave = Leaves::whereRaw('("' . $startDate . '" between start_date and end_date)')
                                    ->where('user_id', $userId)
                                    ->where('id', '!=', $leaveId)
                                    ->count();
            if ($getExistingLeave > 0) {
                return response()->json(
                    ['error_msg' => 'You have alreday leave on this day duration.', 'success' => false],
                    200
                );
            } else {
                $this->leaveRepository->update($leaveId, $data);
            }
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
     * @param  \App\Models\Leaves  $leaves
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        try {
            $leaveId = Crypt::decrypt($id);
            $this->leaveRepository->delete($leaveId);
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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function teamLeaves(Request $request)
    {
        try {
            if ($request->ajax()) {
                $leaves = $this->leaveRepository->teamLeaves(
                    Auth::id(),
                    $request->all()
                );
                $view = \View::make('leave.partials.team-leave-lists', [
                    'leaves' => $leaves,
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
                return view('leave.team-leave', $query_params);
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
     * Store the specified resource status in storage.
     *
     * @param  \App\Models\Leaves  $leaves
     * @return \Illuminate\Http\Response
     */
    public function leaveFeedBack(LeaveRequest $request)
    {
        try {
            $leaveId = $request->id;
            $data = [
                'status' => $request->status,
                'feedback' => $request->feedback,
            ];
            $this->leaveRepository->leaveFeedback($leaveId, $data);
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

    // Get all User Leave.
    public function allLeave(Request $request)
    {
        try {
            if ($request->ajax()) {
                $leaves = $this->leaveRepository->getAllLeaveData(
                    $request->all()
                );
                $view = \View::make('leave.partials.all-leave-lists', [
                    'leaves' => $leaves,
                    'return_back_handle' => http_build_query($request->all()),
                ]);
                $html = $view->render();
                return response()->json([
                    'html' => $html,
                    'message' => 'Data Fetched Successfully!',
                    'success' => true,
                ]);
            } else {
                $users = User::with('roles')
                        ->whereHas('roles', function($query) {
                            $query->where('slug', '!=', User::SUPER_ADMIN_ROLE_SLUG);
                        })
                        ->select('first_name', 'last_name' ,'id')
                        ->get();
                $query_params = $request->all();
                return view('leave.all-leave-index', compact( "query_params", "users"));
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

    // If Leave more than 12
    public function leaveDebited(LeaveRequest $request)
    {
        try {
            $leaveId = Crypt::decrypt($request->id);
            $this->leaveRepository->leaveDebited(
                $leaveId,
                $request->debitedValue
            );
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

    // Financial Year Data
    // public function financialYearData(Request $request)
    // {
    //     try {
    //         $financialYear = $request->financial_year;
    //         if (isset($financialYear) && !empty($financialYear)) {
    //             $year = explode('-', $financialYear);
    //             $startDate = $year[0] . '-04-01';
    //             $endDate = $year[1] . '-03-31';
    //         } else {
    //             $startDate = Config::get(
    //                 'constant.CURRENT_FINANCIAL_YEAR_START_DATE'
    //             );
    //             $endDate = Config::get(
    //                 'constant.CURRENT_FINANCIAL_YEAR_END_DATE'
    //             );
    //         }
    //         $data = [
    //             'start_date' => $startDate,
    //             'end_date' => $endDate,
    //             'record_per_page' => $request->record_per_page,
    //         ];
    //         $usersTotalLeaves = $this->leaveRepository->financialYearData($data);
    //         $financialYearLeave = finacialYearLeave($startDate, $endDate) ?? 0;
    //         $view = \View::make('leave.partials.lists', [
    //             'leaves' => $usersTotalLeaves,
    //         ]);
    //         $html = $view->render();
    //         return response()->json([
    //             'html' => $html,
    //             'message' => 'Data Fetched Successfully!',
    //             'success' => true,
    //             'totalFinacialyearleave' => $financialYearLeave,
    //         ]);
    //     } catch (\Exception $e) {
    //         \Log::error($e->getMessage());
    //         return response()->json(
    //             [
    //                 'message' => 'Something is wrong',
    //                 'success' => false,
    //                 'error_msg' => $e->getMessage(),
    //             ],
    //             500
    //         );
    //     }
    // }
}