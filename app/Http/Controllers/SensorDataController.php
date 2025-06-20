<?php

namespace App\Http\Controllers;

use App\Models\SensorData;
use Illuminate\Http\Request;

class SensorDataController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'temp' => 'required|numeric',
            'humi' => 'required|numeric',
            'light' => 'required|integer',
        ]);

        SensorData::create($validated);

        return response()->json(['message' => 'Dữ liệu đã được lưu']);
    }
}
