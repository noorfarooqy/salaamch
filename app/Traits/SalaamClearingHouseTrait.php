<?php

namespace Noorfarooqy\Salaamch\Traits;

use Noorfarooqy\Salaamch\Helpers\ErrorCodes;

trait SalaamClearingHouseTrait
{

    public function hasCorrectConfigs()
    {
        $required_configs = [
            'host' => config('salaamch.host.uri'),
            'api_rate' => config('salaamch.host.api_rate'),
            'api_deposit_success' => config('salaamch.host.api_deposit_success'),
            'api_deposit_failed' => config('salaamch.host.api_deposit_failed'),
        ];
        foreach ($required_configs as $key => $config) {
            if ($config == null) {
                $this->setError('Missing ' . $key . ' config', ErrorCodes::sch_missing_config->value);
                return false;
            }
        }

        return true;
    }
}
