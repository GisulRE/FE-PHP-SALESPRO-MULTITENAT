<?php

namespace App\Http\Controllers;

use Config;
use DB;
use App\Biller;

use App\Account;
use App\Customer;
use App\SiatCufd;
use App\Warehouse;
use Carbon\Carbon;
use App\HrmSetting;
use App\PosSetting;
use Clickatell\Rest;
use App\CustomerGroup;
use App\PrinterConfig;
use App\GeneralSetting;
use App\SiatPuntoVenta;
use Log;
use PDO;
use PDOException;
use Twilio\Rest\Client;
use Illuminate\Http\Request;
use App\Http\Traits\CufdTrait;
use Clickatell\ClickatellException;
use Illuminate\Support\Facades\Artisan;

class SettingController extends Controller
{
    use CufdTrait;

    public function emptyDatabase()
    {
        $tables = DB::select('SHOW TABLES');
        $str = 'Tables_in_' . env('DB_DATABASE');
        foreach ($tables as $table) {
            if ($table->$str != 'accounts' && $table->$str != 'general_settings' && $table->$str != 'hrm_settings' && $table->$str != 'languages' && $table->$str != 'migrations' && $table->$str != 'password_resets' && $table->$str != 'permissions' && $table->$str != 'pos_setting' && $table->$str != 'roles' && $table->$str != 'role_has_permissions' && $table->$str != 'users') {
                DB::table($table->$str)->truncate();
            }
        }
        return redirect()->back()->with('message', 'Base de Datos Limpiado con éxito');
    }

