<?php declare(strict_types=1);
/*
 * This file is part of Aplus Framework HTTP Library.
 *
 * (c) Natan Felles <natanfelles@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Framework\HTTP;

use InvalidArgumentException;

/**
 * Class Protocol.
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Overview
 *
 * @package http
 */
class Protocol
{
    /**
     * @see https://en.wikipedia.org/wiki/HTTP/1.0
     *
     * @var string
     */
    public const HTTP_1_0 = 'HTTP/1.0';
    /**
     * @see https://en.wikipedia.org/wiki/HTTP/1.1
     *
     * @var string
     */
    public const HTTP_1_1 = 'HTTP/1.1';
    /**
     * @see https://en.wikipedia.org/wiki/HTTP/2.0
     *
     * @var string
     */
    public const HTTP_2_0 = 'HTTP/2.0';
    /**
     * @see https://en.wikipedia.org/wiki/HTTP/2
     *
     * @var string
     */
    public const HTTP_2 = 'HTTP/2';
    /**
     * @see https://en.wikipedia.org/wiki/HTTP/3
     *
     * @var string
     */
    public const HTTP_3 = 'HTTP/3';
    /**
     * @var array<string>
     */
    protected static array $protocols = [
        'HTTP/1.0',
        'HTTP/1.1',
        'HTTP/2.0',
        'HTTP/2',
        'HTTP/3',
    ];

    /**
     * @param string $protocol
     *
     * @throws InvalidArgumentException for invalid protocol
     *
     * @return string
     */
    public static function validate(string $protocol) : string
    {
        $valid = \strtoupper($protocol);
        if (\in_array($valid, static::$protocols, true)) {
            return $valid;
        }
        throw new InvalidArgumentException('Invalid protocol: ' . $protocol);
    }
}
