<?php

declare(strict_types=1);

/**
 * SPDX-License-Identifier: Apache-2.0
 *
 * The OpenSearch Contributors require contributions made to
 * this file be licensed under the Apache-2.0 license or a
 * compatible open source license.
 *
 * Modifications Copyright OpenSearch Contributors. See
 * GitHub history for details.
 */

namespace OpenSearch\Endpoints\Ml;

use OpenSearch\Exception\RuntimeException;
use OpenSearch\Endpoints\AbstractEndpoint;

/**
 * NOTE: This file is autogenerated using util/GenerateEndpoints.php
 */
class GetMemory extends AbstractEndpoint
{
    protected $memory_id;

    public function getURI(): string
    {
        $memory_id = $this->memory_id ?? null;
        if (isset($memory_id)) {
            return "/_plugins/_ml/memory/$memory_id";
        }
        throw new RuntimeException('Missing parameter for the endpoint ml.get_memory');
    }

    public function getParamWhitelist(): array
    {
        return [
            'pretty',
            'human',
            'error_trace',
            'source',
            'filter_path'
        ];
    }

    public function getMethod(): string
    {
        return 'GET';
    }

    public function setMemoryId($memory_id): static
    {
        if (is_null($memory_id)) {
            return $this;
        }
        $this->memory_id = $memory_id;

        return $this;
    }
}
