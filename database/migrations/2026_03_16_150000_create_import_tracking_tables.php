<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImportTrackingTables extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('import_jobs')) {
            Schema::create('import_jobs', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('company_id');
                $table->unsignedInteger('user_id')->nullable();
                $table->string('source_name');
                $table->string('source_path');
                $table->string('status')->default('queued');
                $table->unsignedInteger('total_tables')->default(0);
                $table->unsignedInteger('processed_tables')->default(0);
                $table->unsignedBigInteger('total_rows')->default(0);
                $table->unsignedBigInteger('processed_rows')->default(0);
                $table->unsignedBigInteger('failed_rows')->default(0);
                $table->unsignedInteger('retries')->default(0);
                $table->unsignedInteger('max_retries')->default(3);
                $table->timestamp('started_at')->nullable();
                $table->timestamp('finished_at')->nullable();
                $table->longText('last_error')->nullable();
                $table->json('root_tables')->nullable();
                $table->json('migration_order')->nullable();
                $table->json('options')->nullable();
                $table->timestamps();

                $table->index(['company_id', 'status']);
            });
        }

        if (!Schema::hasTable('import_job_details')) {
                Schema::create('import_job_details', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('import_job_id');
                $table->unsignedBigInteger('company_id');
                $table->string('table_name');
                $table->unsignedInteger('sort_order')->default(0);
                $table->string('status')->default('queued');
                $table->unsignedBigInteger('total_rows')->default(0);
                $table->unsignedBigInteger('processed_rows')->default(0);
                $table->unsignedBigInteger('failed_rows')->default(0);
                $table->unsignedBigInteger('deferred_rows')->default(0);
                $table->unsignedInteger('retries')->default(0);
                $table->timestamp('started_at')->nullable();
                $table->timestamp('finished_at')->nullable();
                $table->longText('error_message')->nullable();
                $table->json('meta')->nullable();
                $table->timestamps();

                $table->foreign('import_job_id')->references('id')->on('import_jobs')->onDelete('cascade');
                $table->index(['import_job_id', 'sort_order']);
                $table->unique(['import_job_id', 'table_name']);
            });
        }

        if (!Schema::hasTable('import_job_logs')) {
                Schema::create('import_job_logs', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('import_job_id');
                $table->unsignedBigInteger('import_job_detail_id')->nullable();
                $table->string('level', 20)->default('info');
                $table->text('message');
                $table->json('context')->nullable();
                $table->timestamps();

                $table->foreign('import_job_id')->references('id')->on('import_jobs')->onDelete('cascade');
                $table->foreign('import_job_detail_id')->references('id')->on('import_job_details')->onDelete('set null');
                $table->index(['import_job_id', 'id']);
            });
        }

        if (!Schema::hasTable('migration_map')) {
                Schema::create('migration_map', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('import_job_id')->nullable();
                $table->unsignedBigInteger('company_id');
                $table->string('table_name');
                $table->string('old_id');
                $table->unsignedBigInteger('new_id')->nullable();
                $table->json('source_payload')->nullable();
                $table->timestamps();

                $table->foreign('import_job_id')->references('id')->on('import_jobs')->onDelete('set null');
                $table->index(['company_id', 'table_name', 'old_id']);
                $table->unique(['import_job_id', 'company_id', 'table_name', 'old_id'], 'migration_map_job_company_table_old_unique');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('migration_map');
        Schema::dropIfExists('import_job_logs');
        Schema::dropIfExists('import_job_details');
        Schema::dropIfExists('import_jobs');
    }
}