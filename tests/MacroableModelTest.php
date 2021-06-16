<?php

namespace Dheyne\LaravelMacroableModels\Tests;

use Dheyne\LaravelMacroableModels\MacroableModel;
use Illuminate\Database\Eloquent\Builder;
use ReflectionClass;

class MacroableModelTest extends TestCase
{
    /** @test */
    public function a_macroable_model_can_have_a_macro(): void
    {
        ModelWithMacros::macro('anActualMacro', function () {
            return 'macro response';
        });

        $model = new ModelWithMacros();

        self::assertTrue(ModelWithMacros::hasMacro('anActualMacro'));
        self::assertEquals('macro response', $model->anActualMacro());
    }

    /** @test */
    public function a_macroable_model_can_have_a_macro_with_parameters(): void
    {
        ModelWithMacros::macro('aMacroWithParameters', function ($param1, $param2) {
            return 'macro response with two parameters: ' . $param1 . ', ' . $param2;
        });

        $model = new ModelWithMacros();

        self::assertEquals(
            'macro response with two parameters: value1, value2',
            $model->aMacroWithParameters('value1', 'value2')
        );
    }

    /** @test */
    public function a_macroable_model_can_have_an_accessor_macro(): void
    {
        ModelWithMacros::macro('getMacroAccessorAttribute', function ($value) {
            return 'modified: ' . $value;
        });

        $model = new ModelWithMacros();
        $model->macro_accessor = 'macro accessor value';

        self::assertEquals('modified: macro accessor value', $model->macro_accessor);
    }

    /** @test */
    public function a_macroable_model_can_have_a_computed_accessor_macro(): void
    {
        ModelWithMacros::macro('getMacroComputedAccessorAttribute', function () {
            /** @var $this ModelWithMacros */
            return $this::class;
        });

        $model = new ModelWithMacros();

        self::assertEquals(ModelWithMacros::class, $model->macro_computed_accessor);
    }

    /** @test */
    public function a_macroable_model_can_have_a_mutator_macro(): void
    {
        ModelWithMacros::macro('setMacroMutatorAttribute', function ($value) {
            /** @mixin ModelWithMacros */
            $this->attributes['macro_mutator'] = 'modified: ' . $value;
        });

        $model = new ModelWithMacros();
        $model->macro_mutator = 'macro mutator value';

        self::assertEquals('modified: macro mutator value', $model->macro_mutator);
    }

    /** @test */
    public function a_macroable_model_can_have_a_local_scope_macro(): void
    {
        ModelWithMacros::macro('scopeMacroScope', function (Builder $query) {
            return $query->where('macro_scope_column', 'hello world');
        });

        $model = new ModelWithMacros();
        $query = $model->newQuery()->macroScope();

        /** @noinspection SqlNoDataSourceInspection */
        self::assertEquals(
            'select * from `table` where `macro_scope_column` = ?',
            $query->toSql()
        );
    }

    /** @test */
    public function a_macroable_model_can_have_native_local_scopes(): void
    {
        $model = new ModelWithMacros();
        $query = $model->newQuery()->localScope();

        self::assertEquals(
            'select * from `table` where `local_scope_column` = ?',
            $query->toSql()
        );
    }

    /** @test */
    public function a_macroable_model_can_have_native_accessors(): void
    {
        $model = new ModelWithMacros();
        $model->native_accessor = 'native accessor value';

        self::assertEquals('modified: native accessor value', $model->native_accessor);
    }

    /** @test */
    public function a_macroable_model_can_have_native_computed_accessors(): void
    {
        $model = new ModelWithMacros();

        self::assertEquals($model::class, $model->native_computed_accessor);
    }

    /** @test */
    public function a_macroable_model_can_have_native_mutators(): void
    {
        $model = new ModelWithMacros();
        $model->native_mutator = 'native mutator value';

        self::assertEquals('modified: native mutator value', $model->native_mutator);
    }

    public function setUp(): void
    {
        parent::setUp();

        self::assertFalse(ModelWithMacros::hasMacros());
    }

    public function tearDown(): void
    {
        // Reset static $macros property within ModelWithMacro class.
        $reflection = new ReflectionClass(new ModelWithMacros());
        $instance = $reflection->getProperty('macros');
        $instance->setAccessible(true);
        $instance->setValue([]);
        $instance->setAccessible(false);
    }
}

class ModelWithMacros extends MacroableModel
{
    protected $table = 'table';

    public static function hasMacros(): bool
    {
        return count(self::$macros) > 0;
    }

    public function scopeLocalScope($query)
    {
        return $query->where('local_scope_column', 'value');
    }

    public function getNativeAccessorAttribute($value)
    {
        return 'modified: ' . $value;
    }

    public function getNativeComputedAccessorAttribute()
    {
        return $this::class;
    }

    public function setNativeMutatorAttribute($value)
    {
        $this->attributes['native_mutator'] = 'modified: ' . $value;
    }
}
