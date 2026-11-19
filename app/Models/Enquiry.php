<?php

// PHP start tag.

// Eloquent models live here. A model is a PHP class that reads and writes one database table.

namespace App\Models;

// Fillable lists the columns we are allowed to set with Enquiry::create([...]).
use Illuminate\Database\Eloquent\Attributes\Fillable;
// Model gives us query(), create(), save(), and the table name guess "enquiries".
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * One saved customer enquiry.
 *
 * @property int $id The database row number.
 * @property int $product_id The Fake Store product the customer asked about.
 * @property string $product_title A copy of the title, kept even if the API changes later.
 * @property string $name
 * @property string $email
 * @property string $phone
 * @property string $message
 * @property Carbon|null $created_at When the row was inserted.
 * @property Carbon|null $updated_at When the row was last changed.
 */
// Only these columns can be mass-assigned. id and timestamps are set by the database.
#[Fillable(['product_id', 'product_title', 'name', 'email', 'phone', 'message'])]
class Enquiry extends Model
{
    /**
     * Tell Eloquent that product_id should always come back as an integer.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'product_id' => 'integer',
        ];
    }
}
