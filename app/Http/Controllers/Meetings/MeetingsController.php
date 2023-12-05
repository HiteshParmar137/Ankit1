<?php

namespace App\Http\Controllers\Meetings;

use App\Models\Meetings;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Interfaces\MeetingsRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use App\Http\Requests\MeetingRequest;


class MeetingsController extends Controller
{   

    public function __construct(protected MeetingsRepositoryInterface $meetingRepository)
    {
        $this->meetingRepository = $meetingRepository;
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
                $meetings =  $this->meetingRepository->getAllData($request->all());
                $view = \View::make('meeting.partials.lists', [
                    'meetings' => $meetings,
                    'return_back_handle' => http_build_query($request->all()),
                ]);
                $html = $view->render();
                return response()->json(['html' => $html, 'message' => 'Data Fetched Successfully!', 'success' =>  true]);
            } else {
                $users = User::Active()
                            ->select('first_name', 'last_name' ,'id')
                            ->get();
                $query_params = $request->all();
                return view('meeting.index',compact('users', 'query_params'));
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
        $user = auth()->user();
        $users = User::Active()
                    ->select('first_name', 'last_name' ,'id')
                    ->get();
        return view('meeting.form', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(MeetingRequest $request)
    {
        try {

            $date = $request->date . ' ' . $request->time;
            $dateTime = Carbon::parse($date)->format('Y-m-d H:i') . ":" . "00";
            $presenter = implode(',', $request->presenter);
            $guest     = implode(',', $request->guest);

            $data = [
                'title'     => $request->title,
                'agenda'    => $request->agenda,
                'presenter' => $presenter,
                'guest'     => $guest,
                'date_time' => $dateTime,
                'duration'  => $request->duration,
                'status'    => Meetings::SCHEDULED
            ];
            $this->meetingRepository->store($data);
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
     * @param  \App\Models\Meetings  $meetings
     * @return \Illuminate\Http\Response
     */
    public function show(Meetings $meetings)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Meetings  $meetings
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $meetingId = Crypt::decrypt($id);
            $meeting = $this->meetingRepository->edit($meetingId);
            $users = User::Active()
                    ->select('first_name', 'last_name' ,'id')
                    ->get();
            $view = \View::make('modal.meeting-form-edit-data', [
                'meeting' => $meeting,
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
     * @param  \App\Models\Meetings  $meetings
     * @return \Illuminate\Http\Response
     */
    public function update(MeetingRequest $request, $id)
    {   
        try {
            $meetingId = Crypt::decrypt($id);
            $date = $request->date . ' ' . $request->time;
            $dateTime = Carbon::parse($date)->format('Y-m-d H:i') . ":" . "00";
            $presenter = implode(',', $request->presenter);
            $guest     = implode(',', $request->guest);
            $data = [
                'title'     => $request->title,
                'agenda'    => $request->agenda,
                'presenter' => $presenter,
                'guest'     => $guest,
                'date_time' => $dateTime,
                'duration'  => $request->duration,
                'status'    => Meetings::SCHEDULED
            ];
            $this->meetingRepository->update($meetingId, $data);
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
     * @param  \App\Models\Meetings  $meetings
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        try {
            $meetingId = Crypt::decrypt($id);
            $this->meetingRepository->delete($meetingId);
            return response()->json(['message' => 'Data delete successfully.', 'success' => true], 200);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return response()->json(['message' => 'Something is wrong', 'success' => false, 'error_msg' => $e->getMessage()], 500);
        }
    }

    public function feedback(MeetingRequest $request)
    {
        try {
            $this->meetingRepository->feedback($request->all());
            return response()->json(['message' => 'Data store successfully.', 'success' => true], 200);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return response()->json(['message' => 'Something is wrong', 'success' => false, 'error_msg' => $e->getMessage()], 500);
        }
    }

    public function filterData(MeetingRequest $request)
    {   
        try {
            $this->meetingRepository->filterData($request->all());
            return response()->json(['message' => 'Data Fetched Successfully!','success' => true,], 200);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return response()->json(['message' => 'Something is wrong', 'success' => false, 'error_msg' => $e->getMessage()], 500);
        }
    }
}
