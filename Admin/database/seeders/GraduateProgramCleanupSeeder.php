<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GraduateProgramCleanupSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Graduate Program Cleanup
        |--------------------------------------------------------------------------
        |
        | Add explicit mappings here.
        |
        | Format:
        | 'Program Name' => 'Actual College Name'
        |
        | Example:
        | 'Master in Information Technology' => 'College of Computer Studies'
        |
        */

        $graduateProgramMappings = [
            'Doctor of Philosophy in Sociology' => 'College of Arts and Sciences',
            'Doctor of Public Administration' => 'School of Business and Management',
            'MS Agricultural Economics' => 'College of Agriculture',
            'MS Biology' => 'College of Arts and Sciences',
            'MS Health and Hospital Management' => 'School of Business and Management',
            'MS Chemical Engineering with Specialization in Advanced Energy' => 'College of Engineering',
            'MS Chemistry' => 'College of Arts and Sciences',
            'MA Economics' => 'College of Arts and Sciences',
            'MA English Language' => 'College of Arts and Sciences',
            'MA Health Professions Education' => 'School of Education',
            'MA History' => 'College of Arts and Sciences',
            'MA Literature' => 'College of Arts and Sciences',
            'MA Nursing' => 'College of Nursing',
            'MA Psychology' => 'College of Arts and Sciences',
            'MA Philosophy' => 'College of Arts and Sciences',
            'MA Sociology' => 'College of Arts and Sciences',
            'Master in Public Administration' => 'School of Business and Management',
            'Master of Engineering' => 'College of Engineering',
            'Master in Biology' => 'College of Arts and Sciences',
            'Master in English Language and Literature' => 'College of Arts and Sciences',
            'Master in English Language' => 'College of Arts and Sciences',
            'Master in History' => 'College of Arts and Sciences',
            'Master in Literature' => 'College of Arts and Sciences',
            'Master in Psychology' => 'College of Arts and Sciences',
            'Master in Information Technology' => 'College of Computer Studies',
            'MS Chemical Engineering with Specialization in Pollution Control' => 'College of Engineering',
            'Master of Arts in English' => 'College of Arts and Sciences',
            '(UPDATED) Master in Business Administration' => 'School of Business and Management',
            '(UPDATED) Doctor in Business Management' => 'School of Business and Management',
            'Masters in Teaching Communication Arts in Filipino' => 'School of Education',

            // add more graduate program mappings here
        ];

        foreach ($graduateProgramMappings as $programName => $collegeName) {
            $college = DB::table('colleges')
                ->where('college_name', $collegeName)
                ->first();

            $program = DB::table('programs')
                ->where('program_name', $programName)
                ->first();

            if (! $college || ! $program) {
                continue;
            }

            DB::table('programs')
                ->where('id', $program->id)
                ->update([
                    'college_id' => $college->id,
                    'is_graduate_program' => true,
                ]);

            DB::table('student_info')
                ->where('program_id', $program->id)
                ->update([
                    'college_id' => $college->id,
                ]);
        }
    }
}