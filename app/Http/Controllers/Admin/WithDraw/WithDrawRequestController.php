<?php

namespace App\Http\Controllers\Admin\WithDraw;

use App\Enums\TransactionName;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WithDrawRequest;
use App\Services\WalletService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WithDrawRequestController extends Controller
{
    public function index()
    {
        $withdraws = WithDrawRequest::with(['user', 'paymentType'])->where('agent_id', Auth::id())->orderby('id', 'desc')->get();

        return view('admin.withdraw_request.index', compact('withdraws'));
    }

    public function statusChangeIndex(Request $request, WithDrawRequest $withdraw)
    {
        $request->validate([
            'status' => 'required|in:0,1,2',
            'amount' => 'required|numeric|min:0',
            'player' => 'required|exists:users,id',
        ]);
        $agent = Auth::user();
        $player = User::find($request->player);

        // Check if the status is being approved and balance is sufficient
        if ($request->status == 1 && $agent->balanceFloat < $request->amount) {
            return redirect()->back()->with('error', 'You do not have enough balance to transfer!');
        }

        if ($request->status == 1 && $player->balanceFloat < $request->amount) {
            return redirect()->back()->with('error', 'Player does not have enough balance to transfer!');
        }

        if ($request->status == 1) {
            $withdraw->update([
                'status' => $request->status,
            ]);
            app(WalletService::class)->transfer($player, $agent, $request->amount, TransactionName::DebitTransfer);
        }

        return redirect()->route('admin.agent.withdraw')->with('success', 'WithDraw status updated successfully!');
    }

    public function statusChangeReject(Request $request, WithDrawRequest $withdraw)
    {
        $request->validate([
            'status' => 'required|in:0,1,2',
        ]);

        try {
            // Update the deposit status
            $withdraw->update([
                'status' => $request->status,
            ]);

            return redirect()->route('admin.agent.withdraw')->with('success', 'Withdraw status updated successfully!');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
