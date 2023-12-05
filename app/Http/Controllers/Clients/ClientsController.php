<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClientRequest;
use App\Interfaces\ClientsRepositoryInterface;
use App\Models\Client;
use App\Models\Clients;
use App\Models\Country;
use App\Models\Technology;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class ClientsController extends Controller
{   

    public function __construct(protected ClientsRepositoryInterface $clientRepository)
    {
        $this->clientRepository = $clientRepository;
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
                $clients = $this->clientRepository->getAllData($request->all());
                $view = \View::make('client.partials.lists', [
                    'clients' => $clients,
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
                return view('client.index', $query_params);
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
    public function create()
    {   
        $technologies = Technology::all()->pluck('name', 'id')->toArray();
        $countries = Country::get()->pluck('name', 'id')->toArray();
        return view('client.form', compact('technologies', 'countries'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ClientRequest $request)
    {
        try {
            if (isset($request->approached_technologies) && !empty($request->approached_technologies)) {
                $approachedTechnologies = implode(',', $request->approached_technologies);
            }
            $data = [
                'name' => $request->name,
                'email_id' => $request->email_id ?? '',
                'skype_id' => $request->skype_id ?? '',
                'contact_no' => $request->contact_no ?? '',
                'linkedIn_url' => $request->linkedIn_url ?? '',
                'website_url' => $request->website_url ?? '',
                'city' => $request->city ?? '',
                'country' => $request->country ?? '',
                'reference_from' => $request->reference_from ?? '',
                'company_name' => $request->company_name ?? '',
                'company_email' => $request->company_email ?? '',
                'company_contact' => $request->company_contact ?? '',
                'logo' => $request->logo ?? null,
                'photo' => $request->photo ?? null,
                'company_skype' => $request->company_skype ?? '',
                'company_website' => $request->company_website ?? '',
                'company_country' => $request->company_country ?? '',
                'address' => $request->address ?? '',
                'company_address' => $request->company_address ?? '',
                'note' => $request->note ?? '',
                'approached_technologies' => $approachedTechnologies ?? '',
            ];
            $this->clientRepository->store($data);
            return redirect()
                ->route('client-lists')
                ->with(['success' => 'Data store successfully.']);
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
     * Display the specified resource.
     *
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $clientId = Crypt::decrypt($id);
            $client = $this->clientRepository->edit($clientId);
            $view = \View::make('modal.client-details-data', [
                'client' => $client,
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
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {   
        try {
            $clientId = Crypt::decrypt($id);
            $client = $this->clientRepository->edit($clientId);
            $technologies = Technology::all()->pluck('name', 'id')->toArray();
            $countries = Country::get()->pluck('name', 'id')->toArray();
            return view('client.form', compact('technologies', 'countries', 'client'));
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
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function update(ClientRequest $request, $id)
    {
        try {
            $clientId = Crypt::decrypt($id);

            if (isset($request->approached_technologies) && !empty($request->approached_technologies)) {
                $approachedTechnologies = implode(',', $request->approached_technologies);
            }
            $data = [
                'name' => $request->name,
                'email_id' => $request->email_id ?? '',
                'skype_id' => $request->skype_id ?? '',
                'contact_no' => $request->contact_no ?? '',
                'linkedIn_url' => $request->linkedIn_url ?? '',
                'website_url' => $request->website_url ?? '',
                'city' => $request->city ?? '',
                'country' => $request->country ?? '',
                'reference_from' => $request->reference_from ?? '',
                'company_name' => $request->company_name ?? '',
                'company_email' => $request->company_email ?? '',
                'company_contact' => $request->company_contact ?? '',
                'logo' => $request->logo ?? null,
                'photo' => $request->photo ?? null,
                'company_skype' => $request->company_skype ?? '',
                'company_website' => $request->company_website ?? '',
                'company_country' => $request->company_country ?? '',
                'address' => $request->address ?? '',
                'company_address' => $request->company_address ?? '',
                'note' => $request->note ?? '',
                'approached_technologies' => $approachedTechnologies ?? '',
            ];
            $this->clientRepository->update($clientId, $data);
            return redirect()
                ->route('client-lists')
                ->with(['success' => 'Data store successfully.']);
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
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        try {
            $clientId = Crypt::decrypt($id);
            $this->clientRepository->delete($clientId);
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
}
