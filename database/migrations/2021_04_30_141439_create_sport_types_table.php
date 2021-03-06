<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSportTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sport_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug' , 100)->unique();
            $table->string('provider_slug' , 50)->unique();
            $table->tinyInteger('type')->default(1);
            $table->boolean('active')->default(true);
            $table->unsignedBigInteger('parent_id')->nullable();
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
        Schema::dropIfExists('sport_types');
    }
}
