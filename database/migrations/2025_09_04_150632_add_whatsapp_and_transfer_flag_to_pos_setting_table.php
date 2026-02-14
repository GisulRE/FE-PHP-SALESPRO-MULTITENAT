<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWhatsappAndTransferFlagToPosSettingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('pos_setting')) {
            // Add columns only if they don't exist. Avoid using ->after() when the referenced column is missing.
            if (!Schema::hasColumn('pos_setting', 'url_whatsapp')) {
                Schema::table('pos_setting', function (Blueprint $table) {
                    try {
                        if (Schema::hasColumn('pos_setting', 'url_cobranza')) {
                            $table->string('url_whatsapp')->nullable()->after('url_cobranza');
                        } else {
                            $table->string('url_whatsapp')->nullable();
                        }
                    } catch (\Exception $e) {
                        // Fallback: add without after() if driver doesn't support it
                        $table->string('url_whatsapp')->nullable();
                    }
                });
            }

            if (!Schema::hasColumn('pos_setting', 'require_transfer_authorization')) {
                Schema::table('pos_setting', function (Blueprint $table) {
                    try {
                        if (Schema::hasColumn('pos_setting', 'url_whatsapp')) {
                            $table->tinyInteger('require_transfer_authorization')
                                ->default(1)
                                ->after('url_whatsapp');
                        } else {
                            $table->tinyInteger('require_transfer_authorization')
                                ->default(1);
                        }
                    } catch (\Exception $e) {
                        $table->tinyInteger('require_transfer_authorization')
                            ->default(1);
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('pos_setting')) {
            Schema::table('pos_setting', function (Blueprint $table) {
                if (Schema::hasColumn('pos_setting', 'require_transfer_authorization')) {
                    $table->dropColumn('require_transfer_authorization');
                }
                if (Schema::hasColumn('pos_setting', 'url_whatsapp')) {
                    $table->dropColumn('url_whatsapp');
                }
            });
        }
    }
}
