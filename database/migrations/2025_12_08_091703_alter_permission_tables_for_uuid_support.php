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
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');

        // Drop existing foreign keys and primary keys
        Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($columnNames) {
            $table->dropForeign(['permission_id']);
            $table->dropPrimary();
        });

        Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($columnNames) {
            $table->dropForeign(['role_id']);
            $table->dropPrimary();
        });

        // Change model_id to model_uuid (string for UUIDs)
        Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($columnNames) {
            $table->dropIndex('model_has_permissions_model_id_model_type_index');
            $table->string($columnNames['model_morph_key'])->change();
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_model_uuid_model_type_index');
        });

        Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($columnNames) {
            $table->dropIndex('model_has_roles_model_id_model_type_index');
            $table->string($columnNames['model_morph_key'])->change();
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_roles_model_uuid_model_type_index');
        });

        // Recreate foreign keys and primary keys
        Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames, $columnNames) {
            $table->foreign('permission_id')
                ->references('id')
                ->on($tableNames['permissions'])
                ->onDelete('cascade');

            $table->primary(['permission_id', $columnNames['model_morph_key'], 'model_type'],
                'model_has_permissions_permission_model_type_primary');
        });

        Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames, $columnNames) {
            $table->foreign('role_id')
                ->references('id')
                ->on($tableNames['roles'])
                ->onDelete('cascade');

            $table->primary(['role_id', $columnNames['model_morph_key'], 'model_type'],
                'model_has_roles_role_model_type_primary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');

        // Drop foreign keys and primary keys
        Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($columnNames) {
            $table->dropForeign(['permission_id']);
            $table->dropPrimary();
        });

        Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($columnNames) {
            $table->dropForeign(['role_id']);
            $table->dropPrimary();
        });

        // Change model_uuid back to model_id (big integer)
        Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($columnNames) {
            $table->dropIndex('model_has_permissions_model_uuid_model_type_index');
            $table->unsignedBigInteger($columnNames['model_morph_key'])->change();
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_model_id_model_type_index');
        });

        Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($columnNames) {
            $table->dropIndex('model_has_roles_model_uuid_model_type_index');
            $table->unsignedBigInteger($columnNames['model_morph_key'])->change();
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_roles_model_id_model_type_index');
        });

        // Recreate foreign keys and primary keys
        Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames, $columnNames) {
            $table->foreign('permission_id')
                ->references('id')
                ->on($tableNames['permissions'])
                ->onDelete('cascade');

            $table->primary(['permission_id', $columnNames['model_morph_key'], 'model_type'],
                'model_has_permissions_permission_model_type_primary');
        });

        Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames, $columnNames) {
            $table->foreign('role_id')
                ->references('id')
                ->on($tableNames['roles'])
                ->onDelete('cascade');

            $table->primary(['role_id', $columnNames['model_morph_key'], 'model_type'],
                'model_has_roles_role_model_type_primary');
        });
    }
};
