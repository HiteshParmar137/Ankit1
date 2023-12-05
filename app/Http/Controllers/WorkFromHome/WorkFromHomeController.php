<?php

namespace App\Http\Controllers\WorkFromHome;

use App\Http\Controllers\Controller;
use App\Http\Requests\WorkFromHomeRequest;
use App\Interfaces\WorkFromHomeRepositoryInterface;
use App\Models\User;
use App\Models\WorkFromHome;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;

class WorkFromHomeController extends Controller
{
    public function __construct(protected WorkFromHomeRepositoryInterface $workFromHomeRepository) 
    {
        $this->workFromHomeRepository = $workFromHomeRepository;
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

                $workFromHome = $this->workFromHomeRepository->getAllData($request->all(), $finacialYearStartDate, $finacialYearEndDate);
                $finacialYearStartDate = Config::get('constant.CURRENT_FINANCIAL_YEAR_START_DATE');
                $finacialYearEndDate = Config::get('constant.CURRENT_FINANCIAL_YEAR_END_DATE');
                $totalFinacialyearWfh = finacialYearWfh($finacialYearStartDate, $finacialYearEndDate,  Auth::id());
                $view = \View::make('work-from-home.partials.lists', [
                    'workFromHome' => $workFromHome,
                    'return_back_handle' => http_build_query($request->all()),
                ]);
                $html = $view->render();
                return response()->json([
                    'html' => $html,
                    'message' => 'Data Fetched Successfully!',
                    'success' => true,
                    'totalFinacialyearWfh' => $totalFinacialyearWfh,
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
                return view('work-from-home.index', compact('users', 'query_params'));
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
        $user = auth()->user();
        $users = User::with('roles')->where('id','!=',Auth::id())
                ->whereHas('roles', function($query) {
                    $query->where('slug', '!=', User::SUPER_ADMIN_ROLE_SLUG);
                })
                ->pluck('name', 'id')
                ->toArray();
        if (($user->isAdmin() || $user->isHr()) && $request->url() == route('all-work-from-home-create')) {
            return view(
                'work-from-home.all-work-from-home-form',
                compact('users')
            );
        } else {
            return view('work-from-home.form', compact('users'));
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(WorkFromHomeRequest $request)
    {
        try {
            // dd($request->all());
            if ($request->type == WorkFromHome::FULL_DAY) {
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
                $user_id = $request->user_id;
                $request_to = Auth::id();
                $status = WorkFromHome::APPROVED;
                $feedback = " Yes Approved.";
            } else {
                $user_id = Auth::id();
                $request_to = $request->request_to;
                $status = WorkFromHome::PENDING;
                $feedback = Null;
            }
            $data = [
                'request_to' => $request_to,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'requested_date' => $requestDate,
                'type' => $request->type ?? 1,
                'half_day' => $halfDay ?? 1,
                'contact_number' => $request->contact_number,
                'reason' => $request->reason,
                'day_duration' => $dayDuration,
                'user_id' => $user_id, 
                'status' => $status,
                'feedback' => $feedback,
            ];
            $getExistingWorkFromHome = WorkFromHome::whereRaw('("' . $startDate . '" between start_date and end_date)')
                ->where('user_id', $request->user_id)
                ->count();
            if ($getExistingWorkFromHome > 0) {
                    return response()->json(
                        ['message' => 'You have alreday work from home on this day duration.', 'success' => false],
                        200
                    );
            } else {
                $this->workFromHomeRepository->store($data);
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
     * @param  \App\Models\WorkFromHome  $workFromHome
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $workFromHomeId = Crypt::decrypt($id);
            $requestedUserId = 1; // Add Auth When implement Auth.
            $workFromHome = $this->workFromHomeRepository->show(
                $workFromHomeId,
                $requestedUserId
            );
            $view = \View::make('modal.work-from-home-details-data', [
                'workFromHome' => $workFromHome,
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
     * @param  \App\Models\WorkFromHome  $workFromHome
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        try {
            $workFromHomeId = Crypt::decrypt($id);
            $workFromHome = $this->workFromHomeRepository->edit($workFromHomeId);
            $users = User::Active()
                    ->select('first_name', 'last_name' ,'id')
                    ->get();
            if (isset($request->type) && !empty($request->type)) {
                $view = \View::make('modal.wfh-form-edit-data', [
                    'workFromHome' => $workFromHome,
                    'users' => $users,
                ]);
                $html = $view->render();
                return response()->json([
                    'html' => $html,
                    'message' => 'Data Fetched Successfully!',
                    'success' => true,
                ]);
            } else {
                $view = \View::make('modal.all-wfh-form-edit-data', [
                    'workFromHome' => $workFromHome,
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
            return redirect()
                ->back()
                ->with([
                    'error' => 'Something is wrong',
                    'error_msg' => $e->getMessage(),
                ]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\WorkFromHome  $workFromHome
     * @return \Illuminate\Http\Response
     */
    public function update(WorkFromHomeRequest $request, $id)
    {
        try {
            $workFromHomeId = Crypt::decrypt($id);

            if ($request->wfh_type == WorkFromHome::FULL_DAY) {
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
                $status = WorkFromHome::APPROVED;
                $feedback = " Yes Approved.";
            } else {
                $userId = Auth::id();
                $requestTo = $request->request_to;
                $status = WorkFromHome::PENDING;
                $feedback = Null;
            }
            $data = [
                'request_to' => $requestTo,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'requested_date' => $requestDate,
                'type' => $request->wfh_type ?? 1,
                'half_day' => $halfDay ?? 1,
                'contact_number' => $request->contact_number,
                'reason' => $request->reason,
                'day_duration' => $dayDuration,
                'user_id' => $userId, 
                'status' => $status,
                'feedback' => $feedback,
            ];
            $getExistingLeave = WorkFromHome::whereRaw('("' . $startDate . '" between start_date and end_date)')
                ->where('user_id', $userId)
                ->where('id', '!=', $workFromHomeId)
                ->count();
            if ($getExistingLeave > 0) {
                return redirect()
                    ->route('work-from-home-lists')
                    ->with([
                        'error' =>
                            'You have alreday leave on this day duration.',
                    ]);
            } else {
                $this->workFromHomeRepository->update($workFromHomeId, $data);
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
     * @param  \App\Models\WorkFromHome  $workFromHome
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        try {
            $workFromHomeId = Crypt::decrypt($id);
            $this->workFromHomeRepository->delete($workFromHomeId);
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
    public function teamWfh(Request $request)
    {
        try {
            if ($request->ajax()) {
                $workFromHome = $this->workFromHomeRepository->teamWorkFromHome(
                    1,
                    $request->all()
                );
                $view = \View::make(
                    'work-from-home.partials.team-work-from-home-lists',
                    [
                        'workFromHome' => $workFromHome,
                        'return_back_handle' => http_build_query(
                            $request->all()
                        ),
                    ]
                );
                $html = $view->render();
                return response()->json([
                    'html' => $html,
                    'message' => 'Data Fetched Successfully!',
                    'success' => true,
                ]);
            } else {
                $query_params = $request->all();
                return view(
                    'work-from-home.team-work-from-home',
                    $query_params
                );
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
    public function wfhFeedBack(WorkFromHomeRequest $request)
    {
        try {
            $leaveId = $request->id;
            $data = [
                'status' => $request->status,
                'feedback' => $request->feedback,
            ];
            $this->workFromHomeRepository->workFromHomeFeedBack(
                $leaveId,
                $data
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

    // Get all User Leave.
    public function allworkFromHome(Request $request)
    {
        try {
            if ($request->ajax()) {
                $workFromHome = $this->workFromHomeRepository->getAllWfhData(
                    $request->all()
                );
                $view = \View::make(
                    'work-from-home.partials.all-work-from-home-lists',
                    [
                        'workFromHome' => $workFromHome,
                        'return_back_handle' => http_build_query(
                            $request->all()
                        ),
                    ]
                );
                $html = $view->render();
                return response()->json([
                    'html' => $html,
                    'message' => 'Data Fetched Successfully!',
                    'success' => true,
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
                return view('work-from-home.all-work-from-home', compact('users', 'query_params'));
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

    // Financial Year Data
    public function financialYearData(Request $request)
    {
        try {
            $financialYear = $request->financial_year;
            if (isset($financialYear) && !empty($financialYear)) {
                $year = explode('-', $financialYear);
                $startDate = $year[0] . '-04-01';
                $endDate = $year[1] . '-03-31';
            } else {
                $startDate = Config::get(
                    'constant.CURRENT_FINANCIAL_YEAR_START_DATE'
                );
                $endDate = Config::get(
                    'constant.CURRENT_FINANCIAL_YEAR_END_DATE'
                );
            }
            $data = [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'record_per_page' => $request->record_per_page,
            ];
            $usersTotalWorkFromHome = $this->workFromHomeRepository->financialYearData(
                $data
            );
            $financialYearWfh = finacialYearWfh($startDate, $endDate) ?? 0;
            $view = \View::make('work-from-home.partials.lists', [
                'workFromHome' => $usersTotalWorkFromHome,
            ]);
            $html = $view->render();
            return response()->json([
                'html' => $html,
                'message' => 'Data Fetched Successfully!',
                'success' => true,
                'totalFinacialyearWfh' => $financialYearWfh,
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