<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Http\Requests\CarRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\CarsResource;
use App\Http\Resources\CarDetailResource;

class CarController extends Controller
{
    public function index()
    {
        $cars = Car::orderByRaw("CASE WHEN status = 'available' THEN 1 ELSE 2 END")->get();
        return response(CarsResource::collection($cars), 200);
    }

    public function show(Car $car)
    {
        return response(new CarDetailResource($car), 200);
    }

    public function store(CarRequest $request)
    {
        try{
            DB::beginTransaction();
            $validated = $request->validated();

            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('cars', 'public'); // Simpan ke folder storage/app/public/cars
                $validated['image'] = $imagePath;
            }
            $car = Car::create($validated);

            DB::commit();
            return response()->json([
                'message' => "Succesful Create Car",
                'data' => (new CarDetailResource($car))
            ], 201);
        } catch (\Exception $e){
            DB::rollBack();
            Log::error("Eror Store Car : " . $e->getMessage());
            return response()->json(['message' => "Eror Store Car", 'eror' => $e->getMessage()], 422);
        }
    }

    public function update(CarRequest $request, Car $car)
    {
        try{
            DB::beginTransaction();

            $validated = $request->validated();
            $car->update($validated);
            
            DB::commit();
            return response()->json([
                'message' => "Succesful Update Car",
                'data' => (new CarDetailResource($car))
            ], 200);
        } catch (\Exception $e){
            DB::rollBack();
            Log::error("Eror Update Car : " . $e->getMessage());
            return response()->json(['message' => "Eror Update Car", 'eror' => $e->getMessage()], 422);
        }
    }

    public function destroy(Car $car)
    {
        try{
            DB::beginTransaction();

            $car->delete();
            
            DB::commit();
            return response()->json([
                'message' => "Succesful Delete Car",
                'data' => (new CarDetailResource($car))
            ], 200);
        } catch (\Exception $e){
            DB::rollBack();
            Log::error("Eror Delete Car : " . $e->getMessage());
            return response()->json(['message' => "Eror Destroy Car", 'eror' => $e->getMessage()], 422);
        }
    }
}
