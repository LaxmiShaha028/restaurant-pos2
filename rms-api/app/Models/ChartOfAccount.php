<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChartOfAccount extends Model
{
    use HasFactory;
    use SoftDeletes;


    public const TYPE_ASSET =
        'asset';

    public const TYPE_LIABILITY =
        'liability';

    public const TYPE_EQUITY =
        'equity';

    public const TYPE_REVENUE =
        'revenue';

    public const TYPE_COGS =
        'cogs';

    public const TYPE_EXPENSE =
        'expense';


    public const BALANCE_DEBIT =
        'debit';

    public const BALANCE_CREDIT =
        'credit';


    protected $table =
        'chart_of_accounts';


    protected $fillable = [

        'code',

        'system_key',

        'name',

        'account_type',

        'normal_balance',

        'parent_id',

        'is_system',

        'is_active',

        'allow_manual_posting',

        'description',

        'created_by',

        'updated_by',

    ];


    protected $casts = [

        'parent_id' =>
            'integer',

        'is_system' =>
            'boolean',

        'is_active' =>
            'boolean',

        'allow_manual_posting' =>
            'boolean',

        'created_by' =>
            'integer',

        'updated_by' =>
            'integer',

    ];


    public static function allowedTypes(): array
    {
        return [

            self::TYPE_ASSET,

            self::TYPE_LIABILITY,

            self::TYPE_EQUITY,

            self::TYPE_REVENUE,

            self::TYPE_COGS,

            self::TYPE_EXPENSE,

        ];
    }


    public static function normalBalanceForType(
        string $type
    ): string {

        return match ($type) {

            self::TYPE_ASSET,
            self::TYPE_COGS,
            self::TYPE_EXPENSE =>
                self::BALANCE_DEBIT,

            self::TYPE_LIABILITY,
            self::TYPE_EQUITY,
            self::TYPE_REVENUE =>
                self::BALANCE_CREDIT,

            default =>
                throw new \InvalidArgumentException(
                    'Unsupported account type.'
                ),
        };
    }


    public static function typeOptions(): array
    {
        return [

            [
                'value' =>
                    self::TYPE_ASSET,

                'label' =>
                    'Asset',

                'normal_balance' =>
                    self::BALANCE_DEBIT,
            ],

            [
                'value' =>
                    self::TYPE_LIABILITY,

                'label' =>
                    'Liability',

                'normal_balance' =>
                    self::BALANCE_CREDIT,
            ],

            [
                'value' =>
                    self::TYPE_EQUITY,

                'label' =>
                    'Equity',

                'normal_balance' =>
                    self::BALANCE_CREDIT,
            ],

            [
                'value' =>
                    self::TYPE_REVENUE,

                'label' =>
                    'Revenue',

                'normal_balance' =>
                    self::BALANCE_CREDIT,
            ],

            [
                'value' =>
                    self::TYPE_COGS,

                'label' =>
                    'Cost of Goods Sold',

                'normal_balance' =>
                    self::BALANCE_DEBIT,
            ],

            [
                'value' =>
                    self::TYPE_EXPENSE,

                'label' =>
                    'Expense',

                'normal_balance' =>
                    self::BALANCE_DEBIT,
            ],

        ];
    }


    public function parent(): BelongsTo
    {
        return $this->belongsTo(
            self::class,
            'parent_id'
        );
    }


    public function children(): HasMany
    {
        return $this->hasMany(
            self::class,
            'parent_id'
        )->orderBy(
            'code'
        );
    }


    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }


    public function updater(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'updated_by'
        );
    }


    public function scopeSearch(
        Builder $query,
        ?string $search
    ): Builder {

        $search =
            trim(
                (string) $search
            );


        if ($search === '') {
            return $query;
        }


        return $query->where(
            function (
                Builder $builder
            ) use ($search): void {

                $builder
                    ->where(
                        'code',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'name',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'description',
                        'like',
                        "%{$search}%"
                    );
            }
        );
    }


    public function accountTypeLabel(): string
    {
        return match (
            $this->account_type
        ) {

            self::TYPE_ASSET =>
                'Asset',

            self::TYPE_LIABILITY =>
                'Liability',

            self::TYPE_EQUITY =>
                'Equity',

            self::TYPE_REVENUE =>
                'Revenue',

            self::TYPE_COGS =>
                'Cost of Goods Sold',

            self::TYPE_EXPENSE =>
                'Expense',

            default =>
                ucfirst(
                    (string) $this->account_type
                ),
        };
    }


    public function normalBalanceLabel(): string
    {
        return ucfirst(
            (string) $this->normal_balance
        );
    }
}
