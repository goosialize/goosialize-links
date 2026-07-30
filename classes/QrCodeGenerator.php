<?php

declare(strict_types=1);

namespace Goosialize\Links;

use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use InvalidArgumentException;
use RuntimeException;

final class QrCodeGenerator
{
    public function __construct(
        private readonly int $size = 320,
        private readonly int $margin = 16
    ) {
        if (
            $this->size < 128 ||
            $this->size > 2048
        ) {
            throw new InvalidArgumentException(
                'QR size is outside the supported range.'
            );
        }

        if (
            $this->margin < 0 ||
            $this->margin > 128
        ) {
            throw new InvalidArgumentException(
                'QR margin is outside the supported range.'
            );
        }
    }

    public function generatePng(
        string $trackedUrl
    ): string {
        if (!extension_loaded('gd')) {
            throw new RuntimeException(
                'The GD extension is required for PNG output.'
            );
        }

        $result =
            (new PngWriter())
                ->write(
                    $this->createQrCode(
                        $trackedUrl
                    )
                );

        if (
            $result->getMimeType() !==
                'image/png'
        ) {
            throw new RuntimeException(
                'QR PNG writer returned an invalid MIME type.'
            );
        }

        $content = $result->getString();

        if (
            !str_starts_with(
                $content,
                "\x89PNG\r\n\x1a\n"
            )
        ) {
            throw new RuntimeException(
                'QR PNG output is invalid.'
            );
        }

        return $content;
    }

    public function generateSvg(
        string $trackedUrl
    ): string {
        $result =
            (new SvgWriter())
                ->write(
                    $this->createQrCode(
                        $trackedUrl
                    )
                );

        if (
            $result->getMimeType() !==
                'image/svg+xml'
        ) {
            throw new RuntimeException(
                'QR SVG writer returned an invalid MIME type.'
            );
        }

        $content = $result->getString();

        if (
            !str_contains($content, '<svg') ||
            !str_contains(
                $content,
                'xmlns="http://www.w3.org/2000/svg"'
            )
        ) {
            throw new RuntimeException(
                'QR SVG output is invalid.'
            );
        }

        return $content;
    }

    private function createQrCode(
        string $trackedUrl
    ): QrCode {
        $trackedUrl = trim($trackedUrl);

        if (
            strlen($trackedUrl) > 2048 ||
            filter_var(
                $trackedUrl,
                FILTER_VALIDATE_URL
            ) === false
        ) {
            throw new InvalidArgumentException(
                'Tracked QR URL is invalid.'
            );
        }

        $scheme = strtolower(
            (string) parse_url(
                $trackedUrl,
                PHP_URL_SCHEME
            )
        );

        $host = trim(
            (string) parse_url(
                $trackedUrl,
                PHP_URL_HOST
            )
        );

        if (
            !in_array(
                $scheme,
                ['http', 'https'],
                true
            ) ||
            $host === ''
        ) {
            throw new InvalidArgumentException(
                'Tracked QR URL must use HTTP or HTTPS.'
            );
        }

        return new QrCode(
            data: $trackedUrl,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel:
                ErrorCorrectionLevel::Medium,
            size: $this->size,
            margin: $this->margin,
            roundBlockSizeMode:
                RoundBlockSizeMode::Margin
        );
    }
}
