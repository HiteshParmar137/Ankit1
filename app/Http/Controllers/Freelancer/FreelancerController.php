<?php

namespace App\Http\Controllers\Freelancer;

use App\Http\Controllers\Controller;
use App\Http\Requests\FreelancerRequest;
use App\Http\Requests\TechnologyRequest;
use App\Interfaces\FreelancerInterface;
use App\Interfaces\TechnologyRepositoryInterface;
use App\Models\freelancer;
use App\Models\Technology;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class FreelancerController extends Controller
{   
    public function __construct(protected FreelancerInterface $freelancerRepository, protected TechnologyRepositoryInterface $technologyRepository)
    {   
        $this->freelancerRepository = $freelancerRepository;
        $this->technologyRepository = $technologyRepository;
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
                $freelancers =  $this->freelancerRepository->getAllData($request->all());
                $view = \View::make('freelancer.partials.lists', [
                    'freelancers' => $freelancers,
                    'return_back_handle' => http_build_query($request->all()),
                ]);
                $html = $view->render();
                return response()->json(['html' => $html, 'message' => 'Data Fetched Successfully!', 'success' =>  true]);
            } else {
                $technologies = Technology::all()
                        ->pluck('name', 'id')
                        ->toArray();
                $queryParams = $request->all();
                return view('freelancer.index', compact('technologies', 'queryParams'));
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
    public function store(FreelancerRequest $request)
    {   
        try {
            $this->freelancerRepository->store($request->all());
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
     * @param  \App\Models\freelancer  $freelancer
     * @return \Illuminate\Http\Response
     */
    public function show(freelancer $freelancer)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\freelancer  $freelancer
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try{
            $freelancerId = Crypt::decrypt($id);
            $freelancer = $this->freelancerRepository->edit($freelancerId);
            $technologies = Technology::all()->pluck('name', 'id')->toArray();
            $view = \View::make('modal.freelancer-form-edit-data', [
                'freelancer' => $freelancer,
                'technologies' => $technologies,
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
     * @param  \App\Models\freelancer  $freelancer
     * @return \Illuminate\Http\Response
     */
    public function update(FreelancerRequest $request, $id)
    {
        try {
            $freelancerId = Crypt::decrypt($id);
            $this->freelancerRepository->update($freelancerId, $request->all());
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
     * @param  \App\Models\freelancer  $freelancer
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        try {
            $freelancerId = Crypt::decrypt($id);
            $this->freelancerRepository->delete($freelancerId);
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

    public function projectTechnologyStore(TechnologyRequest $request)
    {
        try {
            $technology = $this->technologyRepository->store($request->all());
            $technology_id = $technology->id;
            $technology_name = $technology->name;
            return response()->json([
                'success' => "New technology added successfully.",
                'id' => $technology_id,
                'name' => $technology_name
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

    public function checkEmail(Request $request)
    {
        $email = $request->email ?? null;
        $checkUser = freelancer::where(function ($query) use ($email) {
            $query->where('email', $email);
        })->count();

        if ($checkUser > 0) {
            return response()->json("Email address is already taken");
        } else {
            return response()->json("true");
        }
    }
}
