<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
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

    public function transactionsUser(Request $request) {
        DB::beginTransaction();
        try{
            $user = Auth::user();

            $transactions = $user->transactions;

            return response()->json([
                'data' => TransactionsResource::collection($transactions->loadMissing('user', 'car', 'payment'))
            ], 201);
        } catch (\Exception $e){
            Log::error("Eror Store Transaction : " . $e->getMessage());
            return response()->json(['message' => "Eror Store Transaction", 'eror' => $e->getMessage()], 422);
        }
    }

    public function show(Transaction $transaction)
    {
        return response(new TransactionDetailResource($transaction->loadMissing('user', 'car', 'payment')), 200);
    }

    public function store(TransactionRequest $request){
        try{
            DB::beginTransaction();

            $car = Car::findOrFail($request->car_id);
            if ($car->status === "rented") {
                return response()->json(['message' => "Car has been Rented"], 422);
            }
            $validated = $request->validated();
            $transaction = Transaction::create($validated);
            $transaction->car()->update(['status' => 'rented']);

            DB::commit();
            return response()->json([
                'message' => "Succesful Rented Car",
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
            if ($request->hasFile('payment_proof')) {
                $file = $request->file('payment_proof');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('public/payments', $filename); // Simpan di storage/public/payments
                $validated['payment_proof'] = str_replace('public/', 'storage/public/', $path); // Ubah path agar bisa diakses
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
    public function cancelTransaction($id)
    {
        try {
            DB::beginTransaction();

            $transaction = Transaction::findOrFail($id);
            if ($transaction->status !== 'pending') {
                return response()->json(['message' => 'Only pending transactions can be canceled'], 422);
            }
            $transaction->update(['status' => 'cancel']);
            $transaction->car()->update(['status' => 'available']);

            DB::commit();
            return response()->json(['message' => 'Transaction has been canceled successfully'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error canceling transaction: " . $e->getMessage());
            return response()->json(['message' => 'Error canceling transaction', 'error' => $e->getMessage()], 500);
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

    public function exportTransactions(Request $request)
    {
        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date|after_or_equal:start',
        ]);

        $transactions = Transaction::whereBetween('rent_date', [$request->start, $request->end])
            ->with(['user', 'car', 'payment'])
            ->get();

        return TransactionsResource::collection($transactions);
    }

    public function downloadTransactionsPDF(Request $request)
    {
        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date|after_or_equal:start',
        ]);

        $transactions = Transaction::whereBetween('rent_date', [$request->start, $request->end])
            ->with(['user', 'car', 'payment'])
            ->get();

        $pdf = Pdf::loadView('pdf.transactions', ['transactions' => $transactions]);

        return $pdf->download('transactions_report.pdf');
    }
}
