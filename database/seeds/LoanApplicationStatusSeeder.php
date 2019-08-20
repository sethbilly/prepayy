<?php

use App\Entities\LoanApplicationStatus;
use Illuminate\Database\Seeder;

class LoanApplicationStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $statuses = collect(LoanApplicationStatus::STATUS)
            ->map(function(string $val) {
                $rec = LoanApplicationStatus::firstOrNew(['status' => $val]);
                $rec->status = $val;
                $rec->save();
            });

        LoanApplicationStatus::whereNotIn('id', $statuses->pluck('id')->all())->delete();
    }
}
