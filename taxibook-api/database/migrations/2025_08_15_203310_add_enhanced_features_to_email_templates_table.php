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
        Schema::table('email_templates', function (Blueprint $table) {
            $table->text('html_body')->nullable()->after('body');
            $table->text('css_styles')->nullable()->after('html_body');
            $table->string('template_type')->default('blade')->after('css_styles'); // blade, html, wysiwyg
            $table->json('version_history')->nullable()->after('template_type');
            $table->json('template_components')->nullable()->after('version_history');
            $table->json('test_recipients')->nullable()->after('template_components');
            $table->string('parent_template')->nullable()->after('test_recipients');
            $table->json('meta_data')->nullable()->after('parent_template');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_templates', function (Blueprint $table) {
            $table->dropColumn([
                'html_body',
                'css_styles',
                'template_type',
                'version_history',
                'template_components',
                'test_recipients',
                'parent_template',
                'meta_data'
            ]);
        });
    }
};
