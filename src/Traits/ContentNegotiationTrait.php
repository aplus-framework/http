<?php declare(strict_types=1);
/*
 * This file is part of Aplus Framework HTTP Library.
 *
 * (c) Natan Felles <natanfelles@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Framework\HTTP\Traits;

use Framework\HTTP\Message;

/**
 * Trait ContentNegotiationTrait.
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Guides/Content_negotiation
 *
 * @package http
 */
trait ContentNegotiationTrait
{
    /**
     * @var array<string,array<mixed>|null>
     */
    protected array $negotiation = [
        'ACCEPT' => null,
        'CHARSET' => null,
        'ENCODING' => null,
        'LANGUAGE' => null,
    ];

    /**
     * @param string $type
     *
     * @return array<int,string>
     */
    protected function getNegotiableValues(string $type) : array
    {
        if ($this->negotiation[$type]) {
            return $this->negotiation[$type];
        }
        $header = $_SERVER['HTTP_ACCEPT' . ($type !== 'ACCEPT' ? '_' . $type : '')] ?? null;
        $this->negotiation[$type] = \array_keys(Message::parseQualityValues(
            $header
        ));
        $this->negotiation[$type] = \array_map('\strtolower', $this->negotiation[$type]);
        return $this->negotiation[$type];
    }

    /**
     * @param string $type
     * @param array<string> $negotiable
     *
     * @return string
     */
    protected function negotiate(string $type, array $negotiable) : string
    {
        $negotiable = \array_map('\strtolower', $negotiable);
        foreach ($this->getNegotiableValues($type) as $item) {
            if (\in_array($item, $negotiable, true)) {
                return $item;
            }
        }
        return $negotiable[0];
    }

    /**
     * Get the mime types of the Accept header.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Accept
     *
     * @return array<int,string>
     */
    public function getAccepts() : array
    {
        return $this->getNegotiableValues('ACCEPT');
    }

    /**
     * Negotiate the Accept header.
     *
     * @param array<string> $negotiable Allowed mime types
     *
     * @return string The negotiated mime type
     */
    public function negotiateAccept(array $negotiable) : string
    {
        return $this->negotiate('ACCEPT', $negotiable);
    }

    /**
     * Get the Accept-Charset's.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Accept-Charset
     *
     * @return array<int,string>
     */
    public function getCharsets() : array
    {
        return $this->getNegotiableValues('CHARSET');
    }

    /**
     * Negotiate the Accept-Charset.
     *
     * @param array<string> $negotiable Allowed charsets
     *
     * @return string The negotiated charset
     */
    public function negotiateCharset(array $negotiable) : string
    {
        return $this->negotiate('CHARSET', $negotiable);
    }

    /**
     * Get the Accept-Encoding.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Accept-Encoding
     *
     * @return array<int,string>
     */
    public function getEncodings() : array
    {
        return $this->getNegotiableValues('ENCODING');
    }

    /**
     * Negotiate the Accept-Encoding.
     *
     * @param array<string> $negotiable The allowed encodings
     *
     * @return string The negotiated encoding
     */
    public function negotiateEncoding(array $negotiable) : string
    {
        return $this->negotiate('ENCODING', $negotiable);
    }

    /**
     * Get the Accept-Language's.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Accept-Language
     *
     * @return array<int,string>
     */
    public function getLanguages() : array
    {
        return $this->getNegotiableValues('LANGUAGE');
    }

    /**
     * Negotiated the Accept-Language.
     *
     * @param array<string> $negotiable Allowed languages
     *
     * @return string The negotiated language
     */
    public function negotiateLanguage(array $negotiable) : string
    {
        return $this->negotiate('LANGUAGE', $negotiable);
    }
}