    public function backupDatabase()
    {
        $lims_general_setting_data = GeneralSetting::latest()->first();
        //ENTER THE RELEVANT INFO BELOW
        if (env('DB_HOST') != null && env('DB_HOST') != '') {
            $server = env('DB_HOST');
            //$username = env('DB_USERNAME');
            //$mysqlPassword = env('DB_PASSWORD');
            $port = env('DB_PORT');
            $database = env('DB_DATABASE');
        } else {
            $linkdb = config('database.connections');
            $server = $linkdb['mysql']['host'];
            $port = $linkdb['mysql']['port'];
            //$username = $linkdb['mysql ']['username'];
            //$password = $linkdb['mysql']['password'];
            $database = $linkdb['mysql']['database'];
        }
        if ($lims_general_setting_data)
            $file_name = $lims_general_setting_data->site_title . '_database_backup_on_' . date('y-m-d') . '.sql';
        else
            $file_name = 'pos_database_backup_on_' . date('y-m-d') . '.sql';

        $queryTables = DB::select(DB::raw('SHOW TABLES'));
        foreach ($queryTables as $table) {
            foreach ($table as $tName) {
                $tables[] = $tName;
            }
        }

        $connect = self::db();
        $get_all_table_query = "SHOW TABLES";
        $statement = $connect->prepare($get_all_table_query);
        $statement->execute();
        $result = $statement->fetchAll();
        $output = '';
        $output .= "\n-- Servidor: " . $server . ":" . $port;
        $output .= "\n-- Tiempo de generación: " . date('d-m-y') . " a las " . date('h:i:s');
        $output .= "\n--";
        $output .= "\n-- Base de datos: `" . $database . "`";
        $output .= "\n--";
        $output .= "\n-- --------------------------------------------------------";
        $output .= "\n";
        foreach ($tables as $table) {
            $show_table_query = "SHOW CREATE TABLE " . $table . "";
            $statement = $connect->prepare($show_table_query);
            $statement->execute();
            $show_table_result = $statement->fetchAll();

            foreach ($show_table_result as $show_table_row) {
                $output .= "\n-- ";
                $output .= "\n-- Estructura de tabla para la tabla `" . $table . "`";
                $output .= "\n-- ";
                $output .= "\n\n" . $show_table_row["Create Table"] . ";\n\n";
            }
            $select_query = "SELECT * FROM " . $table . "";
            $statement = $connect->prepare($select_query);
            $statement->execute();
            $total_row = $statement->rowCount();
            $output .= "\n-- ";
            $output .= "\n-- Volcado de datos para la tabla `" . $table . "`";
            $output .= "\n-- ";
            for ($count = 0; $count < $total_row; $count++) {
                $single_result = $statement->fetch(\PDO::FETCH_ASSOC);
                $table_column_array = array_keys($single_result);
                $table_value_array = array_values($single_result);
                $output .= "\nINSERT INTO $table (";
                $output .= "" . implode(", ", $table_column_array) . ") VALUES (";
                $output .= "'" . implode("','", $table_value_array) . "');\n";
            }
        }

        $file_handle = fopen($file_name, 'w+');
        fwrite($file_handle, $output);
        fclose($file_handle);
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . basename($file_name));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file_name));
        //ob_clean();
        flush();
        readfile($file_name);
        unlink($file_name);
    }

    public function moduleQr()
    {
        $tables = DB::select('SHOW TABLES');
        $str = 'Tables_in_' . env('DB_DATABASE');
        foreach ($tables as $table) {
            if ($table->$str != 'accounts' && $table->$str != 'general_settings' && $table->$str != 'hrm_settings' && $table->$str != 'languages' && $table->$str != 'migrations' && $table->$str != 'password_resets' && $table->$str != 'permissions' && $table->$str != 'pos_setting' && $table->$str != 'roles' && $table->$str != 'role_has_permissions' && $table->$str != 'users') {
                DB::table($table->$str)->truncate();
            }
        }
        return redirect()->back()->with('message', 'Database cleared successfully');
    }

    public function generalSetting()
    {
        $lims_general_setting_data = GeneralSetting::latest()->first();
        $lims_account_list = Account::where('is_active', true)->get();
        $zones_array = array();
        $timestamp = time();
        foreach (timezone_identifiers_list() as $key => $zone) {
            date_default_timezone_set($zone);
            $zones_array[$key]['zone'] = $zone;
            $zones_array[$key]['diff_from_GMT'] = 'UTC/GMT ' . date('P', $timestamp);
        }
        return view('setting.general_setting', compact('lims_general_setting_data', 'lims_account_list', 'zones_array'));
    }

    public function generalSettingStore(Request $request)
    {
        $this->validate($request, [
            'site_logo' => 'image|mimes:jpg,jpeg,png,gif|max:100000',
        ]);

        $data = $request->except('site_logo');
        //writting timezone info in .env file
        $path = '.env';
        $searchArray = array('APP_TIMEZONE=' . env('APP_TIMEZONE'));
        $replaceArray = array('APP_TIMEZONE=' . $data['timezone']);

        file_put_contents($path, str_replace($searchArray, $replaceArray, file_get_contents($path)));

        $general_setting = GeneralSetting::latest()->first();
        $general_setting->id = 1;
        $general_setting->site_title = $data['site_title'];
        $general_setting->currency = $data['currency'];
        $general_setting->currency_position = $data['currency_position'];
        $general_setting->staff_access = $data['staff_access'];
        $general_setting->date_format = $data['date_format'];
        $general_setting->alert_expiration = $data['alert_expiration'];
        $logo = $request->site_logo;
        if ($logo) {
            $logoName = $logo->getClientOriginalName();
            $logo->move('public/logo', $logoName);
            $general_setting->site_logo = $logoName;
        }
        $general_setting->save();
        return redirect()->back()->with('message', 'Datos actualizados con éxito');
    }

    public function changeTheme($theme)
    {
        $lims_general_setting_data = GeneralSetting::latest()->first();
        $lims_general_setting_data->theme = $theme;
        $lims_general_setting_data->save();
    }

    public function mailSetting()
    {
        return view('setting.mail_setting');
    }

    public function mailSettingStore(Request $request)
    {
        $data = $request->all();
        //writting mail info in .env file
        $path = '.env';
        $searchArray = array('MAIL_HOST="' . env('MAIL_HOST') . '"', 'MAIL_PORT=' . env('MAIL_PORT'), 'MAIL_FROM_ADDRESS="' . env('MAIL_FROM_ADDRESS') . '"', 'MAIL_FROM_NAME="' . env('MAIL_FROM_NAME') . '"', 'MAIL_USERNAME="' . env('MAIL_USERNAME') . '"', 'MAIL_PASSWORD="' . env('MAIL_PASSWORD') . '"', 'MAIL_ENCRYPTION="' . env('MAIL_ENCRYPTION') . '"');
        //return $searchArray;

        $replaceArray = array('MAIL_HOST="' . $data['mail_host'] . '"', 'MAIL_PORT=' . $data['port'], 'MAIL_FROM_ADDRESS="' . $data['mail_address'] . '"', 'MAIL_FROM_NAME="' . $data['mail_name'] . '"', 'MAIL_USERNAME="' . $data['mail_address'] . '"', 'MAIL_PASSWORD="' . $data['password'] . '"', 'MAIL_ENCRYPTION="' . $data['encryption'] . '"');

        file_put_contents($path, str_replace($searchArray, $replaceArray, file_get_contents($path)));

        return redirect()->back()->with('message', 'Data updated successfully');
    }

    public function smsSetting()
    {
        return view('setting.sms_setting');
    }

    public function smsSettingStore(Request $request)
    {
        $data = $request->all();
        //writting bulksms info in .env file
        $path = '.env';
        if ($data['gateway'] == 'twilio') {
            $searchArray = array('SMS_GATEWAY=' . env('SMS_GATEWAY'), 'ACCOUNT_SID=' . env('ACCOUNT_SID'), 'AUTH_TOKEN=' . env('AUTH_TOKEN'), 'Twilio_Number=' . env('Twilio_Number'));

            $replaceArray = array('SMS_GATEWAY=' . $data['gateway'], 'ACCOUNT_SID=' . $data['account_sid'], 'AUTH_TOKEN=' . $data['auth_token'], 'Twilio_Number=' . $data['twilio_number']);
        } else {
            $searchArray = array('SMS_GATEWAY=' . env('SMS_GATEWAY'), 'CLICKATELL_API_KEY=' . env('CLICKATELL_API_KEY'));
            $replaceArray = array('SMS_GATEWAY=' . $data['gateway'], 'CLICKATELL_API_KEY=' . $data['api_key']);
        }

        file_put_contents($path, str_replace($searchArray, $replaceArray, file_get_contents($path)));
        return redirect()->back()->with('message', 'Data updated successfully');
    }

    public function createSms()
    {
        $lims_customer_list = Customer::where('is_active', true)->get();
        return view('setting.create_sms', compact('lims_customer_list'));
    }

    public function sendSms(Request $request)
    {
        $data = $request->all();
        $numbers = explode(",", $data['mobile']);

        if (env('SMS_GATEWAY') == 'twilio') {
            $account_sid = env('ACCOUNT_SID');
            $auth_token = env('AUTH_TOKEN');
            $twilio_phone_number = env('Twilio_Number');
            try {
                $client = new Client($account_sid, $auth_token);
                foreach ($numbers as $number) {
                    $client->messages->create(
                        $number,
                        array(
                            "from" => $twilio_phone_number,
                            "body" => $data['message']
                        )
                    );
                }
            } catch (\Exception $e) {
                return redirect()->back()->with('not_permitted', 'Please setup your <a href="sms_setting">SMS Setting</a> to send SMS.');
            }
            $message = "SMS sent successfully";
        } elseif (env('SMS_GATEWAY') == 'clickatell') {
            try {
                $clickatell = new \Clickatell\Rest(env('CLICKATELL_API_KEY'));
                foreach ($numbers as $number) {
                    $result = $clickatell->sendMessage(['to' => [$number], 'content' => $data['message']]);
                }
            } catch (ClickatellException $e) {
                return redirect()->back()->with('not_permitted', 'Please setup your <a href="sms_setting">SMS Setting</a> to send SMS.');
            }
            $message = "SMS sent successfully";
        } else
            return redirect()->back()->with('not_permitted', 'Please setup your <a href="sms_setting">SMS Setting</a> to send SMS.');
        return redirect()->back()->with('message', $message);
    }

    public function hrmSetting()
    {
        $lims_hrm_setting_data = HrmSetting::latest()->first();
        return view('setting.hrm_setting', compact('lims_hrm_setting_data'));
    }

    public function hrmSettingStore(Request $request)
    {
        $data = $request->all();
        $lims_hrm_setting_data = HrmSetting::firstOrNew(['id' => 1]);
        $lims_hrm_setting_data->checkin = $data['checkin'];
        $lims_hrm_setting_data->checkout = $data['checkout'];
        $lims_hrm_setting_data->save();
        return redirect()->back()->with('message', 'Datos HRM actualizado con éxito');

    }
    public function posSetting()
    {
        $lims_customer_list = Customer::where('is_active', true)->get();
        $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        $lims_formatprint_list = array();
        $lims_formatprint_list[0]['id'] = 1;
        $lims_formatprint_list[0]['name'] = "Impresion Predeterminada";
        $lims_formatprint_list[1]['id'] = 2;
        $lims_formatprint_list[1]['name'] = "Impresion Ticket";
        $lims_formatprint_list[2]['id'] = 3;
        $lims_formatprint_list[2]['name'] = "Impresion Media Carta";
        $lims_formatprint_list[3]['id'] = 4;
        $lims_formatprint_list[3]['name'] = "Impresion PDF Matricial (A4)";
        $lims_formatprint_list[4]['id'] = 5;
        $lims_formatprint_list[4]['name'] = "Impresion PDF Matricial (Carta)";
        $lims_formatprint_list[5]['id'] = 6;
        $lims_formatprint_list[5]['name'] = "Impresion Media Carta (EPSAS)";
        $lims_formatprint_list[6]['id'] = 7;
        $lims_formatprint_list[6]['name'] = "Impresion Ticket (MTP-3 80mm)";
        $lims_formatprint_list[7]['id'] = 8;
        $lims_formatprint_list[7]['name'] = "Impresion Media Carta (MOLE)";
        $tipo_emision_list = array();
        $tipo_emision_list[0]['id'] = 1;
        $tipo_emision_list[0]['name'] = "Online";
        $tipo_emision_list[1]['id'] = 3;
        $tipo_emision_list[1]['name'] = "Masivo";
        $lims_biller_list = Biller::where('is_active', true)->get();
        $lims_printer_list = PrinterConfig::where('status', true)->get();
        $lims_pos_setting_data = PosSetting::latest()->first();

        return view('setting.pos_setting', compact('lims_customer_list', 'tipo_emision_list', 'lims_warehouse_list', 'lims_biller_list', 'lims_formatprint_list', 'lims_pos_setting_data', 'lims_printer_list'));
    }

    public function posSettingJSON()
    {
        $lims_pos_setting_data = PosSetting::latest()->first();

        return $lims_pos_setting_data;
    }

    public function posSettingUpdate(Request $request)
    {
        $data = $request->all();
        $pos_setting = PosSetting::firstOrNew(['id' => 1]);
        if (isset($data['hour_resetshift'])) {
            $pos_setting->hour_resetshift = $data['hour_resetshift'];
        }
        if (isset($data['qr_commission'])) {
            $pos_setting->qr_commission = $data['qr_commission'];
        }
        if (isset($data['quotation_printer'])) {
            $pos_setting->quotation_printer = $data['quotation_printer'];
        }
        $pos_setting->save();
        return array('status' => true, 'message' => 'Actualizado con éxito');
    }

    public function posSettingStore(Request $request)
    {
        $data = $request->all();

        $pos_setting = PosSetting::firstOrNew(['id' => 1]);
        $pos_setting->id = 1;
        $pos_setting->customer_id = $data['customer_id'];
        $pos_setting->warehouse_id = $data['warehouse_id'];
        $pos_setting->type_print = $data['type_print_id'];
        $pos_setting->print_order = $data['type_printorder_id'];
        $pos_setting->biller_id = $data['biller_id'];
        $pos_setting->product_number = $data['product_number'];
        $pos_setting->t_c = $data['t_c'];
        $pos_setting->facturacion_id = $data['facturacion_id'];
        $pos_setting->codigo_emision = $data['codigo_emision'];
        $pos_setting->tipo_moneda_siat = $data['tipo_moneda_siat'];
        $pos_setting->nit_emisor = $data['nit_emisor'];
        $pos_setting->razon_social_emisor = $data['razon_social_emisor'];
        $pos_setting->direccion_emisor = $data['direccion_emisor'];
        $pos_setting->user_siat = $data['user_siat'];
        $pos_setting->pass_siat = $data['pass_siat'];
        $pos_setting->url_siat = $data['url_siat'];
        $pos_setting->url_operaciones = $data['url_operaciones'];
        $pos_setting->url_optimo = $data['url_optimo'];
        $pos_setting->url_cobranza = $data['url_cobranza'];
        $pos_setting->cant_decimal = $data['cant_decimal'];
        $pos_setting->cufd_centralizado = isset($data['cufd_centralizado']) ? 1 : 0;

        $pos_setting->url_whatsapp = $data['url_whatsapp'] ?? null;
        $pos_setting->require_transfer_authorization = isset($data['require_transfer_authorization']) ? 1 : 0;


        if (!isset($data['print']))
            $pos_setting->print = false;
        else
            $pos_setting->print = true;

        if (!isset($data['print_presale']))
            $pos_setting->print_presale = false;
        else
            $pos_setting->print_presale = true;

        if (!isset($data['date_sell']))
            $pos_setting->date_sell = false;
        else
            $pos_setting->date_sell = true;

        if (!isset($data['keybord_active']))
            $pos_setting->keybord_active = false;
        else
            $pos_setting->keybord_active = true;

        if (!isset($data['keybord_presale']))
            $pos_setting->keybord_presale = false;
        else
            $pos_setting->keybord_presale = true;

        if (!isset($data['customer_sucursal']))
            $pos_setting->customer_sucursal = false;
        else
            $pos_setting->customer_sucursal = true;

        if (!isset($data['user_category']))
            $pos_setting->user_category = false;
        else
            $pos_setting->user_category = true;

        $pos_setting->save();
        return redirect()->back()->with('message', 'Ajustes POS actualizado con éxito');
    }

    public function clearRoute(Request $request)
    {
        try {
            Artisan::call('migrate', ['--force' => true,]);
            Artisan::call('storage:link', []);
            Artisan::call('config:clear');
            Artisan::call('view:clear');
            Artisan::call('cache:clear');
        } catch (Exception $ex) {
            $request->session()->flash('error', $ex->getMessage());
            return $ex->getMessage();
        }
        return '<h1>Route cache cleared</h1>';
    }

    public function runTareaProgramada(Request $request)
    {
        $bandera = false;
        try {
            // Artisan::call('taskcufd:renovar');
            Artisan::call('schedule:run');
            return $bandera = true;
        } catch (Exception $ex) {
            $request->session()->flash('error', $ex->getMessage());
            return $bandera;
        }

    }

    public function forzarRenovarCUFD()
    {
        return $this->forceRenovarCUFD(); //CufdTrait, devuelve boolean
    }

    public function listaPuntoVenta()
    {
        $items = SiatPuntoVenta::whereNotNull('codigo_cuis')->where([['is_siat', true]])->orderBy('nombre_punto_venta')->get();

        $lista_final = collect();
        //iterar todos los puntos de ventas.         
        foreach ($items as $value) {
            if ($value->codigo_cuis) {
                $item = collect($value);
                $registro = SiatCufd::where('sucursal', $value->sucursal)->where('codigo_punto_venta', $value->codigo_punto_venta)->where('estado', true)->first();
                if (isset($registro->fecha_vigencia)) {
                    $formato_fecha = GeneralSetting::first()->date_format;
                    $fecha = new Carbon($registro->fecha_vigencia);
                    $fecha = $fecha->format("$formato_fecha H:i");
                    $item->put('fecha_vencimiento', $fecha);

                }
                $lista_final->push($item);
            }

        }
        return $lista_final;
    }


    // operación para renovar al día siguiente el cufd del determinado punto de venta, ya que la hora es mayor a las 23:30 
    public function vigenciaRenovarCUFD($biller_id)
    {
        $data_biller = Biller::where('id', $biller_id)->first();
        $data_p_venta = SiatPuntoVenta::where('codigo_punto_venta', $data_biller->punto_venta_siat)->first();
        $registro = SiatCufd::where('sucursal', $data_p_venta->sucursal)->where('codigo_punto_venta', $data_p_venta->codigo_punto_venta)->where('estado', true)->get()->each->updateEstado();

        $bandera = false;
        try {
            Log::info('Renovando CUFD Manualmente desde Ajustes');
            $this->renovarVigenciaxPuntoVenta($data_p_venta);
            return $bandera = true;
        } catch (\Throwable $th) {
            return $bandera;
        }
    }

    // operación para renovar al día siguiente el cufd del determinado punto de venta, ya que la hora es mayor a las 23:30 
    public function vigenciaRenovarCUFDPuntoVenta($id)
    {
        $data_p_venta = SiatPuntoVenta::find($id);
        $registro = SiatCufd::where('sucursal', $data_p_venta->sucursal)->where('codigo_punto_venta', $data_p_venta->codigo_punto_venta)->where('estado', true)->get()->each->updateEstado();

        $bandera = false;
        try {
            Log::info('Renovando CUFD Manualmente desde Ajustes');
            $this->renovarVigenciaxPuntoVenta($data_p_venta);
            return $bandera = true;
        } catch (\Throwable $th) {
            return $bandera;
        }
    }


    static function db()
    {
        try {
            $db = DB::connection()->getPdo();
        } catch (PDOException $e) {
            self::fatal(
                "An error occurred while connecting to the database. " .
                "The error reported by the server was: " . $e->getMessage()
            );
        }
        return $db;
    }
}