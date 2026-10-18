<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
 
class ExpensesJournal extends Model
{
    use HasFactory;
 
    protected $table = 'expenses_journal';
 
    protected $fillable = [
        'e_id',
        'e_transid',
        'e_total',
        'e_vat',
        'e_wtaxpercent',
        'e_wtax',
        'e_amount',
        'store_code',
        'approved_by',
        'approved_at',
    ];
 
    // No Eloquent model exists for inventory_etransaction in this project
    // (ExpensesController queries it via DB::table() query builder), so
    // pull the header manually where needed, e.g.:
    // DB::table('inventory_etransaction')->where('id', $journal->e_id)->first();
 
    // Who approved this expense.
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}