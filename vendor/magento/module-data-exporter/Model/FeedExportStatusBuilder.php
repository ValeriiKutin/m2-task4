<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 */
declare(strict_types=1);

namespace Magento\DataExporter\Model;

use Magento\DataExporter\Model\Logging\CommerceDataExportLoggerInterface;
use Magento\DataExporter\Status\ExportStatusCode;
use Magento\DataExporter\Status\ExportStatusCodeFactory;

/**
 * Build FeedExportStatus class
 */
class FeedExportStatusBuilder
{
    private CommerceDataExportLoggerInterface $logger;

    /**
     * @var FeedExportStatusFactory
     */
    private FeedExportStatusFactory $feedExportStatusFactory;

    /**
     * @var ExportStatusCodeFactory
     */
    private ExportStatusCodeFactory $exportStatusCodeFactory;

    /**
     * @param FeedExportStatusFactory $feedExportStatusFactory
     * @param ExportStatusCodeFactory $exportStatusCodeFactory
     * @param CommerceDataExportLoggerInterface $logger
     */
    public function __construct(
        FeedExportStatusFactory $feedExportStatusFactory,
        ExportStatusCodeFactory $exportStatusCodeFactory,
        CommerceDataExportLoggerInterface $logger
    ) {
        $this->feedExportStatusFactory = $feedExportStatusFactory;
        $this->exportStatusCodeFactory = $exportStatusCodeFactory;
        $this->logger = $logger;
    }

    /**
     * Build data
     *
     * @param int $status
     * @param string $reasonPhrase
     * @param array $failedItems
     * @param array $metadata
     * @return FeedExportStatus
     */
    public function build(
        int $status,
        string $reasonPhrase = '',
        array $failedItems = [],
        array $metadata = []
    ) : FeedExportStatus {
        try {
            return $this->feedExportStatusFactory->create(
                [
                    'status' => $this->buildStatusCode($status),
                    'reasonPhrase' => $reasonPhrase,
                    'failedItems' => $failedItems,
                    'metadata' => $metadata
                ]
            );

        } catch (\Throwable $e) {
            $this->logger->error(
                'Data Exporter exception has occurred: ' . $e->getMessage(),
                ['exception' => $e]
            );
            throw new \RuntimeException('Unable to instantiate Feed Export Status');
        }
    }

    /**
     * Build status code
     *
     * @param int $statusCode
     * @return ExportStatusCode
     */
    private function buildStatusCode(int $statusCode) : ExportStatusCode
    {
        try {
            return $this->exportStatusCodeFactory->create(['statusCode' => $statusCode]);

        } catch (\Throwable $e) {
            $this->logger->error(
                'Data Exporter exception has occurred: ' . $e->getMessage(),
                ['exception' => $e]
            );
            throw new \RuntimeException('Unable to instantiate Export Status Code');
        }
    }
}
