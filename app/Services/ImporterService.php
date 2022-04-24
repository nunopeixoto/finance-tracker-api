<?php
namespace App\Services;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ExpenseSubCategory;
use Spatie\SimpleExcel\SimpleExcelReader;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class ImporterService {

    private $path = '';
    private $user;
    public function __construct(User $user, string $filePath)
    {
        $this->path = $filePath;
        $this->user = $user;
    }

    /**
     * Not built for performance. Don't use on huge files.
     * Expects a XLSX/CSV with the columns: date, description, debit, credit, category, note
     * Optionally, an adicional column "subcategory" can be added
     * Date should be in d-m-Y format
     *
     * @return void
     */
    public function importData() : void
    {

        DB::transaction(function () {
            $rows = SimpleExcelReader::create($this->path)->getRows();

            $expensesToCreate = [];
            $rows->each(function(array $row) use (&$expensesToCreate) {
                $expenseCategory = ExpenseCategory::queryUser($this->user->id)
                    ->firstOrCreate([
                        'user_id' => $this->user->id,
                        'description' => $row['category']
                    ])
                ;

                if ($row['subcategory']) {
                    $expenseSubCategory = ExpenseSubCategory::queryUser($this->user->id)
                        ->firstOrCreate([
                            'user_id' => $this->user->id,
                            'description' => $row['subcategory']
                        ])
                    ;
                }

                $expensesToCreate[] = [
                    'user_id' => $this->user->id,
                    'description' => $row['description'],
                    'date' => $row['date'],
                    'expense_category_id' => $expenseCategory->id,
                    'expense_sub_category_id' => $expenseSubCategory->id ??  null,
                    'note' => $row['note'],
                    'debit' => $row['debit'] ? (float) $row['debit'] : null,
                    'credit' => $row['credit'] ? (float) $row['credit'] : null,
                ];
            });

            Expense::insert($expensesToCreate);
        });
    }
}
