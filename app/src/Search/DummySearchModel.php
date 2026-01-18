<?php

declare(strict_types=1);

/*
 * UserFrosting Learn (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/Learn
 * @copyright Copyright (c) 2025 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/Learn/blob/main/LICENSE.md (MIT License)
 */

namespace UserFrosting\Learn\Search;

use Illuminate\Database\Eloquent\Model;

/**
 * Dummy model used by SearchSprunje to satisfy Sprunje's type requirements.
 * This model is never actually used for database queries.
 */
class DummySearchModel extends Model
{
    /**
     * @var string The table associated with the model (not used)
     */
    protected $table = 'search_dummy';

    /**
     * @var bool Indicates if the model should be timestamped
     */
    public $timestamps = false;
}
