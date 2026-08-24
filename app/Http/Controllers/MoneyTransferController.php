<?php

namespace App\Http\Controllers;

use App\MoneyTransfer;
use App\Account;
use App\Cashier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class MoneyTransferController extends Controller
{
    public function index()
    {
        $role = Role::find(Auth::user()->role_id);
        if($role->hasPermissionTo('money-transfer')){
            $lims_money_transfer_all = MoneyTransfer::get();
            $lims_account_list = Account::where('is_active', true)->get();
            return view('money_transfer.index', compact('lims_money_transfer_all', 'lims_account_list'));
        }
        else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $data = $request->all();

        $cashierValidation = $this->validateOpenCashierForSourceAccount($data['from_account_id'] ?? null);
        if (!$cashierValidation['ok']) {
            return redirect()->back()->with('not_permitted', $cashierValidation['message']);
        }

        $data['reference_no'] = 'mtr-' . date("Ymd") . '-'. date("his");
        MoneyTransfer::create($data);
        return redirect()->back()->with('message', 'Registrado con éxito');
    }

    public function show(MoneyTransfer $moneyTransfer)
    {
        //
    }

    public function edit(MoneyTransfer $moneyTransfer)
    {
        //
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();

        $cashierValidation = $this->validateOpenCashierForSourceAccount($data['from_account_id'] ?? null);
        if (!$cashierValidation['ok']) {
            return redirect()->back()->with('not_permitted', $cashierValidation['message']);
        }

        MoneyTransfer::find($data['id'])->update($data);
        return redirect()->back()->with('message', 'Tranferencia de Dinero actualizado con éxito');
    }

    public function destroy($id)
    {
        MoneyTransfer::find($id)->delete();
        return redirect()->back()->with('not_permitted', 'Registro eliminado con éxito');
    }

    private function validateOpenCashierForSourceAccount($fromAccountId)
    {
        if (empty($fromAccountId)) {
            return [
                'ok' => false,
                'message' => 'Debe seleccionar una cuenta de origen válida.'
            ];
        }

        // Si esta cuenta nunca manejó caja, no se bloquea por estado de caja.
        $hasCashierHistory = Cashier::where('account_id', $fromAccountId)->exists();
        if (!$hasCashierHistory) {
            return [
                'ok' => true,
                'message' => ''
            ];
        }

        $openCashier = Cashier::where('account_id', $fromAccountId)
            ->where('is_active', true)
            ->whereNull('end_date')
            ->first();

        if (!$openCashier) {
            return [
                'ok' => false,
                'message' => 'La caja de origen está cerrada. Debe abrir caja para realizar transferencias.'
            ];
        }

        return [
            'ok' => true,
            'message' => ''
        ];
    }
}
