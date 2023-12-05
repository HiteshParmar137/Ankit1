<?php

namespace App\Http\Controllers\PunchLogs;

use App\Http\Controllers\Controller;
use App\Http\Requests\PunchLogRequest;
use App\Interfaces\PunchLogsRepositoryInterface;
use App\Models\Leaves;
use App\Models\PunchLogs;
use App\Models\User;
use App\Models\WorkFromHome;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class PunchLogsController extends Controller
{
    public function __construct(protected PunchLogsRepositoryInterface $punchLogsRepository)
    {
        $this->punchLogsRepository = $punchLogsRepository;
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
                $punchLogs = $this->punchLogsRepository->getAllData($request->all());
                if ((auth()->user()->isAdmin() || auth()->user()->isHr()) && $request->url() == route('punch-log-lists')) {
                    $view = \View::make('punch-log.partials.lists', [
                        'punchLogs' => $punchLogs,
                        'return_back_handle' => http_build_query($request->all()),
                    ]);
                    $html = $view->render();
                } else {
                    $view = \View::make('punch-log.partials.my-punch-log-lists', [
                        'punchLogs' => $punchLogs,
                        'return_back_handle' => http_build_query($request->all()),
                    ]);
                    $html = $view->render();
                }

                return response()->json([
                    'html' => $html,
                    'message' => 'Data Fetched Successfully!',
                    'success' => true,
                ]);
            } else {
                if ((auth()->user()->isAdmin() || auth()->user()->isHr()) && $request->url() == route('punch-log-lists')) {
                    $users = User::with('roles')
                    ->whereHas('roles', function($query) {
                        $query->where('slug', '!=', User::SUPER_ADMIN_ROLE_SLUG);
                    })
                    ->Active()
                    ->select('first_name', 'last_name' ,'id')
                    ->get();
                    $query_params = $request->all();
                    return view('punch-log.index', compact('users', 'query_params'));
                } else{
                    $query_params = $request->all();
                    return view('punch-log.my-punch-logs', $query_params);
                }
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
                ->Active()
                ->select('first_name', 'last_name' ,'id')
                ->get();
        return view('punch-log.form', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PunchLogRequest $request)
    {
        try {
            
            $userId = $request->user_id;
            $logDate = Carbon::createFromFormat('d-m-Y', $request->date)->format('Y-m-d');
            $request->merge(['date' => $logDate]);
            $getExistData = PunchLogs::where('user_id', $userId)->where('date', $logDate)->first();
            if($getExistData){
                return redirect()->route('punch-log-lists')->with('error', 'Alreday Data Exist.');
            }else{
                $workFromHomeData = WorkFromHome::where('user_id', '=', $userId)
                ->where('start_date', '<=', $logDate)
                ->where('end_date', '>=', $logDate)
                ->where(function ($query) {
                    $query->where('status', WorkFromHome::PENDING);
                    $query->orWhere('status', WorkFromHome::APPROVED);
                })->first();
                $leaveData = Leaves::where('user_id', '=', $userId)
                    ->where('type', 1)
                    ->where('start_date', '<=', $logDate)
                    ->where('end_date', '>=', $logDate)
                    ->where(function ($query){
                        $query->where('status', Leaves::PENDING);
                        $query->orWhere('status', Leaves::APPROVED);
                    })->first();

                if ($workFromHomeData && !$leaveData) {
                    $this->punchLogsRepository->store($request->all());
                    return redirect()->route('punch-log-lists')->with(['success' => 'Data store successfully.']);
                } else {
                    if (!$workFromHomeData) {
                        return redirect()->route('punch-log-lists')->with('error', 'Today this user working at office.');
                    } else {
                        return redirect()->route('punch-log-lists')->with('error', 'Today this user on leave.');
                    }
                }
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
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PunchLogs  $punchLogs
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $user = auth()->user();
            $users = User::with('roles')->where('id','!=',Auth::id())
                ->whereHas('roles', function($query) {
                    $query->where('slug', '!=', User::SUPER_ADMIN_ROLE_SLUG);
                })
                ->Active()
                ->select('first_name', 'last_name' ,'id')
                ->get();
            $punchLogId =  $policyId = Crypt::decrypt($id);
            $punchLog = $this->punchLogsRepository->edit($punchLogId);
            
        return view('punch-log.edit-form', compact('users','punchLog'));
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return redirect()->back()->with(['error' => 'Something is wrong', 'error_msg' => $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PunchLogs  $leaves
     * @return \Illuminate\Http\Response
     */

    public function update(PunchLogRequest $request, $id)
    {
        try {
            
            $punchLogId = Crypt::decrypt($id);
            $userId = $request->user_id;
            $logDate = Carbon::createFromFormat('d-m-Y', $request->date)->format('Y-m-d');
            $request->merge(['date' => $logDate]);
            $workFromHomeData = WorkFromHome::where('user_id', '=', $userId)
                ->where('start_date', '<=', $logDate)
                ->where('end_date', '>=', $logDate)
                ->where(function ($query) {
                    $query->where('status', WorkFromHome::PENDING);
                    $query->orWhere('status', WorkFromHome::APPROVED);
                })->first();
            $leaveData = Leaves::where('user_id', '=', $userId)
                ->where('type', 1)
                ->where('start_date', '<=', $logDate)
                ->where('end_date', '>=', $logDate)
                ->where(function ($query){
                    $query->where('status', Leaves::PENDING);
                    $query->orWhere('status', Leaves::APPROVED);
                })->first();
            if ($workFromHomeData && !$leaveData) {
                $this->punchLogsRepository->update($punchLogId,$request->all());
                return redirect()->route('punch-log-lists')->with(['success' => 'Data store successfully.']);
            } else {
                if (!$workFromHomeData) {
                    return redirect()->route('punch-log-lists')->with('error', 'This day user is at the workplace.');
                } else {
                    return redirect()->route('punch-log-lists')->with('error', "This day's user is on vacation.");
                }
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
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PunchLogs  $holiday
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        try {
            $punchlogId = Crypt::decrypt($id);
            $this->punchLogsRepository->delete($punchlogId);
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

    public function bulkUpload(PunchLogRequest $request){
        try {
            $data = [
                'date' => $request->date,
                'file' => $request->bulkUpload
            ];
            $this->punchLogsRepository->bulkUpload($data);
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
    // add new in out row
    public function addNewInOutTimeRow(Request $request)
    {   
        try {   
                $currentIndex = $request->current_index ?? 1;
                $index = ++$currentIndex;
                $view = \View::make('punch-log.partials.add-new-in-out-time-row',[
                    'index' => $index
                ]);
                $html = $view->render();
                return response()->json([
                    'html' => $html,
                    'index' => $index,
                    'message' => 'Data Fetched Successfully!',
                    'success' => true,
                ]);
        } catch (\Exception $e) {
            \Log::error($e);
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
