<?php

namespace App\Http\Controllers\Admin\Deposit;

use App\Enums\TransactionName;
use App\Http\Controllers\Controller;
use App\Models\DepositRequest;
use App\Models\User;
use App\Models\WithDrawRequest;
use App\Services\WalletService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepositRequestController extends Controller
{
    public function index()
    {
        $deposits = DepositRequest::with(['user', 'userPayment'])->get();

        return view('admin.deposit_request.index', compact('deposits'));
    }

    public function show($id)
    {
        $deposit = DepositRequest::find($id);

        return view('admin.deposit_request.show', compact('deposit'));
    }

    public function statusChange(Request $request, DepositRequest $deposit)
    {

        $request->validate([
            'status' => 'required|in:0,1,2'
        ]);

        try {
            $agent = Auth::user();
            $player = User::find($request->player);
            if ($agent->balanceFloat < $request->amount) {
                return redirect()->back()->with('error', 'You do not have enough balance to transfer!');
            }

            $deposit->update([
                'status' => $request->status
            ]);

            app(WalletService::class)->transfer($agent, $player, $request->amount, TransactionName::DebitTransfer);

            return redirect()->route('admin.agent.deposit')->with('success', 'Agent status switch successfully!');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

}