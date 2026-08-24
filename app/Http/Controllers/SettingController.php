<?php

namespace App\Http\Controllers;

use Config;
use DB;
use App\Biller;
use App\Company;

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
use Auth;
use Twilio\Rest\Client;
use Illuminate\Http\Request;
use App\Http\Traits\CufdTrait;
use Clickatell\ClickatellException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

class SettingController extends Controller
{
    use CufdTrait;

    public function emptyDatabase()
    {
        $tables = \DB::select('SHOW TABLES');
        $str = 'Tables_in_' . env('DB_DATABASE');
        foreach ($tables as $table) {
            if ($table->$str != 'accounts' && $table->$str != 'general_settings' && $table->$str != 'hrm_settings' && $table->$str != 'languages' && $table->$str != 'migrations' && $table->$str != 'password_resets' && $table->$str != 'permissions' && $table->$str != 'pos_setting' && $table->$str != 'roles' && $table->$str != 'role_has_permissions' && $table->$str != 'users') {
                \DB::table($table->$str)->truncate();
            }
        }
        return redirect()->back()->with('message', 'Base de Datos Limpiado con éxito');
    }

    public function backupDatabase()
    {
        $lims_general_setting_data = GeneralSetting::current();
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

        $queryTables = \DB::select(\DB::raw('SHOW TABLES'));
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

    public function restoreCompanyData()
    {
        $role = Role::find(Auth::user()->role_id);
        if (!$role || !$role->hasPermissionTo('backup_database')) {
            return redirect()->back()->with('not_permitted', '¡Lo sentimos! No tienes permiso para acceder a este módulo');
        }

        $companyId = null;
        $companies = Company::orderBy('name')->get();
        return view('setting.restore_company_data', compact('companies', 'companyId'));
    }

    public function restoreCompanyDataPreview(Request $request)
    {
        $role = Role::find(Auth::user()->role_id);
        if (!$role || !$role->hasPermissionTo('backup_database')) {
            return redirect()->back()->with('not_permitted', '¡Lo sentimos! No tienes permiso para acceder a este módulo');
        }

        $this->validate($request, [
            'company_id'   => 'required|exists:companies,id',
            'restore_file' => 'required|file|mimes:sql,txt|max:204800',
        ]);

        $companyId = (int) $request->company_id;
        $tempPath = $request->file('restore_file')->store('restore_preview', 'local');

        $sqlContent = file_get_contents(storage_path('app/' . $tempPath));
        $sqlContent = preg_replace('/^\xEF\xBB\xBF/', '', $sqlContent);
        $statements = $this->splitSqlStatements($sqlContent);

        $insertsByTable = [];
        foreach ($statements as $statement) {
            $trimmed = trim($statement);
            if (!preg_match('/^INSERT\s+(?:IGNORE\s+)?INTO\s+`?([a-zA-Z0-9_]+)`?\s*\(([^)]+)\)/i', $trimmed, $matches)) {
                continue;
            }

            $table = $matches[1];
            $columns = array_map(function ($column) {
                return trim(str_replace('`', '', $column));
            }, explode(',', $matches[2]));

            if (!isset($insertsByTable[$table])) {
                $insertsByTable[$table] = ['columns' => $columns, 'row_count' => 0];
            }

            $valuePos = stripos($trimmed, 'VALUES');
            if ($valuePos !== false) {
                $tuples = $this->extractValueTuples(substr($trimmed, $valuePos + 6));
                $insertsByTable[$table]['row_count'] += count($tuples);
            }
        }

        $comparison = [];
        $dbName = config('database.connections.mysql.database');

        foreach ($insertsByTable as $table => $tableInfo) {
            $dbColumns = \DB::select(
                "SELECT COLUMN_NAME, DATA_TYPE, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, COLUMN_KEY, EXTRA
                 FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
                 ORDER BY ORDINAL_POSITION",
                [$dbName, $table]
            );

            $dbColumnNames = [];
            foreach ($dbColumns as $column) {
                $dbColumnNames[] = $column->COLUMN_NAME;
            }

            $colDetails = [];
            foreach ($dbColumns as $column) {
                $inSql = in_array($column->COLUMN_NAME, $tableInfo['columns'], true);
                $isCritical = !$inSql
                    && $column->IS_NULLABLE === 'NO'
                    && $column->COLUMN_DEFAULT === null
                    && $column->EXTRA !== 'auto_increment';

                $colDetails[] = [
                    'name'      => $column->COLUMN_NAME,
                    'type'      => $column->DATA_TYPE,
                    'full_type' => $column->COLUMN_TYPE,
                    'nullable'  => $column->IS_NULLABLE === 'YES',
                    'default'   => $column->COLUMN_DEFAULT,
                    'key'       => $column->COLUMN_KEY,
                    'extra'     => $column->EXTRA,
                    'in_sql'    => $inSql,
                    'critical'  => $isCritical,
                ];
            }

            $extraSqlColumns = array_values(array_diff($tableInfo['columns'], $dbColumnNames));
            $criticalCount = count(array_filter($colDetails, function ($column) {
                return $column['critical'];
            }));

            $comparison[$table] = [
                'exists'         => !empty($dbColumns),
                'row_count'      => $tableInfo['row_count'],
                'sql_cols'       => $tableInfo['columns'],
                'col_details'    => $colDetails,
                'extra_sql_cols' => $extraSqlColumns,
                'critical_count' => $criticalCount,
                'has_company_id' => in_array('company_id', $tableInfo['columns'], true),
            ];
        }

        $companies = Company::orderBy('name')->get();
        return view('setting.restore_company_data', compact('companies', 'comparison', 'companyId', 'tempPath'));
    }

    public function restoreCompanyDataStore(Request $request)
    {
        $role = Role::find(Auth::user()->role_id);
        if (!$role || !$role->hasPermissionTo('backup_database')) {
            return redirect()->back()->with('not_permitted', '¡Lo sentimos! No tienes permiso para acceder a este módulo');
        }

        $this->validate($request, [
            'company_id' => 'required|exists:companies,id',
        ]);

        $companyId = (int) $request->company_id;
        $tempToDelete = null;

        if ($request->hasFile('restore_file')) {
            $this->validate($request, [
                'restore_file' => 'required|file|mimes:sql,txt|max:204800',
            ]);
            $sqlContent = file_get_contents($request->file('restore_file')->getRealPath());
        } elseif ($request->filled('temp_file')) {
            $tempPath = $request->input('temp_file');
            if (!preg_match('/^restore_preview\/[a-zA-Z0-9_\-\.]+$/', $tempPath)) {
                return redirect()->back()->with('not_permitted', 'Ruta de archivo temporal inválida.');
            }

            $fullPath = storage_path('app/' . $tempPath);
            if (!file_exists($fullPath)) {
                return redirect()->back()->with('not_permitted', 'El archivo temporal ha expirado. Analiza el archivo nuevamente.');
            }

            $sqlContent = file_get_contents($fullPath);
            $tempToDelete = $fullPath;
        } else {
            return redirect()->back()->with('not_permitted', 'Debes subir un archivo SQL o analizar primero.');
        }

        $sqlContent = preg_replace('/^\xEF\xBB\xBF/', '', $sqlContent);
        $statements = $this->splitSqlStatements($sqlContent);

        $executed = 0;
        $skipped = 0;
        $failed = 0;
        $errors = [];

        if (\DB::getDriverName() === 'mysql') {
            \DB::statement('SET FOREIGN_KEY_CHECKS=0');
            \DB::statement("SET SESSION sql_mode = ''");
        }

        try {
            foreach ($statements as $statement) {
                $trimmed = trim($statement);
                if ($trimmed === '') {
                    continue;
                }

                if (!preg_match('/^INSERT\s+(?:IGNORE\s+)?INTO\s+/i', $trimmed)) {
                    $skipped++;
                    continue;
                }

                $preparedSql = $this->forceCompanyIdInInsert($trimmed, $companyId);

                try {
                    \DB::unprepared($preparedSql);
                    $executed++;
                } catch (\Throwable $e) {
                    $failed++;
                    $errorMsg = $e->getMessage();
                    if (count($errors) < 10) {
                        preg_match('/INSERT\s+INTO\s+`?([a-zA-Z0-9_]+)`?/i', $preparedSql, $tableMatch);
                        $table = $tableMatch[1] ?? '?';
                        $errors[] = "[{$table}] " . substr($errorMsg, 0, 120);
                    }

                    Log::warning('Restauración: sentencia INSERT omitida', [
                        'company_id' => $companyId,
                        'error'      => $errorMsg,
                    ]);
                }
            }
        } finally {
            if (\DB::getDriverName() === 'mysql') {
                \DB::statement('SET FOREIGN_KEY_CHECKS=1');
            }
            if ($tempToDelete && file_exists($tempToDelete)) {
                @unlink($tempToDelete);
            }
        }

        $message = "Restauración completada. Ejecutadas: {$executed} | Omitidas (no INSERT): {$skipped} | Fallidas: {$failed}.";
        if (!empty($errors)) {
            $message .= ' Primeros errores: ' . implode(' / ', $errors);
        }

        $sessionKey = $failed > 0 ? 'not_permitted' : 'message';
        if ($executed > 0 && $failed > 0) {
            $sessionKey = 'message';
        }

        return redirect()->back()->with($sessionKey, $message);
    }

    private function splitSqlStatements($sql)
    {
        $statements = [];
        $buffer = '';
        $inSingle = false;
        $inDouble = false;
        $length = strlen($sql);

        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];
            $prev = $i > 0 ? $sql[$i - 1] : '';

            if ($char === "'" && !$inDouble && $prev !== '\\\\') {
                $inSingle = !$inSingle;
            } elseif ($char === '"' && !$inSingle && $prev !== '\\\\') {
                $inDouble = !$inDouble;
            }

            if ($char === ';' && !$inSingle && !$inDouble) {
                $statements[] = $buffer;
                $buffer = '';
                continue;
            }

            $buffer .= $char;
        }

