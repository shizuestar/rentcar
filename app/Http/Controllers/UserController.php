<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\UserRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\UsersResource;
use App\Http\Resources\UserDetailResource;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return response(UsersResource::collection($users), 200);
    }

    public function show(User $user)
    {
        return response(new UserDetailResource($user), 200);
    }

    public function store(UserRequest $request)
    {
        try{
            DB::beginTransaction();

            $validated = $request->validated();
            $validated['password'] = Hash::make($validated['password']);

            $user = User::create($validated);
            DB::commit();
            return response()->json([
                'message' => "Succesful Create User",
                'data' => (new UserDetailResource($user))
            ], 201);
        } catch (\Exception $e){
            DB::rollBack();
            Log::error("Eror Store User : " . $e->getMessage());
            return response()->json(['message' => "Eror Store User", 'eror' => $e->getMessage()], 422);
        }
    }

    public function update(UserRequest $request, User $user)
    {
        try{
            DB::beginTransaction();

            $validated = $request->validated();
            $validated['password'] = Hash::make($validated['password']);

            $user->update($validated);
            DB::commit();
            return response()->json([
                'message' => "Succesful Update User",
                'data' => (new UserDetailResource($user))
            ], 200);
        } catch (\Exception $e){
            DB::rollBack();
            Log::error("Eror Store User : " . $e->getMessage());
            return response()->json(['message' => "Eror Update User", 'eror' => $e->getMessage()], 422);
        }
    }

    public function destroy(User $user)
    {
        try{
            DB::beginTransaction();

            $user->delete();
            
            DB::commit();
            return response()->json([
                'message' => "Succesful Delete User",
                'data' => (new UserDetailResource($user))
            ], 200);
        } catch (\Exception $e){
            DB::rollBack();
            Log::error("Eror Store User : " . $e->getMessage());
            return response()->json(['message' => "Eror Destroy User", 'eror' => $e->getMessage()], 422);
        }
    }
}
