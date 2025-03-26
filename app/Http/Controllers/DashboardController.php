<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Car;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Menghitung jumlah transaksi user berdasarkan status
        $totalTransactions = Transaction::where('user_id', $user->id)->count();
        $pendingTransactions = Transaction::where('user_id', $user->id)->where('status', 'pending')->count();
        $completedTransactions = Transaction::where('user_id', $user->id)->where('status', 'completed')->count();
        $canceledTransactions = Transaction::where('user_id', $user->id)->where('status', 'cancel')->count();

        // Mengambil total pembayaran user
        $totalPayment = Transaction::where('user_id', $user->id)->where('status', 'paid')->sum('total');

        // Mengambil daftar mobil yang pernah direntalkan oleh user
        $carsRented = Transaction::where('user_id', $user->id)
            ->with('car')
            ->get()
            ->pluck('car')
            ->unique(function ($car) {
                return $car->name . '-' . $car->no_plat; // Bedakan hanya jika no_plat berbeda
            })
            ->values();

        return response()->json([
            'total_transactions' => $totalTransactions,
            'pending_transactions' => $pendingTransactions,
            'completed_transactions' => $completedTransactions,
            'canceled_transactions' => $canceledTransactions,
            'total_payment' => $totalPayment,
            'cars_rented' => $carsRented
        ], 200);
    }
}
