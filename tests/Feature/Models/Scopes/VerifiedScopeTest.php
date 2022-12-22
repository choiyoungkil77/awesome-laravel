<?php

namespace Tests\Feature\Models\Scopes;

use App\Models\Scopes\VerifiedScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Tests\TestCase;

class VerifiedScopeTest extends TestCase
{
    /**
     * VerifiedScope 테스트
     *
     * @return void
     */
    public function testApply()
    {
        $model = $this->model();

        $this->assertTrue(Str::containsAll(
            $model->toSql(),
            ['where', 'email_verified_at', 'is not null']
        ));
    }

    /**
     * Model
     *
     * @param $attributes
     * @return \Illuminate\Database\Eloquent\Model
     */
    private function model($attributes = [])
    {
        return new class($attributes) extends Model
        {
            /**
             * Perform any actions required after the model boots.
             *
             * @return void
             */
            protected static function booted()
            {
                static::addGlobalScope(new VerifiedScope());
            }
        };
    }
}
