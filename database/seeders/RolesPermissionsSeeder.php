<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesPermissionsSeeder extends Seeder
{
    public function run()
    {
        try {
            if (!\Schema::hasTable('permissions') || !\Schema::hasTable('roles') || !\Schema::hasTable('role_has_permissions')) {
                return;
            }

            // Use insertOrIgnore to avoid duplicate errors - will skip existing records
            DB::table('permissions')->insertOrIgnore([
                    ['id'=>4, 'name'=>'products-edit', 'guard_name'=>'web', 'created_at'=>'2018-06-03 01:00:09', 'updated_at'=>'2018-06-03 01:00:09'],
                    ['id'=>5, 'name'=>'products-delete', 'guard_name'=>'web', 'created_at'=>'2018-06-03 22:54:22', 'updated_at'=>'2018-06-03 22:54:22'],
                    ['id'=>6, 'name'=>'products-add', 'guard_name'=>'web', 'created_at'=>'2018-06-04 00:34:14', 'updated_at'=>'2018-06-04 00:34:14'],
                    ['id'=>7, 'name'=>'products-index', 'guard_name'=>'web', 'created_at'=>'2018-06-04 03:34:27', 'updated_at'=>'2018-06-04 03:34:27'],
                    ['id'=>8, 'name'=>'purchases-index', 'guard_name'=>'web', 'created_at'=>'2018-06-04 08:03:19', 'updated_at'=>'2018-06-04 08:03:19'],
                    ['id'=>9, 'name'=>'purchases-add', 'guard_name'=>'web', 'created_at'=>'2018-06-04 08:12:25', 'updated_at'=>'2018-06-04 08:12:25'],
                    ['id'=>10, 'name'=>'purchases-edit', 'guard_name'=>'web', 'created_at'=>'2018-06-04 09:47:36', 'updated_at'=>'2018-06-04 09:47:36'],
                    ['id'=>11, 'name'=>'purchases-delete', 'guard_name'=>'web', 'created_at'=>'2018-06-04 09:47:36', 'updated_at'=>'2018-06-04 09:47:36'],
                    ['id'=>12, 'name'=>'sales-index', 'guard_name'=>'web', 'created_at'=>'2018-06-04 10:49:08', 'updated_at'=>'2018-06-04 10:49:08'],
                    ['id'=>13, 'name'=>'sales-add', 'guard_name'=>'web', 'created_at'=>'2018-06-04 10:49:52', 'updated_at'=>'2018-06-04 10:49:52'],
                    ['id'=>14, 'name'=>'sales-edit', 'guard_name'=>'web', 'created_at'=>'2018-06-04 10:49:52', 'updated_at'=>'2018-06-04 10:49:52'],
                    ['id'=>15, 'name'=>'sales-delete', 'guard_name'=>'web', 'created_at'=>'2018-06-04 10:49:53', 'updated_at'=>'2018-06-04 10:49:53'],
                    ['id'=>16, 'name'=>'quotes-index', 'guard_name'=>'web', 'created_at'=>'2018-06-04 22:05:10', 'updated_at'=>'2018-06-04 22:05:10'],
                    ['id'=>17, 'name'=>'quotes-add', 'guard_name'=>'web', 'created_at'=>'2018-06-04 22:05:10', 'updated_at'=>'2018-06-04 22:05:10'],
                    ['id'=>18, 'name'=>'quotes-edit', 'guard_name'=>'web', 'created_at'=>'2018-06-04 22:05:10', 'updated_at'=>'2018-06-04 22:05:10'],
                    ['id'=>19, 'name'=>'quotes-delete', 'guard_name'=>'web', 'created_at'=>'2018-06-04 22:05:10', 'updated_at'=>'2018-06-04 22:05:10'],
                    ['id'=>20, 'name'=>'transfers-index', 'guard_name'=>'web', 'created_at'=>'2018-06-04 22:30:03', 'updated_at'=>'2018-06-04 22:30:03'],
                    ['id'=>21, 'name'=>'transfers-add', 'guard_name'=>'web', 'created_at'=>'2018-06-04 22:30:03', 'updated_at'=>'2018-06-04 22:30:03'],
                    ['id'=>22, 'name'=>'transfers-edit', 'guard_name'=>'web', 'created_at'=>'2018-06-04 22:30:03', 'updated_at'=>'2018-06-04 22:30:03'],
                    ['id'=>23, 'name'=>'transfers-delete', 'guard_name'=>'web', 'created_at'=>'2018-06-04 22:30:03', 'updated_at'=>'2018-06-04 22:30:03'],
                    ['id'=>24, 'name'=>'returns-index', 'guard_name'=>'web', 'created_at'=>'2018-06-04 22:50:24', 'updated_at'=>'2018-06-04 22:50:24'],
                    ['id'=>25, 'name'=>'returns-add', 'guard_name'=>'web', 'created_at'=>'2018-06-04 22:50:24', 'updated_at'=>'2018-06-04 22:50:24'],
                    ['id'=>26, 'name'=>'returns-edit', 'guard_name'=>'web', 'created_at'=>'2018-06-04 22:50:25', 'updated_at'=>'2018-06-04 22:50:25'],
                    ['id'=>27, 'name'=>'returns-delete', 'guard_name'=>'web', 'created_at'=>'2018-06-04 22:50:25', 'updated_at'=>'2018-06-04 22:50:25'],
                    ['id'=>28, 'name'=>'customers-index', 'guard_name'=>'web', 'created_at'=>'2018-06-04 23:15:54', 'updated_at'=>'2018-06-04 23:15:54'],
                    ['id'=>29, 'name'=>'customers-add', 'guard_name'=>'web', 'created_at'=>'2018-06-04 23:15:55', 'updated_at'=>'2018-06-04 23:15:55'],
                    ['id'=>30, 'name'=>'customers-edit', 'guard_name'=>'web', 'created_at'=>'2018-06-04 23:15:55', 'updated_at'=>'2018-06-04 23:15:55'],
                    ['id'=>31, 'name'=>'customers-delete', 'guard_name'=>'web', 'created_at'=>'2018-06-04 23:15:55', 'updated_at'=>'2018-06-04 23:15:55'],
                    ['id'=>32, 'name'=>'suppliers-index', 'guard_name'=>'web', 'created_at'=>'2018-06-04 23:40:12', 'updated_at'=>'2018-06-04 23:40:12'],
                    ['id'=>33, 'name'=>'suppliers-add', 'guard_name'=>'web', 'created_at'=>'2018-06-04 23:40:12', 'updated_at'=>'2018-06-04 23:40:12'],
                    ['id'=>34, 'name'=>'suppliers-edit', 'guard_name'=>'web', 'created_at'=>'2018-06-04 23:40:12', 'updated_at'=>'2018-06-04 23:40:12'],
                    ['id'=>35, 'name'=>'suppliers-delete', 'guard_name'=>'web', 'created_at'=>'2018-06-04 23:40:12', 'updated_at'=>'2018-06-04 23:40:12'],
                    ['id'=>36, 'name'=>'product-report', 'guard_name'=>'web', 'created_at'=>'2018-06-24 23:05:33', 'updated_at'=>'2018-06-24 23:05:33'],
                    ['id'=>37, 'name'=>'purchase-report', 'guard_name'=>'web', 'created_at'=>'2018-06-24 23:24:56', 'updated_at'=>'2018-06-24 23:24:56'],
                    ['id'=>38, 'name'=>'sale-report', 'guard_name'=>'web', 'created_at'=>'2018-06-24 23:33:13', 'updated_at'=>'2018-06-24 23:33:13'],
                    ['id'=>39, 'name'=>'customer-report', 'guard_name'=>'web', 'created_at'=>'2018-06-24 23:36:51', 'updated_at'=>'2018-06-24 23:36:51'],
                    ['id'=>40, 'name'=>'due-report', 'guard_name'=>'web', 'created_at'=>'2018-06-24 23:39:52', 'updated_at'=>'2018-06-24 23:39:52'],
                    ['id'=>41, 'name'=>'users-index', 'guard_name'=>'web', 'created_at'=>'2018-06-25 00:00:10', 'updated_at'=>'2018-06-25 00:00:10'],
                    ['id'=>42, 'name'=>'users-add', 'guard_name'=>'web', 'created_at'=>'2018-06-25 00:00:10', 'updated_at'=>'2018-06-25 00:00:10'],
                    ['id'=>43, 'name'=>'users-edit', 'guard_name'=>'web', 'created_at'=>'2018-06-25 00:01:30', 'updated_at'=>'2018-06-25 00:01:30'],
                    ['id'=>44, 'name'=>'users-delete', 'guard_name'=>'web', 'created_at'=>'2018-06-25 00:01:30', 'updated_at'=>'2018-06-25 00:01:30'],
                    ['id'=>45, 'name'=>'profit-loss', 'guard_name'=>'web', 'created_at'=>'2018-07-14 21:50:05', 'updated_at'=>'2018-07-14 21:50:05'],
                    ['id'=>46, 'name'=>'best-seller', 'guard_name'=>'web', 'created_at'=>'2018-07-14 22:01:38', 'updated_at'=>'2018-07-14 22:01:38'],
                    ['id'=>47, 'name'=>'daily-sale', 'guard_name'=>'web', 'created_at'=>'2018-07-14 22:24:21', 'updated_at'=>'2018-07-14 22:24:21'],
                    ['id'=>48, 'name'=>'monthly-sale', 'guard_name'=>'web', 'created_at'=>'2018-07-14 22:30:41', 'updated_at'=>'2018-07-14 22:30:41'],
                    ['id'=>49, 'name'=>'daily-purchase', 'guard_name'=>'web', 'created_at'=>'2018-07-14 22:36:46', 'updated_at'=>'2018-07-14 22:36:46'],
                    ['id'=>50, 'name'=>'monthly-purchase', 'guard_name'=>'web', 'created_at'=>'2018-07-14 22:48:17', 'updated_at'=>'2018-07-14 22:48:17'],
                    ['id'=>51, 'name'=>'payment-report', 'guard_name'=>'web', 'created_at'=>'2018-07-14 23:10:41', 'updated_at'=>'2018-07-14 23:10:41'],
                    ['id'=>52, 'name'=>'warehouse-stock-report', 'guard_name'=>'web', 'created_at'=>'2018-07-14 23:16:55', 'updated_at'=>'2018-07-14 23:16:55'],
                    ['id'=>53, 'name'=>'product-qty-alert', 'guard_name'=>'web', 'created_at'=>'2018-07-14 23:33:21', 'updated_at'=>'2018-07-14 23:33:21'],
                    ['id'=>54, 'name'=>'supplier-report', 'guard_name'=>'web', 'created_at'=>'2018-07-30 03:00:01', 'updated_at'=>'2018-07-30 03:00:01'],
                    ['id'=>55, 'name'=>'expenses-index', 'guard_name'=>'web', 'created_at'=>'2018-09-05 01:07:10', 'updated_at'=>'2018-09-05 01:07:10'],
                    ['id'=>56, 'name'=>'expenses-add', 'guard_name'=>'web', 'created_at'=>'2018-09-05 01:07:10', 'updated_at'=>'2018-09-05 01:07:10'],
                    ['id'=>57, 'name'=>'expenses-edit', 'guard_name'=>'web', 'created_at'=>'2018-09-05 01:07:10', 'updated_at'=>'2018-09-05 01:07:10'],
                    ['id'=>58, 'name'=>'expenses-delete', 'guard_name'=>'web', 'created_at'=>'2018-09-05 01:07:11', 'updated_at'=>'2018-09-05 01:07:11'],
                    ['id'=>59, 'name'=>'general_setting', 'guard_name'=>'web', 'created_at'=>'2018-10-19 23:10:04', 'updated_at'=>'2018-10-19 23:10:04'],
                    ['id'=>60, 'name'=>'mail_setting', 'guard_name'=>'web', 'created_at'=>'2018-10-19 23:10:04', 'updated_at'=>'2018-10-19 23:10:04'],
                    ['id'=>61, 'name'=>'pos_setting', 'guard_name'=>'web', 'created_at'=>'2018-10-19 23:10:04', 'updated_at'=>'2018-10-19 23:10:04'],
                    ['id'=>62, 'name'=>'hrm_setting', 'guard_name'=>'web', 'created_at'=>'2019-01-02 10:30:23', 'updated_at'=>'2019-01-02 10:30:23'],
                    ['id'=>63, 'name'=>'purchase-return-index', 'guard_name'=>'web', 'created_at'=>'2019-01-02 21:45:14', 'updated_at'=>'2019-01-02 21:45:14'],
                    ['id'=>64, 'name'=>'purchase-return-add', 'guard_name'=>'web', 'created_at'=>'2019-01-02 21:45:14', 'updated_at'=>'2019-01-02 21:45:14'],
                    ['id'=>65, 'name'=>'purchase-return-edit', 'guard_name'=>'web', 'created_at'=>'2019-01-02 21:45:14', 'updated_at'=>'2019-01-02 21:45:14'],
                    ['id'=>66, 'name'=>'purchase-return-delete', 'guard_name'=>'web', 'created_at'=>'2019-01-02 21:45:14', 'updated_at'=>'2019-01-02 21:45:14'],
                    ['id'=>67, 'name'=>'account-index', 'guard_name'=>'web', 'created_at'=>'2019-01-02 22:06:13', 'updated_at'=>'2019-01-02 22:06:13'],
                    ['id'=>68, 'name'=>'balance-sheet', 'guard_name'=>'web', 'created_at'=>'2019-01-02 22:06:14', 'updated_at'=>'2019-01-02 22:06:14'],
                    ['id'=>69, 'name'=>'account-statement', 'guard_name'=>'web', 'created_at'=>'2019-01-02 22:06:14', 'updated_at'=>'2019-01-02 22:06:14'],
                    ['id'=>70, 'name'=>'department', 'guard_name'=>'web', 'created_at'=>'2019-01-02 22:30:01', 'updated_at'=>'2019-01-02 22:30:01'],
                    ['id'=>71, 'name'=>'attendance', 'guard_name'=>'web', 'created_at'=>'2019-01-02 22:30:01', 'updated_at'=>'2019-01-02 22:30:01'],
                    ['id'=>72, 'name'=>'payroll', 'guard_name'=>'web', 'created_at'=>'2019-01-02 22:30:01', 'updated_at'=>'2019-01-02 22:30:01'],
                    ['id'=>73, 'name'=>'employees-index', 'guard_name'=>'web', 'created_at'=>'2019-01-02 22:52:19', 'updated_at'=>'2019-01-02 22:52:19'],
                    ['id'=>74, 'name'=>'employees-add', 'guard_name'=>'web', 'created_at'=>'2019-01-02 22:52:19', 'updated_at'=>'2019-01-02 22:52:19'],
                    ['id'=>75, 'name'=>'employees-edit', 'guard_name'=>'web', 'created_at'=>'2019-01-02 22:52:19', 'updated_at'=>'2019-01-02 22:52:19'],
                    ['id'=>76, 'name'=>'employees-delete', 'guard_name'=>'web', 'created_at'=>'2019-01-02 22:52:19', 'updated_at'=>'2019-01-02 22:52:19'],
                    ['id'=>77, 'name'=>'user-report', 'guard_name'=>'web', 'created_at'=>'2019-01-16 06:48:18', 'updated_at'=>'2019-01-16 06:48:18'],
                    ['id'=>78, 'name'=>'stock_count', 'guard_name'=>'web', 'created_at'=>'2019-02-17 10:32:01', 'updated_at'=>'2019-02-17 10:32:01'],
                    ['id'=>79, 'name'=>'adjustment', 'guard_name'=>'web', 'created_at'=>'2019-02-17 10:32:02', 'updated_at'=>'2019-02-17 10:32:02'],
                    ['id'=>80, 'name'=>'sms_setting', 'guard_name'=>'web', 'created_at'=>'2019-02-22 05:18:03', 'updated_at'=>'2019-02-22 05:18:03'],
                    ['id'=>81, 'name'=>'create_sms', 'guard_name'=>'web', 'created_at'=>'2019-02-22 05:18:03', 'updated_at'=>'2019-02-22 05:18:03'],
                    ['id'=>82, 'name'=>'print_barcode', 'guard_name'=>'web', 'created_at'=>'2019-03-07 05:02:19', 'updated_at'=>'2019-03-07 05:02:19'],
                    ['id'=>83, 'name'=>'empty_database', 'guard_name'=>'web', 'created_at'=>'2019-03-07 05:02:19', 'updated_at'=>'2019-03-07 05:02:19'],
                    ['id'=>84, 'name'=>'customer_group', 'guard_name'=>'web', 'created_at'=>'2019-03-07 05:37:15', 'updated_at'=>'2019-03-07 05:37:15'],
                    ['id'=>85, 'name'=>'unit', 'guard_name'=>'web', 'created_at'=>'2019-03-07 05:37:15', 'updated_at'=>'2019-03-07 05:37:15'],
                    ['id'=>86, 'name'=>'tax', 'guard_name'=>'web', 'created_at'=>'2019-03-07 05:37:15', 'updated_at'=>'2019-03-07 05:37:15'],
                    ['id'=>87, 'name'=>'gift_card', 'guard_name'=>'web', 'created_at'=>'2019-03-07 06:29:38', 'updated_at'=>'2019-03-07 06:29:38'],
                    ['id'=>88, 'name'=>'coupon', 'guard_name'=>'web', 'created_at'=>'2019-03-07 06:29:38', 'updated_at'=>'2019-03-07 06:29:38'],
                    ['id'=>89, 'name'=>'holiday', 'guard_name'=>'web', 'created_at'=>'2019-10-19 08:57:15', 'updated_at'=>'2019-10-19 08:57:15'],
                    ['id'=>90, 'name'=>'warehouse-report', 'guard_name'=>'web', 'created_at'=>'2019-10-22 06:00:23', 'updated_at'=>'2019-10-22 06:00:23'],
                    ['id'=>91, 'name'=>'warehouse', 'guard_name'=>'web', 'created_at'=>'2020-02-26 06:47:32', 'updated_at'=>'2020-02-26 06:47:32'],
                    ['id'=>92, 'name'=>'brand', 'guard_name'=>'web', 'created_at'=>'2020-02-26 06:59:59', 'updated_at'=>'2020-02-26 06:59:59'],
                    ['id'=>93, 'name'=>'billers-index', 'guard_name'=>'web', 'created_at'=>'2020-02-26 07:11:15', 'updated_at'=>'2020-02-26 07:11:15'],
                    ['id'=>94, 'name'=>'billers-add', 'guard_name'=>'web', 'created_at'=>'2020-02-26 07:11:15', 'updated_at'=>'2020-02-26 07:11:15'],
                    ['id'=>95, 'name'=>'billers-edit', 'guard_name'=>'web', 'created_at'=>'2020-02-26 07:11:15', 'updated_at'=>'2020-02-26 07:11:15'],
                    ['id'=>96, 'name'=>'billers-delete', 'guard_name'=>'web', 'created_at'=>'2020-02-26 07:11:15', 'updated_at'=>'2020-02-26 07:11:15'],
                    ['id'=>97, 'name'=>'money-transfer', 'guard_name'=>'web', 'created_at'=>'2020-03-02 05:41:48', 'updated_at'=>'2020-03-02 05:41:48'],
                    ['id'=>98, 'name'=>'category', 'guard_name'=>'web', 'created_at'=>'2020-07-13 12:13:16', 'updated_at'=>'2020-07-13 12:13:16'],
                    ['id'=>99, 'name'=>'delivery', 'guard_name'=>'web', 'created_at'=>'2020-07-13 12:13:16', 'updated_at'=>'2020-07-13 12:13:16'],
                    ['id'=>101, 'name'=>'module_qr', 'guard_name'=>'web', 'created_at'=>'2021-07-09 03:17:47', 'updated_at'=>'2021-07-09 03:17:47'],
                    ['id'=>102, 'name'=>'salebiller-report', 'guard_name'=>'web', 'created_at'=>'2021-12-03 22:42:19', 'updated_at'=>'0000-00-00 00:00:00'],
                    ['id'=>103, 'name'=>'salecustomer-report', 'guard_name'=>'web', 'created_at'=>'2021-12-03 22:42:30', 'updated_at'=>'2021-12-28 21:05:29'],
                    ['id'=>104, 'name'=>'adjustment-account-index', 'guard_name'=>'web', 'created_at'=>'2021-12-28 20:49:46', 'updated_at'=>'2021-12-28 21:05:35'],
                    ['id'=>105, 'name'=>'module_siat', 'guard_name'=>'web', 'created_at'=>'2022-07-07 02:09:57', 'updated_at'=>'0000-00-00 00:00:00'],
                    ['id'=>106, 'name'=>'attentionshift', 'guard_name'=>'web', 'created_at'=>'2022-07-29 03:07:50', 'updated_at'=>'0000-00-00 00:00:00'],
                    ['id'=>107, 'name'=>'presale-index', 'guard_name'=>'web', 'created_at'=>'2022-08-03 08:48:56', 'updated_at'=>'0000-00-00 00:00:00'],
                    ['id'=>108, 'name'=>'presale-edit', 'guard_name'=>'web', 'created_at'=>'2022-08-03 08:48:56', 'updated_at'=>'0000-00-00 00:00:00'],
                    ['id'=>109, 'name'=>'presale-index', 'guard_name'=>'web', 'created_at'=>'2022-08-06 00:38:01', 'updated_at'=>'0000-00-00 00:00:00'],
                    ['id'=>110, 'name'=>'presale-edit', 'guard_name'=>'web', 'created_at'=>'2022-08-06 00:33:30', 'updated_at'=>'0000-00-00 00:00:00'],
                    ['id'=>111, 'name'=>'presale-delete', 'guard_name'=>'web', 'created_at'=>'2022-08-06 00:33:30', 'updated_at'=>'0000-00-00 00:00:00'],
                    ['id'=>112, 'name'=>'presale-create', 'guard_name'=>'web', 'created_at'=>'2022-08-06 00:33:30', 'updated_at'=>'0000-00-00 00:00:00'],
                    ['id'=>113, 'name'=>'only-commision-report', 'guard_name'=>'web', 'created_at'=>'2022-08-13 01:10:49', 'updated_at'=>'0000-00-00 00:00:00'],
                    ['id'=>114, 'name'=>'service-commission-report', 'guard_name'=>'web', 'created_at'=>'2022-08-13 01:10:49', 'updated_at'=>'0000-00-00 00:00:00'],
                    ['id'=>115, 'name'=>'panel_siat', 'guard_name'=>'web', 'created_at'=>'2022-09-28 15:47:50', 'updated_at'=>'0000-00-00 00:00:00'],
                    ['id'=>116, 'name'=>'sucursal_siat', 'guard_name'=>'web', 'created_at'=>'2022-09-28 15:47:50', 'updated_at'=>'0000-00-00 00:00:00'],
                    ['id'=>117, 'name'=>'urlws_siat', 'guard_name'=>'web', 'created_at'=>'2022-09-28 15:47:50', 'updated_at'=>'0000-00-00 00:00:00'],
                    ['id'=>118, 'name'=>'authfact_siat', 'guard_name'=>'web', 'created_at'=>'2022-09-28 15:47:50', 'updated_at'=>'0000-00-00 00:00:00'],
                    ['id'=>119, 'name'=>'puntoventa_siat', 'guard_name'=>'web', 'created_at'=>'2022-09-28 15:47:50', 'updated_at'=>'0000-00-00 00:00:00'],
                    ['id'=>120, 'name'=>'contingencia_siat', 'guard_name'=>'web', 'created_at'=>'2022-09-28 16:00:10', 'updated_at'=>'0000-00-00 00:00:00'],
                    ['id'=>121, 'name'=>'hrm-menu', 'guard_name'=>'web', 'created_at'=>'2023-03-30 00:56:26', 'updated_at'=>'2023-03-30 00:56:26'],
                    ['id'=>122, 'name'=>'facturamasiva_siat', 'guard_name'=>'web', 'created_at'=>'2023-03-30 17:18:55', 'updated_at'=>'2023-03-30 17:18:55'],
                    ['id'=>123, 'name'=>'notadebcred_siat', 'guard_name'=>'web', 'created_at'=>'2023-03-30 17:18:55', 'updated_at'=>'2023-03-30 17:18:55'],
                    ['id'=>124, 'name'=>'sale_pendingdue', 'guard_name'=>'web', 'created_at'=>'2023-03-30 17:25:18', 'updated_at'=>'2023-03-30 17:25:18'],
                    ['id'=>125, 'name'=>'pos_payment_card', 'guard_name'=>'web', 'created_at'=>'2023-03-30 18:07:20', 'updated_at'=>'2023-03-30 18:07:20'],
                    ['id'=>126, 'name'=>'pos_payment_cash', 'guard_name'=>'web', 'created_at'=>'2023-03-30 18:07:21', 'updated_at'=>'2023-03-30 18:07:21'],
                    ['id'=>127, 'name'=>'pos_create_due', 'guard_name'=>'web', 'created_at'=>'2023-03-30 18:07:21', 'updated_at'=>'2023-03-30 18:07:21'],
                    ['id'=>128, 'name'=>'pos_payment_qr', 'guard_name'=>'web', 'created_at'=>'2023-03-30 18:07:21', 'updated_at'=>'2023-03-30 18:07:21'],
                    ['id'=>129, 'name'=>'pos_payment_check', 'guard_name'=>'web', 'created_at'=>'2023-03-30 18:07:22', 'updated_at'=>'2023-03-30 18:07:22'],
                    ['id'=>130, 'name'=>'pos_payment_giftcard', 'guard_name'=>'web', 'created_at'=>'2023-03-30 18:07:22', 'updated_at'=>'2023-03-30 18:07:22'],
                    ['id'=>131, 'name'=>'pos_payment_deposit', 'guard_name'=>'web', 'created_at'=>'2023-03-30 18:07:23', 'updated_at'=>'2023-03-30 18:07:23'],
                    ['id'=>132, 'name'=>'pos_paid_due', 'guard_name'=>'web', 'created_at'=>'2023-03-30 18:07:23', 'updated_at'=>'2023-03-30 18:07:23'],
                    ['id'=>133, 'name'=>'pos_recent_sales', 'guard_name'=>'web', 'created_at'=>'2023-03-30 18:07:23', 'updated_at'=>'2023-03-30 18:07:23'],
                    ['id'=>134, 'name'=>'sales-list-booksale', 'guard_name'=>'web', 'created_at'=>'2023-04-01 18:15:02', 'updated_at'=>'2023-04-01 18:15:02'],
                    ['id'=>135, 'name'=>'lv_arqueogralpdf', 'guard_name'=>'web', 'created_at'=>'2023-06-20 03:02:54', 'updated_at'=>'2023-06-20 03:02:54'],
                    ['id'=>136, 'name'=>'lv_arqueogral_categ', 'guard_name'=>'web', 'created_at'=>'2023-06-20 03:04:25', 'updated_at'=>'2023-06-20 03:04:25'],
                    ['id'=>138, 'name'=>'lv_reportespdf_excel', 'guard_name'=>'web', 'created_at'=>'2023-06-20 03:04:25', 'updated_at'=>'2023-06-20 03:04:25'],
                    ['id'=>139, 'name'=>'lv_facturas_cobradas', 'guard_name'=>'web', 'created_at'=>'2023-06-20 03:04:25', 'updated_at'=>'2023-06-20 03:04:25'],
                    ['id'=>140, 'name'=>'saledetail-report', 'guard_name'=>'web', 'created_at'=>'2023-07-05 00:02:12', 'updated_at'=>'2023-07-05 00:02:12'],
                    ['id'=>141, 'name'=>'lv_facturas_revertidas', 'guard_name'=>'web', 'created_at'=>'2023-07-08 16:04:01', 'updated_at'=>'2023-07-08 16:04:01'],
                    ['id'=>142, 'name'=>'pos_discount_gral', 'guard_name'=>'web', 'created_at'=>'2023-07-14 20:14:17', 'updated_at'=>'2023-07-14 20:14:17'],
                    ['id'=>143, 'name'=>'pos_discount_item', 'guard_name'=>'web', 'created_at'=>'2023-07-14 20:14:17', 'updated_at'=>'2023-07-14 20:14:17'],
                    ['id'=>144, 'name'=>'backup_database', 'guard_name'=>'web', 'created_at'=>'2023-07-14 21:16:01', 'updated_at'=>'2023-07-14 21:16:01'],
                    ['id'=>145, 'name'=>'cafc_siat', 'guard_name'=>'web', 'created_at'=>'2023-07-27 06:14:02', 'updated_at'=>'2023-07-27 06:14:02'],
                    ['id'=>146, 'name'=>'balance-sheet-account', 'guard_name'=>'web', 'created_at'=>'2023-07-27 06:14:02', 'updated_at'=>'2023-07-27 06:14:02'],
                    ['id'=>147, 'name'=>'close-balance-account', 'guard_name'=>'web', 'created_at'=>'2023-08-04 02:12:54', 'updated_at'=>'2023-08-04 02:12:54'],
                    ['id'=>148, 'name'=>'pos_customer_advanced', 'guard_name'=>'web', 'created_at'=>'2023-08-16 16:54:55', 'updated_at'=>'2023-08-16 16:54:55'],
                    ['id'=>149, 'name'=>'product-detail-report', 'guard_name'=>'web', 'created_at'=>'2023-09-13 17:03:23', 'updated_at'=>'2023-09-13 17:03:23'],
                    ['id'=>150, 'name'=>'sale-renueve-report', 'guard_name'=>'web', 'created_at'=>'2023-09-27 05:44:29', 'updated_at'=>'2023-09-27 05:44:29'],
                    ['id'=>151, 'name'=>'attendance-employee-report', 'guard_name'=>'web', 'created_at'=>'2023-11-11 08:03:10', 'updated_at'=>'2023-11-11 08:03:10'],
                    ['id'=>152, 'name'=>'holiday-employee-report', 'guard_name'=>'web', 'created_at'=>'2023-11-11 08:03:10', 'updated_at'=>'2023-11-11 08:03:10'],
                    ['id'=>153, 'name'=>'pos_payment_qrcash', 'guard_name'=>'web', 'created_at'=>'2024-09-19 03:55:43', 'updated_at'=>'2024-09-19 03:55:43'],
                    ['id'=>154, 'name'=>'qty_adjustment-index', 'guard_name'=>'web', 'created_at'=>'2024-10-08 23:39:59', 'updated_at'=>'2024-10-08 23:39:59'],
                    ['id'=>155, 'name'=>'qty_adjustment-add', 'guard_name'=>'web', 'created_at'=>'2024-10-08 23:39:59', 'updated_at'=>'2024-10-08 23:39:59'],
                    ['id'=>156, 'name'=>'qty_adjustment-edit', 'guard_name'=>'web', 'created_at'=>'2024-10-08 23:39:59', 'updated_at'=>'2024-10-08 23:39:59'],
                    ['id'=>157, 'name'=>'qty_adjustment-delete', 'guard_name'=>'web', 'created_at'=>'2024-10-08 23:39:59', 'updated_at'=>'2024-10-08 23:39:59'],
                    ['id'=>158, 'name'=>'accept-transfers', 'guard_name'=>'web', 'created_at'=>'2025-11-07 21:59:01', 'updated_at'=>'2025-11-07 21:59:01'],
                ]);

            // Roles - use insertOrIgnore to skip existing
            DB::table('roles')->insertOrIgnore([
                    ['id'=>1, 'name'=>'Administrador', 'description'=>'El administrador del sistema', 'guard_name'=>'web', 'is_active'=>1, 'created_at'=>'2018-06-01 23:46:44', 'updated_at'=>'2026-01-15 20:10:09', 'blocked_modules'=>'[]'],
                    ['id'=>2, 'name'=>'Tienda', 'description'=>'Propietario de la tienda', 'guard_name'=>'web', 'is_active'=>1, 'created_at'=>'2018-10-22 02:38:13', 'updated_at'=>'2023-07-08 15:52:31', 'blocked_modules'=>null],
                    ['id'=>4, 'name'=>'VentasTienda', 'description'=>'Personal a cargo de la tienda', 'guard_name'=>'web', 'is_active'=>1, 'created_at'=>'2018-06-02 00:05:27', 'updated_at'=>'2025-11-11 20:06:49', 'blocked_modules'=>'["purchases","expenses","quotations","transfers","returns","purchase-returns","settings","pos","people","proforma"]'],
                    ['id'=>5, 'name'=>'Supervisor Comercial', 'description'=>'Supervisor de todas las tiendas', 'guard_name'=>'web', 'is_active'=>1, 'created_at'=>'2023-07-08 14:11:33', 'updated_at'=>'2023-07-08 15:53:21', 'blocked_modules'=>null],
                    ['id'=>6, 'name'=>'RespVentasTienda', 'description'=>'Responsable de la tienda', 'guard_name'=>'web', 'is_active'=>1, 'created_at'=>'2023-07-08 15:59:52', 'updated_at'=>'2023-07-08 15:59:52', 'blocked_modules'=>null],
                ]);

            // role_has_permissions - use insertOrIgnore to skip existing
            $mappings = [
                    [4,1],[4,2],[5,1],[5,2],[6,1],[6,2],[7,1],[7,2],[7,4],[8,1],[8,2],[8,6],[9,1],[9,2],[10,1],[10,2],[10,6],[11,1],[11,2],[12,1],[12,2],[12,4],[12,6],[13,1],[13,2],[13,4],[13,6],[14,1],[14,2],[14,4],[15,1],[15,2],[15,4],[16,1],[16,2],[16,6],[17,1],[17,2],[17,6],[18,1],[18,2],[19,1],[19,2],[20,1],[20,2],[21,1],[21,2],[22,1],[22,2],[23,1],[23,2],[24,1],[24,2],[24,6],[25,1],[25,2],[25,6],[26,1],[26,2],[27,1],[27,2],[28,1],[28,2],[28,4],[28,6],[29,1],[29,2],[29,4],[29,6],[30,1],[30,2],[30,4],[30,6],[31,1],[31,2],[32,1],[32,2],[32,4],[32,6],[33,1],[33,2],[34,1],[34,2],[35,1],[35,2],[36,1],[36,2],[36,4],[37,1],[37,2],[38,1],[38,2],[38,4],[39,1],[39,2],[39,4],[40,1],[40,2],[40,4],[41,1],[42,1],[43,1],[44,1],[45,1],[45,2],[45,4],[46,1],[46,2],[46,4],[47,1],[47,2],[47,4],[47,6],[48,1],[48,2],[48,4],[48,6],[49,1],[49,2],[50,1],[50,2],[51,1],[51,2],[51,4],[52,1],[52,2],[52,4],[52,6],[53,1],[53,2],[53,6],[54,1],[54,2],[55,1],[56,1],[57,1],[58,1],[59,1],[60,1],[61,1],[62,1],[63,1],[63,2],[64,1],[64,2],[65,1],[65,2],[66,1],[66,2],[67,1],[69,1],[69,2],[70,1],[71,1],[72,1],[73,1],[74,1],[75,1],[76,1],[77,1],[77,2],[77,4],[77,6],[78,1],[78,2],[79,1],[79,2],[82,1],[84,1],[84,2],[85,1],[85,2],[86,1],[86,2],[87,1],[87,2],[88,1],[88,2],[89,1],[90,1],[90,6],[91,1],[92,1],[92,2],[93,1],[94,1],[95,1],[96,1],[98,1],[99,1],[102,1],[102,4],[103,1],[103,4],[104,1],[104,2],[106,1],[107,1],[108,1],[111,1],[112,1],[113,1],[113,4],[114,1],[114,4],[116,1],[121,1],[123,1],[125,1],[126,1],[126,6],[127,6],[128,1],[128,6],[131,6],[132,1],[132,6],[133,1],[133,6],[134,1],[134,4],[135,2],[136,2],[138,2],[139,1],[139,4],[141,6],[142,6],[148,1],[148,4],[149,4],[150,1],[150,4],[151,4],[153,1],[154,1],[155,1],[156,1]
                ];

            $insert = [];
            foreach ($mappings as $m) {
                $insert[] = ['permission_id' => $m[0], 'role_id' => $m[1]];
            }
            if (!empty($insert)) {
                DB::table('role_has_permissions')->insertOrIgnore($insert);
            }

        } catch (\Exception $e) {
            // ignore errors during seeding to avoid breaking other seeds
        }
    }
}
