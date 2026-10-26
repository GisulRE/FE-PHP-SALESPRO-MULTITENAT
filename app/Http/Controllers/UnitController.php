<?php

namespace App\Http\Controllers;

use Auth;
use App\Unit;
use App\PosSetting;
use App\SiatParametricaVario;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Log;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UnitController extends Controller
{
    public function index()
    {
        $role = Role::find(Auth::user()->role_id);
        if($role->hasPermissionTo('unit')) {
            $lims_unit_all = Unit::where('is_active', true)->get();
            $siat_unidades = $this->obtenerUnidadesMedidaSiat();
            return view('unit.create', compact('lims_unit_all', 'siat_unidades'));
        }
        else
            return redirect()->back()->with('not_permitted', '¡Lo siento! No tienes permiso para acceder a este módulo.');
    }

    public function getUnidadesMedida()
    {
        Log::info("UNIT GET UNIDADES MEDIDA: Iniciando petición");

        $unidades = $this->obtenerUnidadesMedidaSiat();

        // Fallback remoto a Optimo si la base de datos local estuviera vacía y hay token
        if (empty($unidades) && Session::get('token_siat') != null) {
            $pos_setting = PosSetting::latest()->first();
            if ($pos_setting) {
                $nitValue = $pos_setting->nit_emisor;
                $bearer = 'Bearer ' . Session::get('token_siat');
                $host = $pos_setting->url_optimo;
                $url = $host . '/datosincronizado/v1/listar-nit';

                Log::info("UNIT GET UNIDADES MEDIDA: Enviando petición fallback", [
                    'url' => $url,
                    'nit' => $nitValue,
                    'parametro' => 'unidades_medida',
                ]);

                $body = [
                    'nit' => $nitValue,
                    'parametro' => 'unidades_medida',
                    'sucursal' => 0,
                    'codigoPuntoVenta' => 0,
                ];

                try {
                    $response = Http::withHeaders([
                        'Authorization' => $bearer,
                        'Content-Type' => 'application/json',
                    ])->post($url, $body);

                    if ($response->successful()) {
                        $data = $response->json();
                        if ($data && isset($data['ENTITIES'])) {
                            $unidades = $data['ENTITIES'];
                        }
                    }
                } catch (\Exception $e) {
                    Log::error("UNIT GET UNIDADES MEDIDA: Excepción remota", [
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        Log::info("UNIT GET UNIDADES MEDIDA: Finalizado", [
            'total_retornadas' => count($unidades),
        ]);

        return response()->json(['unidades' => $unidades]);
    }

    private function obtenerUnidadesMedidaSiat()
    {
        $items = SiatParametricaVario::where('tipo_clasificador', 'unidadMedida')
            ->whereNotNull('codigo_clasificador')
            ->where('codigo_clasificador', '!=', '')
            ->get();

        if ($items->isEmpty()) {
            $items = SiatParametricaVario::withoutGlobalScope('company')
                ->where('tipo_clasificador', 'unidadMedida')
                ->whereNotNull('codigo_clasificador')
                ->where('codigo_clasificador', '!=', '')
                ->get();
        }

        if ($items->isNotEmpty()) {
            return $items->unique('codigo_clasificador')
                ->sortBy(function ($item) {
                    return (int) $item->codigo_clasificador;
                })
                ->values()
                ->map(function ($item) {
                    return [
                        'valor' => (string) $item->codigo_clasificador,
                        'descripcion' => $item->codigo_clasificador . ' - ' . $item->descripcion,
                        'clave' => 'unidadMedida',
                    ];
                })
                ->all();
        }

        return [];
    }

    public function store(Request $request)
    {
        $companyId = Auth::user()->company_id;
        $this->validate($request, [
            'unit_code' => [
                'max:255',
                Rule::unique('units')->where(function ($query) use ($companyId) {
                    return $query->where('is_active', 1)->where('company_id', $companyId);
                }),
            ],

            'unit_name' => [
                'max:255',
                Rule::unique('units')->where(function ($query) use ($companyId) {
                    return $query->where('is_active', 1)->where('company_id', $companyId);
                }),
            ]

        ]);
        $input = $request->all();
        $input['is_active'] = true;
        $input['company_id'] = $companyId;
        if(!$input['base_unit']){
            $input['operator'] = '*';
            $input['operation_value'] = 1;
        }
        Unit::create($input);
        return redirect('unit');
    }

    public function limsUnitSearch()
    {
        $lims_unit_name = $_GET['lims_unitNameSearch'];
        $lims_unit_all = Unit::where('unit_name', $lims_unit_name)->paginate(5);
        $lims_unit_list = Unit::all();
        $siat_unidades = $this->obtenerUnidadesMedidaSiat();
        return view('unit.create', compact('lims_unit_all','lims_unit_list', 'siat_unidades'));
    }

    public function edit($id)
    {
        $lims_unit_data = Unit::findOrFail($id);
        return $lims_unit_data;
    }

    public function update(Request $request, $id)
    {
        $companyId = Auth::user()->company_id;
        $this->validate($request, [
            'unit_code' => [
                'max:255',
                Rule::unique('units')->ignore($request->unit_id)->where(function ($query) use ($companyId) {
                    return $query->where('is_active', 1)->where('company_id', $companyId);
                }),
            ],
            'unit_name' => [
                'max:255',
                Rule::unique('units')->ignore($request->unit_id)->where(function ($query) use ($companyId) {
                    return $query->where('is_active', 1)->where('company_id', $companyId);
                }),
            ]
        ]);

        $input = $request->all();
        $lims_unit_data = Unit::where('id',$input['unit_id'])->first();
        $lims_unit_data->update($input);
        return redirect('unit');
    }

    public function importUnit(Request $request)
    {  
        //get file
        $filename =  $request->file->getClientOriginalName();
        $upload=$request->file('file');
        $filePath=$upload->getRealPath();
        //open and read
        $file=fopen($filePath, 'r');
        $header= fgetcsv($file);
        $escapedHeader=[];
        //validate
        foreach ($header as $key => $value) {
            $lheader=strtolower($value);
            $escapedItem=preg_replace('/[^a-z]/', '', $lheader);
            array_push($escapedHeader, $escapedItem);
        }
        //looping through othe columns
        $lims_unit_data = [];
        while($columns=fgetcsv($file))
        {
            if($columns[0]=="")
                continue;
            foreach ($columns as $key => $value) {
                $value=preg_replace('/\D/','',$value);
            }
            $data= array_combine($escapedHeader, $columns);

            $unit = Unit::firstOrNew(['unit_code' => $data['code'],'is_active' => true ]);
            $unit->unit_code = $data['code'];
            $unit->unit_name = $data['name'];
            if($data['baseunit']==null)
                $unit->base_unit = null;
            else{
                $base_unit = Unit::where('unit_code', $data['baseunit'])->first();
                $unit->base_unit = $base_unit->id;
            }
            if($data['operator'] == null)
                $unit->operator = '*';
            else
                $unit->operator = $data['operator'];
            if($data['operationvalue'] == null)
                $unit->operation_value = 1;
            else 
                $unit->operation_value = $data['operationvalue'];
            $unit->save();
        }
        return redirect('unit')->with('message', 'Unidad importada con éxito');
        
    }

    public function deleteBySelection(Request $request)
    {
        $unit_id = $request['unitIdArray'];
        foreach ($unit_id as $id) {
            $lims_unit_data = Unit::findOrFail($id);
            $lims_unit_data->is_active = false;
            $lims_unit_data->save();
        }
        return 'Unidad eliminada con éxito!';
    }

    public function destroy($id)
    {
        $lims_unit_data = Unit::findOrFail($id);
        $lims_unit_data->is_active = false;
        $lims_unit_data->save();
        return redirect('unit');
    }
}
