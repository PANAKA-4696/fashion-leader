<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // 1. ユーザーマスタ
        Schema::create('USER', function (Blueprint $table) {
            $table->char('USER_ID', 8)->primary();
            $table->string('USER_NAME', 50);
            $table->string('MAIL', 254)->unique();
            $table->string('PASSWORD', 255);
            $table->boolean('ROOT')->default(false);
            $table->timestamps();
        });

        // 2. 服マスタ
        Schema::create('WEAR', function (Blueprint $table) {
            $table->char('WEAR_ID', 16)->primary();
            $table->string('ITEM_NAME', 150);
            $table->string('CATEGORY', 50);
            $table->string('IMAGE_PATH', 512)->nullable();
            $table->char('USER_ID', 8);
            $table->foreign('USER_ID')->references('USER_ID')->on('USER')->onDelete('cascade');
            $table->timestamps();
        });

        // 3. コーデマスタ
        Schema::create('CODE', function (Blueprint $table) {
            $table->char('CODE_ID', 16)->primary();
            $table->string('CODE_NAME', 255);
            $table->string('CODE_INFO', 1024)->nullable();
            $table->string('IMAGE_PATH', 512)->nullable();
            $table->char('USER_ID', 8);
            $table->foreign('USER_ID')->references('USER_ID')->on('USER')->onDelete('cascade');
            $table->timestamps();
        });

        // 4. クローゼットマスタ
        Schema::create('CLOSET', function (Blueprint $table) {
            $table->char('CLOSET_ID', 16)->primary();
            $table->string('CLOSET_NAME', 255);
            $table->string('CLOSET_INFO', 1024)->nullable();
            $table->char('USER_ID', 8);
            $table->foreign('USER_ID')->references('USER_ID')->on('USER')->onDelete('cascade');
            $table->timestamps();
        });

        // 5. タグマスタ
        Schema::create('TAG', function (Blueprint $table) {
            $table->char('TAG_ID', 16)->primary();
            $table->string('TAG_NAME', 32);
            $table->char('USER_ID', 8);
            $table->char('CLOSET_ID', 16)->nullable();
            $table->char('WEAR_ID', 16)->nullable();
            $table->char('CODE_ID', 16)->nullable();
            $table->foreign('USER_ID')->references('USER_ID')->on('USER')->onDelete('cascade');
            $table->foreign('CLOSET_ID')->references('CLOSET_ID')->on('CLOSET')->onDelete('cascade');
            $table->foreign('WEAR_ID')->references('WEAR_ID')->on('WEAR')->onDelete('cascade');
            $table->foreign('CODE_ID')->references('CODE_ID')->on('CODE')->onDelete('cascade');
        });

        // --- 以下、中間テーブル・付随情報 ---
        
        Schema::create('FAVORITE', function (Blueprint $table) {
            $table->char('USER_ID', 8);
            $table->char('CLOSET_ID', 16)->nullable();
            $table->char('WEAR_ID', 16)->nullable();
            $table->char('CODE_ID', 16)->nullable();
            $table->unique(['USER_ID', 'CLOSET_ID', 'WEAR_ID', 'CODE_ID'], 'fav_unique');
            $table->foreign('USER_ID')->references('USER_ID')->on('USER')->onDelete('cascade');
        });

        Schema::create('CALENDAR', function (Blueprint $table) {
            $table->char('CALENDAR_ID', 16)->primary();
            $table->integer('CAL_YEAR');
            $table->integer('CAL_MONTH');
            $table->integer('CAL_DATE');
            $table->char('USER_ID', 8);
            $table->unique(['USER_ID', 'CAL_YEAR', 'CAL_MONTH', 'CAL_DATE']);
            $table->foreign('USER_ID')->references('USER_ID')->on('USER')->onDelete('cascade');
        });

        Schema::create('WEAR_CODE', function (Blueprint $table) {
            $table->char('CODE_ID', 16);
            $table->char('WEAR_ID', 16);
            $table->primary(['CODE_ID', 'WEAR_ID']);
            $table->foreign('CODE_ID')->references('CODE_ID')->on('CODE')->onDelete('cascade');
            $table->foreign('WEAR_ID')->references('WEAR_ID')->on('WEAR')->onDelete('cascade');
        });

        Schema::create('CLOSET_WEAR', function (Blueprint $table) {
            $table->char('CLOSET_ID', 16);
            $table->char('WEAR_ID', 16);
            $table->primary(['CLOSET_ID', 'WEAR_ID']);
        });

        Schema::create('CLOSET_CODE', function (Blueprint $table) {
            $table->char('CLOSET_ID', 16);
            $table->char('CODE_ID', 16);
            $table->primary(['CLOSET_ID', 'CODE_ID']);
        });

        Schema::create('TODAY_CODE', function (Blueprint $table) {
            $table->char('CALENDAR_ID', 16);
            $table->char('CODE_ID', 16);
            $table->primary(['CALENDAR_ID', 'CODE_ID']);
            $table->foreign('CALENDAR_ID')->references('CALENDAR_ID')->on('CALENDAR')->onDelete('cascade');
            $table->foreign('CODE_ID')->references('CODE_ID')->on('CODE')->onDelete('cascade');
        });
    }

    public function down(): void {
        // テーブルを削除する際は、外部キー制約の関係で逆順に消す必要があります
        Schema::dropIfExists('TODAY_CODE');
        Schema::dropIfExists('CLOSET_CODE');
        Schema::dropIfExists('CLOSET_WEAR');
        Schema::dropIfExists('WEAR_CODE');
        Schema::dropIfExists('CALENDAR');
        Schema::dropIfExists('FAVORITE');
        Schema::dropIfExists('TAG');
        Schema::dropIfExists('CLOSET');
        Schema::dropIfExists('CODE');
        Schema::dropIfExists('WEAR');
        Schema::dropIfExists('USER');
    }
};