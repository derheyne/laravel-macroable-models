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
        ModelWithMacros::macro('getTestAttributeAttribute', function ($value) {
            return 'modified: ' . $value;
        });

        $model = new ModelWithMacros([
            'test_attribute' => 'value',
        ]);

        self::assertEquals('modified: value', $model->test_attribute);
    }

    /** @test */
    public function a_macroable_model_can_have_a_computed_accessor_macro(): void
    {
        ModelWithMacros::macro('getCustomAttributeAttribute', function () {
            /** @var $this ModelWithMacros */
            return $this::class;
        });

        $model = new ModelWithMacros();

        self::assertEquals(ModelWithMacros::class, $model->custom_attribute);
    }

    /** @test */
    public function a_macroable_model_can_have_a_mutator_macro(): void
    {
        ModelWithMacros::macro('setCustomAttributeAttribute', function ($value) {
            /** @mixin ModelWithMacros */
            $this->attributes['custom_attribute'] = 'modified: ' . $value;
        });

        $model = new ModelWithMacros();
        $model->custom_attribute = 'value';

        self::assertEquals('modified: value', $model->custom_attribute);
    }

    /** @test */
    public function a_macroable_model_can_have_a_local_scope_macro(): void
    {
        ModelWithMacros::macro('scopeTest', function (Builder $query) {
            return $query->where('test_column', 'hello world');
        });

        $model = new ModelWithMacros();
        $query = $model->newQuery()->test();

        /** @noinspection SqlNoDataSourceInspection */
        self::assertEquals(
            'select * from `table` where `test_column` = ?',
            $query->toSql()
        );
    }

    public function setUp(): void
    {
        parent::setUp();

        self::assertFalse(ModelWithMacros::hasMacros());
    }

    public function tearDown(): void
    {
        // Reset static $macros property within ModelWithMacro class.
        $reflection = new ReflectionClass(new ModelWithMacros);
        $instance = $reflection->getProperty('macros');
        $instance->setAccessible(true);
        $instance->setValue([]);
        $instance->setAccessible(false);
    }
}

class ModelWithMacros extends MacroableModel
{
    protected $table = 'table';

    protected $fillable = [
        'test_attribute',
    ];

    public static function hasMacros(): bool
    {
        return count(self::$macros) > 0;
    }
}
