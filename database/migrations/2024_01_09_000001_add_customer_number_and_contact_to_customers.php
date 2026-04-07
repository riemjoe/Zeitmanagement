<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Customer;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('customer_number', 9)->nullable()->unique()->after('id');
            $table->string('contact_person')->nullable()->after('email');
        });

        // Bestehenden Kunden eine Kundennummer vergeben
        Customer::whereNull('customer_number')->each(function (Customer $customer) {
            $customer->customer_number = \App\Models\Customer::generateNumber();
            $customer->saveQuietly();
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['customer_number', 'contact_person']);
        });
    }
};
