<?php

namespace App\Contracts;

use Carbon\Carbon;

interface NewsSourceContract
{
    public function sourceKey(): string;

    public function sourceUrl(): string;

    public function pull(Carbon $since = null);


}
