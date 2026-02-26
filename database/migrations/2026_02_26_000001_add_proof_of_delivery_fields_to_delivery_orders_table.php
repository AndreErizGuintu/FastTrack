<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('delivery_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('delivery_orders', 'pod_image_path')) {
                $table->string('pod_image_path')->nullable();
            }

            if (!Schema::hasColumn('delivery_orders', 'pod_image_mime')) {
                $table->string('pod_image_mime', 100)->nullable();
            }

            if (!Schema::hasColumn('delivery_orders', 'pod_image_size')) {
                $table->unsignedBigInteger('pod_image_size')->nullable();
            }

            if (!Schema::hasColumn('delivery_orders', 'pod_uploaded_at')) {
                $table->timestamp('pod_uploaded_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->dropColumn([
                'pod_image_path',
                'pod_image_mime',
                'pod_image_size',
                'pod_uploaded_at',
            ]);
        });
    }
};
