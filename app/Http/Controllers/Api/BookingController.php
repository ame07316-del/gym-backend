<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'phone' => 'required|string',
            'plan' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $booking = Booking::create($validated);

        return response()->json([
            'message' => 'تم الحجز بنجاح!',
            'data' => $booking
        ], 201);
    }
}
