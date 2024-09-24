<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusToAttendancesTable extends Migration
{
    /**
     * Tambahkan kolom status ke tabel attendances.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->string('status')->default('start'); // Tambahkan kolom status dengan default 'start'
        });
    }

    /**
     * Hapus kolom status dari tabel attendances.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
}

