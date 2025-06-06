<?php

declare(strict_types=1);

/**
 * Copyright OpenSearch Contributors
 * SPDX-License-Identifier: Apache-2.0
 *
 * OpenSearch PHP client
 *
 * @link      https://github.com/opensearch-project/opensearch-php/
 * @copyright Copyright (c) Elasticsearch B.V (https://www.elastic.co)
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @license   https://www.gnu.org/licenses/lgpl-2.1.html GNU Lesser General Public License, Version 2.1
 *
 * Licensed to Elasticsearch B.V under one or more agreements.
 * Elasticsearch B.V licenses this file to you under the Apache 2.0 License or
 * the GNU Lesser General Public License, Version 2.1, at your option.
 * See the LICENSE file in the project root for more information.
 */

namespace OpenSearch\Endpoints;

use OpenSearch\Exception\RuntimeException;

/**
 * NOTE: This file is autogenerated using util/GenerateEndpoints.php
 */
class GetSource extends AbstractEndpoint
{
    public function getURI(): string
    {
        if (!isset($this->id) || $this->id === '') {
            throw new RuntimeException('id is required for get_source');
        }
        $id = $this->id;
        if (!isset($this->index) || $this->index === '') {
            throw new RuntimeException('index is required for get_source');
        }
        $index = $this->index;

        return "/$index/_source/$id";
    }

    public function getParamWhitelist(): array
    {
        return [
            '_source',
            '_source_excludes',
            '_source_includes',
            'preference',
            'realtime',
            'refresh',
            'routing',
            'version',
            'version_type',
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
}