        if (trim($buffer) !== '') {
            $statements[] = $buffer;
        }

        return $statements;
    }

    private function forceCompanyIdInInsert($sql, $companyId)
    {
        if (!preg_match('/^INSERT\s+(?:IGNORE\s+)?INTO\s+(`?[a-zA-Z0-9_]+`?(?:\.`?[a-zA-Z0-9_]+`?)?)\s*(?:\((.*?)\))?\s*VALUES\s*(.+)$/is', $sql, $matches)) {
            return $sql;
        }

        $tableNameRaw = $matches[1];
        $columnSection = isset($matches[2]) ? trim((string) $matches[2]) : '';
        $valuesSection = trim($matches[3]);

        // Si viene calificado como db.table, tomar solo table para consultar esquema
        $tableParts = explode('.', str_replace('`', '', $tableNameRaw));
        $tableName = end($tableParts);

        $columns = [];
        if ($columnSection !== '') {
            $columns = array_map(function ($column) {
                return trim(str_replace('`', '', $column));
            }, explode(',', $columnSection));
        } else {
            $dbName = config('database.connections.mysql.database');
            $dbColumns = \DB::select(
                'SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? ORDER BY ORDINAL_POSITION',
                [$dbName, $tableName]
            );
            foreach ($dbColumns as $dbColumn) {
                $columns[] = $dbColumn->COLUMN_NAME;
            }
        }

        if (empty($columns)) {
            return $sql;
        }

        $dbName = config('database.connections.mysql.database');
        static $autoIncrementColumnCache = [];
        if (!isset($autoIncrementColumnCache[$tableName])) {
            $autoIncrementColumnCache[$tableName] = [];
            $autoIncrementColumns = \DB::select(
                'SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND EXTRA LIKE "%auto_increment%"',
                [$dbName, $tableName]
            );
            foreach ($autoIncrementColumns as $autoIncrementColumn) {
                $autoIncrementColumnCache[$tableName][] = strtolower($autoIncrementColumn->COLUMN_NAME);
            }
        }

        $autoIncrementIndexes = [];
        foreach ($columns as $index => $columnName) {
            if (in_array(strtolower($columnName), $autoIncrementColumnCache[$tableName], true)) {
                $autoIncrementIndexes[] = $index;
            }
        }

        // Si el INSERT trae lista de columnas y no incluye company_id, la agregamos
        $companyIndex = false;
        foreach ($columns as $idx => $columnName) {
            if (strtolower($columnName) === 'company_id') {
                $companyIndex = $idx;
                break;
            }
        }

        $shouldAppendCompanyColumn = false;
        if ($companyIndex === false && $columnSection !== '') {
            $columns[] = 'company_id';
            $companyIndex = count($columns) - 1;
            $shouldAppendCompanyColumn = true;
        }

        if (!empty($autoIncrementIndexes)) {
            rsort($autoIncrementIndexes);
            foreach ($autoIncrementIndexes as $indexToRemove) {
                if (isset($columns[$indexToRemove])) {
                    unset($columns[$indexToRemove]);
                }
            }
            $columns = array_values($columns);

            $companyIndex = false;
            foreach ($columns as $idx => $columnName) {
                if (strtolower($columnName) === 'company_id') {
                    $companyIndex = $idx;
                    break;
                }
            }
            $shouldAppendCompanyColumn = false;
        }

        if ($companyIndex === false) {
            return $sql;
        }

        $tuples = $this->extractValueTuples($valuesSection);
        if (empty($tuples)) {
            return $sql;
        }

        $updatedTuples = [];
        foreach ($tuples as $tuple) {
            $values = $this->splitCsvSql($tuple);

            if (!empty($autoIncrementIndexes)) {
                foreach ($autoIncrementIndexes as $indexToRemove) {
                    if (isset($values[$indexToRemove])) {
                        unset($values[$indexToRemove]);
                    }
                }
                $values = array_values($values);
            }

            if ($shouldAppendCompanyColumn) {
                $values[] = (string) $companyId;
            } else {
                if (isset($values[$companyIndex])) {
                    $values[$companyIndex] = (string) $companyId;
                } else {
                    $values[] = (string) $companyId;
                }
            }
            $updatedTuples[] = '(' . implode(',', $values) . ')';
        }

        $insertKeyword = stripos($sql, 'INSERT IGNORE INTO') === 0 ? 'INSERT IGNORE INTO ' : 'INSERT INTO ';
        $newColumnSection = '(' . implode(', ', $columns) . ')';

        return $insertKeyword . $tableNameRaw . ' ' . $newColumnSection . ' VALUES ' . implode(',', $updatedTuples) . ';';
    }

    private function extractValueTuples($valuesSection)
    {
        $tuples = [];
        $buffer = '';
        $depth = 0;
        $inSingle = false;
        $inDouble = false;
        $length = strlen($valuesSection);

        for ($i = 0; $i < $length; $i++) {
            $char = $valuesSection[$i];
            $prev = $i > 0 ? $valuesSection[$i - 1] : '';

            if ($char === "'" && !$inDouble && $prev !== '\\\\') {
                $inSingle = !$inSingle;
            } elseif ($char === '"' && !$inSingle && $prev !== '\\\\') {
                $inDouble = !$inDouble;
            }

            if (!$inSingle && !$inDouble) {
                if ($char === '(') {
                    if ($depth === 0) {
                        $buffer = '';
                    }
                    $depth++;
                    continue;
                }
                if ($char === ')') {
                    $depth--;
                    if ($depth === 0) {
                        $tuples[] = $buffer;
                        $buffer = '';
                        continue;
                    }
                }
            }

            if ($depth > 0) {
                $buffer .= $char;
            }
        }

        return $tuples;
    }

    private function splitCsvSql($tuple)
    {
        $parts = [];
        $buffer = '';
        $inSingle = false;
        $inDouble = false;
        $length = strlen($tuple);

        for ($i = 0; $i < $length; $i++) {
            $char = $tuple[$i];
            $prev = $i > 0 ? $tuple[$i - 1] : '';

            if ($char === "'" && !$inDouble && $prev !== '\\\\') {
                $inSingle = !$inSingle;
            } elseif ($char === '"' && !$inSingle && $prev !== '\\\\') {
                $inDouble = !$inDouble;
            }

            if ($char === ',' && !$inSingle && !$inDouble) {
                $parts[] = trim($buffer);
                $buffer = '';
                continue;
            }

            $buffer .= $char;
        }

        if ($buffer !== '') {
            $parts[] = trim($buffer);
        }

        return $parts;
    }

    public function moduleQr()
    {
        return view('setting.siat_setting');
    }

    public function generalSetting()
    {
        if (Schema::hasColumn('general_settings', 'company_id') && auth()->check()) {
            $companyId = auth()->user()->company_id;
            $lims_general_setting_data = GeneralSetting::firstOrNew(['company_id' => $companyId]);
            $lims_general_setting_data->company_id = $companyId;
        } else {
            $lims_general_setting_data = GeneralSetting::firstOrNew(['id' => 1]);
        }
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

        if (Schema::hasColumn('general_settings', 'company_id') && auth()->check()) {
            $companyId = auth()->user()->company_id;
            $general_setting = GeneralSetting::firstOrNew(['company_id' => $companyId]);
            $general_setting->company_id = $companyId;
        } else {
            $general_setting = GeneralSetting::firstOrNew(['id' => 1]);
        }
        $general_setting->site_title = $data['site_title'];
        $general_setting->currency = $data['currency'];
        $general_setting->currency_position = $data['currency_position'];
        $general_setting->staff_access = $data['staff_access'];
        $general_setting->date_format = $data['date_format'];
        $general_setting->alert_expiration = isset($data['alert_expiration']) && $data['alert_expiration'] !== '' ? (int) $data['alert_expiration'] : 30;
        // Asegurar que el campo `theme` no quede nulo (evita error SQL cuando se crea el registro)
        if (isset($data['theme']) && $data['theme'] !== '') {
            $general_setting->theme = $data['theme'];
        } elseif (empty($general_setting->theme)) {
            $general_setting->theme = 'default.css';
        }
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
        if (Schema::hasColumn('general_settings', 'company_id') && auth()->check()) {
            $companyId = auth()->user()->company_id;
            $lims_general_setting_data = GeneralSetting::firstOrNew(['company_id' => $companyId]);
            $lims_general_setting_data->company_id = $companyId;
        } else {
            $lims_general_setting_data = GeneralSetting::firstOrNew(['id' => 1]);
        }
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
        $companyId = auth()->user()->company_id;
        $lims_hrm_setting_data = HrmSetting::firstOrNew(['company_id' => $companyId]);
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
        $defaultCurrencyCode = \DB::table('siat_parametricas_varios')
            ->where('tipo_clasificador', 'tipoMoneda')
            ->whereRaw('UPPER(descripcion) = ?', ['BOLIVIANO'])
            ->value('codigo_clasificador') ?? '1';
        $companyId = auth()->user()->company_id;
        $lims_pos_setting_data = PosSetting::firstOrNew(['company_id' => $companyId]);
        if (empty($lims_pos_setting_data->tipo_moneda_siat)) {
            $lims_pos_setting_data->tipo_moneda_siat = (string) $defaultCurrencyCode;
        }

        return view('setting.pos_setting', compact('lims_customer_list', 'tipo_emision_list', 'lims_warehouse_list', 'lims_biller_list', 'lims_formatprint_list', 'lims_pos_setting_data', 'lims_printer_list'));
    }

    public function posSettingJSON()
    {
        $companyId = auth()->user()->company_id;
        $lims_pos_setting_data = PosSetting::where('company_id', $companyId)->latest()->first();

        return $lims_pos_setting_data ?? new PosSetting();
    }

    public function posSettingUpdate(Request $request)
    {
        $data = $request->all();
        $companyId = auth()->user()->company_id;
        $defaultCurrencyCode = \DB::table('siat_parametricas_varios')
            ->where('tipo_clasificador', 'tipoMoneda')
            ->whereRaw('UPPER(descripcion) = ?', ['BOLIVIANO'])
            ->value('codigo_clasificador') ?? '1';

        $pos_setting = PosSetting::firstOrCreate(
            ['company_id' => $companyId],
            [
                'customer_id' => 1,
                'warehouse_id' => 1,
                'biller_id' => 1,
                'product_number' => 10,
                'stripe_secret_key' => '',
                'stripe_public_key' => null,
                'cant_decimal' => 2,
                'tipo_moneda_siat' => (string) $defaultCurrencyCode,
            ]
        );
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
        if ($request->has('hora_inicio_atencion') && $request->hora_inicio_atencion) {
            $request->merge([
                'hora_inicio_atencion' => date('H:i', strtotime($request->hora_inicio_atencion)),
            ]);
        }
        if ($request->has('hora_fin_atencion') && $request->hora_fin_atencion) {
            $request->merge([
                'hora_fin_atencion' => date('H:i', strtotime($request->hora_fin_atencion)),
            ]);
        }

        $request->validate([
            'hora_inicio_atencion' => 'nullable|date_format:H:i',
            'hora_fin_atencion' => 'nullable|date_format:H:i',
            'intervalo_reserva_minutos' => 'nullable|integer|min:5|max:120',
        ]);

        $data = $request->all();
        $companyId = auth()->user()->company_id;
        $defaultCurrencyCode = \DB::table('siat_parametricas_varios')
            ->where('tipo_clasificador', 'tipoMoneda')
            ->whereRaw('UPPER(descripcion) = ?', ['BOLIVIANO'])
            ->value('codigo_clasificador') ?? '1';

        $horaInicio = $data['hora_inicio_atencion'] ?? '08:00';
        $horaFin = $data['hora_fin_atencion'] ?? '21:00';
        if ($horaInicio >= $horaFin) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['hora_fin_atencion' => 'La hora fin de atención debe ser mayor a la hora inicio']);
        }

        $pos_setting = PosSetting::firstOrNew(['company_id' => $companyId]);
        $pos_setting->company_id = $companyId;
        $pos_setting->customer_id = $data['customer_id'];
        $pos_setting->warehouse_id = $data['warehouse_id'];
        $pos_setting->type_print = $data['type_print_id'];
        $pos_setting->print_order = $data['type_printorder_id'];
        $pos_setting->biller_id = $data['biller_id'];
        $pos_setting->product_number = $data['product_number'];
        $pos_setting->stripe_public_key = $data['stripe_public_key'] ?? null;
        $pos_setting->stripe_secret_key = $data['stripe_secret_key'] ?? '';
        $pos_setting->t_c = $data['t_c'];
        $pos_setting->facturacion_id = $data['facturacion_id'];
        $pos_setting->codigo_emision = $data['codigo_emision'];
        $pos_setting->tipo_moneda_siat = $data['tipo_moneda_siat'] ?? $defaultCurrencyCode;
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

        $pos_setting->hora_inicio_atencion = $horaInicio;
        $pos_setting->hora_fin_atencion = $horaFin;
        $pos_setting->intervalo_reserva_minutos = $data['intervalo_reserva_minutos'] ?? 30;

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
        Log::info('POS_SETTING runTareaProgramada: request recibido', [
            'user_id' => optional(Auth::user())->id,
            'ip' => $request->ip(),
        ]);
        $this->writeCufdDebug('POS_SETTING runTareaProgramada: request recibido', [
            'user_id' => optional(Auth::user())->id,
            'ip' => $request->ip(),
        ]);
        try {
            // Artisan::call('taskcufd:renovar');
            Artisan::call('schedule:run');
            Log::info('POS_SETTING runTareaProgramada: ejecución completada', ['resultado' => true]);
            $this->writeCufdDebug('POS_SETTING runTareaProgramada: ejecución completada', ['resultado' => true]);
            return $bandera = true;
        } catch (Exception $ex) {
            Log::error('POS_SETTING runTareaProgramada: excepción', ['message' => $ex->getMessage()]);
            $this->writeCufdDebug('POS_SETTING runTareaProgramada: excepción', ['message' => $ex->getMessage()]);
            $request->session()->flash('error', $ex->getMessage());
            return $bandera;
        }

    }

    public function forzarRenovarCUFD()
    {
        Log::info('POS_SETTING forzarRenovarCUFD: request recibido', [
            'user_id' => optional(Auth::user())->id,
            'ip' => request()->ip(),
            'endpoint' => 'run/forzar_renovar_cufd',
        ]);
        $this->writeCufdDebug('POS_SETTING forzarRenovarCUFD: request recibido', [
            'user_id' => optional(Auth::user())->id,
            'ip' => request()->ip(),
            'endpoint' => 'run/forzar_renovar_cufd',
        ]);

        $resultado = $this->forceRenovarCUFD(); // CufdTrait, devuelve boolean

        Log::info('POS_SETTING forzarRenovarCUFD: resultado', ['resultado' => (bool) $resultado]);
        $this->writeCufdDebug('POS_SETTING forzarRenovarCUFD: resultado', ['resultado' => (bool) $resultado]);

        return $resultado;
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
                    $generalSetting = GeneralSetting::current();
                    $formato_fecha = $generalSetting ? $generalSetting->date_format : 'd/m/Y';
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
        Log::info('POS_SETTING vigenciaRenovarCUFD: request recibido', [
            'biller_id' => $biller_id,
            'user_id' => optional(Auth::user())->id,
            'ip' => request()->ip(),
            'endpoint' => 'run/vigencia_renovar_cufd/{biller_id}',
        ]);
        $this->writeCufdDebug('POS_SETTING vigenciaRenovarCUFD: request recibido', [
            'biller_id' => $biller_id,
            'user_id' => optional(Auth::user())->id,
            'ip' => request()->ip(),
            'endpoint' => 'run/vigencia_renovar_cufd/{biller_id}',
        ]);

        $data_biller = Biller::where('id', $biller_id)->first();
        if (!$data_biller) {
            return array('status' => false, 'mensaje' => 'No se encontró el biller: ' . $biller_id);
        }
        $data_p_venta = SiatPuntoVenta::where('codigo_punto_venta', $data_biller->punto_venta_siat)->first();
        if (!$data_p_venta) {
            return array('status' => false, 'mensaje' => 'No se encontró el punto de venta asociado al biller: ' . $biller_id);
        }
        $registro = SiatCufd::where('sucursal', $data_p_venta->sucursal)->where('codigo_punto_venta', $data_p_venta->codigo_punto_venta)->where('estado', true)->get()->each->updateEstado();

        try {
            Log::info('Renovando CUFD Manualmente desde Ajustes');
            $resultado = $this->renovarVigenciaxPuntoVenta($data_p_venta);
            Log::info('POS_SETTING vigenciaRenovarCUFD: resultado', [
                'resultado' => $resultado,
                'sucursal' => optional($data_p_venta)->sucursal,
                'codigo_punto_venta' => optional($data_p_venta)->codigo_punto_venta,
            ]);
            $this->writeCufdDebug('POS_SETTING vigenciaRenovarCUFD: resultado', [
                'resultado' => $resultado,
                'sucursal' => optional($data_p_venta)->sucursal,
                'codigo_punto_venta' => optional($data_p_venta)->codigo_punto_venta,
            ]);
            return $resultado;
        } catch (\Throwable $th) {
            Log::error('POS_SETTING vigenciaRenovarCUFD: excepción', [
                'message' => $th->getMessage(),
                'biller_id' => $biller_id,
            ]);
            $this->writeCufdDebug('POS_SETTING vigenciaRenovarCUFD: excepción', [
                'message' => $th->getMessage(),
                'biller_id' => $biller_id,
            ]);
            return array('status' => false, 'mensaje' => $th->getMessage());
        }
    }

    // operación para renovar al día siguiente el cufd del determinado punto de venta, ya que la hora es mayor a las 23:30 
    public function vigenciaRenovarCUFDPuntoVenta($id)
    {
        Log::info('POS_SETTING vigenciaRenovarCUFDPuntoVenta: request recibido', [
            'siat_punto_venta_id' => $id,
            'user_id' => optional(Auth::user())->id,
            'ip' => request()->ip(),
            'endpoint' => 'setting/vigencia_renovar_cufd_pv/{id}',
        ]);
        $this->writeCufdDebug('POS_SETTING vigenciaRenovarCUFDPuntoVenta: request recibido', [
            'siat_punto_venta_id' => $id,
            'user_id' => optional(Auth::user())->id,
            'ip' => request()->ip(),
            'endpoint' => 'setting/vigencia_renovar_cufd_pv/{id}',
        ]);

        $data_p_venta = SiatPuntoVenta::find($id);
        if (!$data_p_venta) {
            return array('status' => false, 'mensaje' => 'No se encontró el punto de venta: ' . $id);
        }
        $registro = SiatCufd::where('sucursal', $data_p_venta->sucursal)->where('codigo_punto_venta', $data_p_venta->codigo_punto_venta)->where('estado', true)->get()->each->updateEstado();

        try {
            Log::info('Renovando CUFD Manualmente desde Ajustes');
            $resultado = $this->renovarVigenciaxPuntoVenta($data_p_venta);
            Log::info('POS_SETTING vigenciaRenovarCUFDPuntoVenta: resultado', [
                'resultado' => $resultado,
                'sucursal' => optional($data_p_venta)->sucursal,
                'codigo_punto_venta' => optional($data_p_venta)->codigo_punto_venta,
            ]);
            $this->writeCufdDebug('POS_SETTING vigenciaRenovarCUFDPuntoVenta: resultado', [
                'resultado' => $resultado,
                'sucursal' => optional($data_p_venta)->sucursal,
                'codigo_punto_venta' => optional($data_p_venta)->codigo_punto_venta,
            ]);
            return $resultado;
        } catch (\Throwable $th) {
            Log::error('POS_SETTING vigenciaRenovarCUFDPuntoVenta: excepción', [
                'message' => $th->getMessage(),
                'siat_punto_venta_id' => $id,
            ]);
            $this->writeCufdDebug('POS_SETTING vigenciaRenovarCUFDPuntoVenta: excepción', [
                'message' => $th->getMessage(),
                'siat_punto_venta_id' => $id,
            ]);
            return array('status' => false, 'mensaje' => $th->getMessage());
        }
    }


    static function db()
    {
        try {
            $db = \DB::connection()->getPdo();
        } catch (PDOException $e) {
            self::fatal(
                "An error occurred while connecting to the database. " .
                "The error reported by the server was: " . $e->getMessage()
            );
        }
        return $db;
    }
}