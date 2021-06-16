<?php

namespace Dheyne\LaravelMacroableModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;

class MacroableModel extends Model
{
    use Macroable {
        Macroable::__call as __macroCall;
    }

    public function hasGetMutator($key): bool
    {
        $mutatorMethodName = 'get' . Str::studly($key) . 'Attribute';

        if (self::hasMacro($mutatorMethodName)) {
            return true;
        }

        return parent::hasGetMutator($key);
    }

    public function hasSetMutator($key): bool
    {
        $mutatorMethodName = 'set' . Str::studly($key) . 'Attribute';

        if (self::hasMacro($mutatorMethodName)) {
            return true;
        }

        return parent::hasSetMutator($key);
    }

    public function hasNamedScope($scope): bool
    {
        $scopeMethodName = 'scope' . ucfirst($scope);

        if (self::hasMacro($scopeMethodName)) {
            return true;
        }

        return parent::hasNamedScope($scope);
    }

    public function __call($method, $parameters)
    {
        if (self::hasMacro($method)) {
            return $this->__macroCall($method, $parameters);
        }

        return parent::__call($method, $parameters);
    }
}
