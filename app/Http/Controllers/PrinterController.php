<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\PrinterConfig;
use App\Category;

class PrinterController extends Controller
{
    public function index()
    {
        //$role = Role::find(Auth::user()->role_id);
        //if($role->hasPermissionTo('payroll')){
            $lims_printers_list = PrinterConfig::all();
            $lims_categories_list = Category::where('is_active', true)->get();
            return view('setting.printer_setting', compact('lims_printers_list', 'lims_categories_list'));
        /*}
        else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');*/
    }

    public function create()
    {
        //
    }

    public function store(Request $request){
        $data = $request->all();
        $data['status'] = true;
        PrinterConfig::create($data);
        $message = "Registrado con éxito";
        return redirect('printer')->with('message', $message);;
    }

    public function edit($id){
        $lims_printer_data = PrinterConfig::findOrFail($id);
        return $lims_printer_data;
    }

    public function update(Request $request, $id)
    {
        $lims_printer_data = PrinterConfig::findOrFail($request->printer_id);
        $lims_printer_data->name = $request->name;
        $lims_printer_data->printer = $request->printer;
        $lims_printer_data->host_address = $request->host_address;
        $lims_printer_data->type = $request->type;
        $lims_printer_data->category_id = $request->category_id;
        $lims_printer_data->status = $request->status;
        $lims_printer_data->save();
        $message = "Impresora actualizada con éxito";
        return redirect('printer')->with('message', $message);;
    }

    public function deleteBySelection(Request $request)
    {
        $printer_id = $request['printerIdArray'];
        foreach ($printer_id as $id) {
            $lims_printer_data = PrinterConfig::find($id);
            $lims_printer_data->delete();
        }
        return 'Impresoras eliminadas con éxito!';
    }

    public function destroy($id)
    {
        $lims_printer_data = PrinterConfig::find($id);
        $lims_printer_data->delete();
        return redirect('printer')->with('not_permitted', 'Impresora eliminada con éxito');
    }
}
