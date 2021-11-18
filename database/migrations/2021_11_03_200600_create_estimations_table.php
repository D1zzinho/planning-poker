<?php

use App\Models\Estimation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEstimationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('estimations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')
                ->constrained('games')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->string('task', 30);
            $table->unsignedFloat('original_result', 3, 1)->nullable();
            $table->unsignedSmallInteger('points')->nullable();
            $table->enum('status', Estimation::getAvailableEstimationStatuses())
                ->default(Estimation::getEstimationStatus(0));
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('estimations');
    }
}
