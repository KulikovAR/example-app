<?php

namespace App\Http\Controllers;

use App\Http\Requests\Device\DeviceRequest;
use App\Http\Responses\ApiJsonResponse;
use App\Services\UserDeviceService;
use Illuminate\Http\Request;

class DeviceController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $userDeviceService = new UserDeviceService($request->user());
        return $userDeviceService->getDevices();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        
    }
    
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DeviceRequest $request)
    {
        $userDeviceService = new UserDeviceService($request->user());
        return new ApiJsonResponse();
    }
}