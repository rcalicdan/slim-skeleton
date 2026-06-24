<?php

declare(strict_types=1);

namespace Integrations\Rules;

use Hibla\QueryBuilder\DB;
use Somnambulist\Components\Validation\Rule;

use function Hibla\await;

class UniqueRule extends Rule
{
    protected string $message = 'The :attribute has already been taken.';

    /**
     * Define the parameters this rule accepts in order: unique:table,column,except_id
     */
    protected array $fillableParams = ['table', 'column', 'except_id'];

    public function check(mixed $value): bool
    {
        $table = $this->parameter('table');
        $column = $this->parameter('column') ?? $this->attribute->key();
        $exceptId = $this->parameter('except_id');

        if (empty($table)) {
            throw new \InvalidArgumentException('Unique validation rule requires a table name parameter.');
        }

        $query = DB::table($table)->where($column, $value);

        // If an ID is provided to ignore (e.g. during a profile update), exclude it
        if ($exceptId !== null && $exceptId !== '') {
            $query = $query->where('id', '!=', $exceptId);
        }

        // Returns true if the record does NOT exist (meaning it is unique)
        return ! await($query->exists());
    }
}