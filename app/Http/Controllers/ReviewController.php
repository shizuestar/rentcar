<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\ReviewRequest;
use App\Http\Resources\ReviewsResource;
use App\Http\Resources\ReviewDetailResource;

class ReviewController extends Controller
{
    public function index()
    {
        $reviews = Review::all();
        return response(ReviewsResource::collection($reviews), 200);
    }

    public function store(ReviewRequest $request, Car $car)
    {
        try{
            DB::beginTransaction();

            $validated = $request->validated();
            $review = $car->reviews()->create($validated);

            DB::commit();
            return response()->json([
                'message' => "Succesful Review Car",
                'data' => (new ReviewDetailResource($review->loadMissing('user', 'car')))
            ], 201);
        } catch (\Exception $e){
            DB::rollBack();
            Log::error("Eror Create Review : " . $e->getMessage());
            return response()->json(['message' => "Eror Create Review", 'eror' => $e->getMessage()], 422);
        }
    }
}
