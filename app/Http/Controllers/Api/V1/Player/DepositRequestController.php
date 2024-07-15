<?php

namespace App\Http\Controllers\Api\V1\Player;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\DepositRequest;
use App\Models\DepositRequest as ModelsDepositRequest;
use App\Traits\HttpResponses;
use Exception;
use Illuminate\Support\Facades\Auth;

class DepositRequestController extends Controller
{
    use HttpResponses;

    public function deposit(DepositRequest $request)
    {
        try {
            $player = Auth::user();
        
            $image = $request->file('image');
            $ext = $image->getClientOriginalExtension();
            $filename = uniqid('deposit') . '.' . $ext; // Generate a unique filename
            $image->move(public_path('assets/img/depositRequest/'), $filename); // Save the file
       
            $deposit = ModelsDepositRequest::create([
                    'user_payment_id' => $request->agent_payment_id,
                    'user_id' => $player->id ,
                    'agent_id' => $player->agent_id,
                    'image' => $filename,
                    'amount' => $request->amount,
                    'note' => $request->note
            ]);

    
            return $this->success($deposit,'Deposit Request Success');
        }catch(Exception $e) {
            $this->error('',$e->getMessage(),401);
        }
    }

    public function log()
    {
        $deposit = ModelsDepositRequest::where('user_id', Auth::id())->get();

        return $this->success($deposit);
    }

}
