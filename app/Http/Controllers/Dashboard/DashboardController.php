<?php

namespace App\Http\Controllers\Dashboard;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            return view('dashboard');
        } catch (Exception $e) {
            Log::error($e);
            return redirect()
                ->back()
                ->with([
                    'error' => 'Somethig went wrong',
                    'error_msg' => $e->getMessage(),
                ]);
        }
    }
}