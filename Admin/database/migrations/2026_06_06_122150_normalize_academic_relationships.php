<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('programs')) {
            Schema::table('programs', function (Blueprint $table) {
                if (! Schema::hasColumn('programs', 'college_id')) {
                    $table->unsignedInteger('college_id')
                        ->nullable()
                        ->after('id');
                }

                if (! Schema::hasColumn('programs', 'is_graduate_program')) {
                    $table->boolean('is_graduate_program')
                        ->default(false)
                        ->after('college_id');
                }
            });
        }

        if (Schema::hasTable('majors')) {
            Schema::table('majors', function (Blueprint $table) {
                if (! Schema::hasColumn('majors', 'program_id')) {
                    $table->unsignedInteger('program_id')
                        ->nullable()
                        ->after('id');
                }
            });
        }

        $this->backfillProgramCollegeIds();
        $this->backfillMajorProgramIds();
        $this->syncStudentCollegeFromProgram();
    }

    public function down(): void
    {
        if (Schema::hasTable('majors')) {
            Schema::table('majors', function (Blueprint $table) {
                if (Schema::hasColumn('majors', 'program_id')) {
                    $table->dropColumn('program_id');
                }
            });
        }

        if (Schema::hasTable('programs')) {
            Schema::table('programs', function (Blueprint $table) {
                if (Schema::hasColumn('programs', 'is_graduate_program')) {
                    $table->dropColumn('is_graduate_program');
                }

                if (Schema::hasColumn('programs', 'college_id')) {
                    $table->dropColumn('college_id');
                }
            });
        }
    }

    private function backfillProgramCollegeIds(): void
    {
        if (
            ! Schema::hasTable('programs') ||
            ! Schema::hasTable('college_program') ||
            ! Schema::hasColumn('programs', 'college_id')
        ) {
            return;
        }

        $programMappings = DB::table('college_program')
            ->select('program_id', DB::raw('MIN(college_id) as college_id'))
            ->groupBy('program_id')
            ->get();

        foreach ($programMappings as $mapping) {
            DB::table('programs')
                ->where('id', $mapping->program_id)
                ->whereNull('college_id')
                ->update([
                    'college_id' => $mapping->college_id,
                ]);
        }
    }

    private function backfillMajorProgramIds(): void
    {
        if (
            ! Schema::hasTable('majors') ||
            ! Schema::hasTable('program_major') ||
            ! Schema::hasColumn('majors', 'program_id')
        ) {
            return;
        }

        $majorMappings = DB::table('program_major')
            ->select('major_id', DB::raw('MIN(program_id) as program_id'))
            ->groupBy('major_id')
            ->get();

        foreach ($majorMappings as $mapping) {
            DB::table('majors')
                ->where('id', $mapping->major_id)
                ->whereNull('program_id')
                ->update([
                    'program_id' => $mapping->program_id,
                ]);
        }
    }

    private function syncStudentCollegeFromProgram(): void
    {
        if (
            ! Schema::hasTable('student_info') ||
            ! Schema::hasTable('programs') ||
            ! Schema::hasColumn('student_info', 'program_id') ||
            ! Schema::hasColumn('student_info', 'college_id') ||
            ! Schema::hasColumn('programs', 'college_id')
        ) {
            return;
        }

        DB::statement("
            UPDATE student_info si
            JOIN programs p ON p.id = si.program_id
            SET si.college_id = p.college_id
            WHERE si.program_id IS NOT NULL
            AND p.college_id IS NOT NULL
        ");
    }
};