<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('affiliates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('merchant_id');
            // TODO: Replace me with a brief explanation of why floats aren't the correct data type, and replace with the correct data type.
            /**
             * when dealing with financial values,
             * using floating-point numbers can lead to accuracy problems because of the way floating-point calculations work.
             * Floating-point numbers can distort certain decimal values, which is a difficult problem in real-world financial calculations.
             *
             * For financial values such as commission_rate,
             * it is recommended to use decimal data types instead of floating point.
             * The decimal type allows you to specify the precision (total number of digits) and scale (number of digits after the decimal point),
             * ensuring accurate representation of decimal values.
             */
            $table->decimal('commission_rate',8,2);
            $table->string('discount_code');
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
        Schema::dropIfExists('affiliates');
    }
};
