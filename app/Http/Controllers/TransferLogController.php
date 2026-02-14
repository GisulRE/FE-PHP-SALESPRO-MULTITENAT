<?php

namespace App\Http\Controllers;

use App\TransferRequestLog;
use App\Transfer;
class TransferLogController extends Controller
{
  public function index()
  {
    $logs = TransferRequestLog::with(['transfer', 'user'])
      ->orderBy('created_at', 'desc')
      ->paginate(15);

    return view('transfer.logs', compact('logs'));
  }

  public function show($transfer_id)
  {
    $logs = TransferRequestLog::with(['transfer', 'user'])
      ->where('transfer_id', $transfer_id)
      ->orderBy('created_at', 'desc')
      ->paginate(15);

    $transfer = Transfer::find($transfer_id);

    return view('transfer.logs', compact('logs', 'transfer'));
  }


}
