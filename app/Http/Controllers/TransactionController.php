<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\PaymentRequest;
use App\Http\Requests\TransactionRequest;
use App\Http\Resources\TransactionsResource;
use App\Http\Resources\TransactionDetailResource;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::all();

        return response(TransactionsResource::collection($transactions->loadMissing('user', 'car', 'payment')), 200);
    }

    public function show(Transaction $transaction)
    {
        return response(new TransactionDetailResource($transaction->loadMissing('user', 'car', 'payment')), 200);
    }

    public function store(TransactionRequest $request){
        try{
            DB::beginTransaction();

            $validated = $request->validated();
            $car = Car::findOrFail($validated['car_id']);
            $carStatus = $car->status;
            if(!$carStatus || $carStatus === "rented"){
                return response()->json(['message' => "Car has been Rented"], 422);
            } else{
                $transaction = Transaction::create($validated);
                $transaction->car()->update(['status' => 'rented']);
            }

            DB::commit();
            return response()->json([
                'message' => "Succesful Create Transaction",
                'data' => (new TransactionDetailResource($transaction->loadMissing('user', "car")))
            ], 201);
        } catch (\Exception $e){
            DB::rollBack();
            Log::error("Eror Store Transaction : " . $e->getMessage());
            return response()->json(['message' => "Eror Store Transaction", 'eror' => $e->getMessage()], 422);
        }
    }

    public function payTransaction(PaymentRequest $request, Transaction $transaction)
    {
        try{
            DB::beginTransaction();

            $validated = $request->validated();
            if($transaction->status !== "pending"){
                return response()->json(["message" => "Transaction has payed!"], 422);
            }
            if($request->amount < ($transaction->car->rent_price)){
                return response()->json(["message" => "The amount money payyed is less!"], 422);
            }

            $transaction->payment()->create($validated);
            $transaction->update(['status' => "paid"]);

            DB::commit();
            return response()->json([
                'message' => "Succesful Payed Transaction",
                'data' => (new TransactionDetailResource($transaction->loadMissing('user', "car", "payment")))
            ], 201);
        } catch (\Exception $e){
            DB::rollBack();
            Log::error("Eror Pay Transaction : " . $e->getMessage());
            return response()->json(['message' => "Eror Pay Transaction", 'eror' => $e->getMessage()], 422);
        }
    }

    // Only Admin
    public function verifyPayment(Transaction $transaction){
        try{
            DB::beginTransaction();

            if($transaction->payment->status !== "pending"){
                return response()->json(["message" => "Payment was verifyed!"], 422);
            }
            $transaction->payment()->update(['status' => 'verify']);

            DB::commit();
            return response()->json([
                'message' => "Succesful Verifyed Payment!",
                'data' => (new TransactionDetailResource($transaction->loadMissing('user', "car", "payment")))
            ], 201);

        } catch (\Exception $e){
            DB::rollBack();
            Log::error("Eror Verify Payment : " . $e->getMessage());
            return response()->json(['message' => "Eror Verifying Payment", 'eror' => $e->getMessage()], 422);
        }
    }

    public function completeTransaction(Transaction $transaction)
    {
        try{
            DB::beginTransaction();

            if($transaction->status === 'paid'){
                $transaction->update(["status" => "completed"]);
                $transaction->payment()->update(["status" => "verify"]);
                $transaction->car()->update(['status' => 'available']);
            } else{
                return response()->json(['message' => "Can't take Action"],422);
            }

            DB::commit();
            return response()->json([
                'message' => "Transactiuon Completed!",
                'data' => (new TransactionDetailResource($transaction->loadMissing('user', "car", "payment")))
            ], 201);

        } catch (\Exception $e){
            DB::rollBack();
            Log::error("Eror Set Complete Transaction : " . $e->getMessage());
            return response()->json(['message' => "Eror Set Complete Transaction", 'eror' => $e->getMessage()], 422);
        }
    }
}
