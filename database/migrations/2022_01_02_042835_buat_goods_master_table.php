<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class BuatGoodsMasterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('goods_master', function (Blueprint $table) {
           $table->id(); 
           $table->unsignedBigInteger('id_produk');
           $table->foreign('id_produk')->references('id')->on('produk')->onUpdate('cascade')->onDelete('cascade');
           
           $table->unsignedBigInteger('id_kategori');
           $table->foreign('id_kategori')->references('id')->on('kategori')->onUpdate('cascade')->onDelete('cascade');
           
           $table->unsignedBigInteger('id_harga');
           $table->foreign('id_harga')->references('id')->on('harga')->onUpdate('cascade')->onDelete('cascade');

           $table->unsignedBigInteger('id_modal');
           $table->foreign('id_modal')->references('id')->on('modal')->onUpdate('cascade')->onDelete('cascade');

           $table->unsignedBigInteger('id_stock');
           $table->foreign('id_stock')->references('id')->on('stock')->onUpdate('cascade')->onDelete('cascade');
           
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
        //
    }
}
