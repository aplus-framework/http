<?php declare(strict_types=1);
/*
 * This file is part of Aplus Framework HTTP Library.
 *
 * (c) Natan Felles <natanfelles@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Framework\HTTP\Debug;

use Framework\Debug\Collector;
use Framework\Debug\Debugger;
use Framework\Helpers\ArraySimple;
use Framework\HTTP\Request;
use Framework\HTTP\Response;

/**
 * Class HTTPCollector.
 *
 * @package http
 */
class HTTPCollector extends Collector
{
    protected Request $request;
    protected Response $response;

    public function setRequest(Request $request) : static
    {
        $this->request = $request;
        return $this;
    }

    public function setResponse(Response $response, bool $replaceRequest = true) : static
    {
        $this->response = $response;
        if ($replaceRequest) {
            $this->setRequest($response->getRequest());
        }
        return $this;
    }

    public function getActivities() : array
    {
        $activities = [];
        foreach ($this->getData() as $data) {
            if (isset($data['message'], $data['type']) &&
                $data['message'] === 'response' &&
                $data['type'] === 'send'
            ) {
                $activities[] = [
                    'collector' => $this->getName(),
                    'class' => static::class,
                    'description' => 'Send response',
                    'start' => $data['start'],
                    'end' => $data['end'],
                ];
            }
        }
        return $activities;
    }

    public function getContents() : string
    {
        \ob_start(); ?>
        <h1>Request</h1>
        <?= $this->renderRequest() ?>
        <h1>Response</h1>
        <?= $this->renderResponse() ?>
        <?php
        return \ob_get_clean(); // @phpstan-ignore-line
    }

    protected function renderRequest() : string
    {
        if ( ! isset($this->request)) {
            return '<p>A Request instance has not been set on this collector.</p>';
        }
        \ob_start(); ?>
        <p title="REMOTE_ADDR"><strong>IP:</strong> <?= $this->request->getIp() ?></p>
        <p><strong>Protocol:</strong> <?= $this->request->getProtocol() ?></p>
        <p><strong>Method:</strong> <?= $this->request->getMethod() ?></p>
        <p><strong>URL:</strong> <?= $this->request->getUrl() ?></p>
        <p><strong>Server:</strong> <?= $this->request->getServer('SERVER_SOFTWARE') ?></p>
        <p><strong>Hostname:</strong> <?= \gethostname() ?></p>
        <?= $this->renderRequestUserAgent() ?>
        <?php
        echo $this->renderHeadersTable($this->request->getHeaderLines());
        echo $this->renderRequestBody();
        echo $this->renderRequestForm();
        echo $this->renderRequestFiles();
        return \ob_get_clean(); // @phpstan-ignore-line
    }

    protected function renderRequestUserAgent() : string
    {
        $userAgent = $this->request->getUserAgent();
        if ($userAgent === null) {
            return '';
        }
        \ob_start(); ?>
        <h2>User-Agent</h2>
        <table>
            <thead>
            <tr>
                <th>Type</th>
                <th>Name</th>
                <th>Version</th>
                <th>Platform</th>
                <th>Is Mobile</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td><?= \htmlentities($userAgent->getType()) ?></td>
                <td><?= \htmlentities($userAgent->getName()) ?></td>
                <td><?= $userAgent->isBrowser()
                        ? \htmlentities((string) $userAgent->getBrowserVersion())
                        : '' ?></td>
                <td><?= \htmlentities((string) $userAgent->getPlatform()) ?></td>
                <td><?= $userAgent->isMobile() ? 'Yes' : 'No' ?></td>
            </tr>
            </tbody>
        </table>
        <?php
        return \ob_get_clean(); // @phpstan-ignore-line
    }

    protected function renderRequestBody() : string
    {
        $body = $this->request->hasFiles()
            ? \http_build_query($this->request->getPost())
            : $this->request->getBody();
        if ($body === '') {
            return '';
        }
        \ob_start(); ?>
        <h2>Body Contents</h2>
        <pre><code class="<?= $this->getCodeLanguage(
            $this->request->getHeader('Content-Type')
        ) ?>"><?= \htmlentities($body) ?></code></pre>
        <?php
        return \ob_get_clean(); // @phpstan-ignore-line
    }

    protected function renderRequestForm() : string
    {
        if ( ! $this->request->isPost() && ! $this->request->isForm()) {
            return '';
        }
        \ob_start(); ?>
        <h2>Form</h2>
        <table>
            <thead>
            <tr>
                <th>Field</th>
                <th>Value</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach (ArraySimple::convert($this->request->getParsedBody()) as $field => $value): ?>
                <tr>
                    <td><?= \htmlentities($field) ?></td>
                    <td>
                        <pre><?= \htmlentities($value) ?></pre>
                    </td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
        <?php
        return \ob_get_clean(); // @phpstan-ignore-line
    }

    protected function renderRequestFiles() : string
    {
        if ( ! $this->request->hasFiles()) {
            return '';
        }
        \ob_start(); ?>
        <h2>Uploaded Files</h2>
        <table>
            <thead>
            <tr>
                <th>Field</th>
                <th>Name</th>
                <th>Full Path</th>
                <th>Type</th>
                <th>Client Type</th>
                <th>Extension</th>
                <th>Size</th>
                <th>Destination</th>
                <th colspan="2">Error</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach (ArraySimple::convert($this->request->getFiles()) as $field => $file): ?>
                <tr>
                    <td><?= \htmlentities($field) ?></td>
                    <td><?= \htmlentities($file->getName()) ?></td>
                    <td><?= \htmlentities($file->getFullPath()) ?></td>
                    <td><?= \htmlentities($file->getType()) ?></td>
                    <td><?= \htmlentities($file->getClientType()) ?></td>
                    <td><?= \htmlentities($file->getExtension()) ?></td>
                    <td><?= Debugger::convertSize($file->getSize()) ?></td>
                    <td><?= $file->getDestination() ?></td>
                    <td><?= $file->getError() ?></td>
                    <td><?= \htmlentities($file->getErrorMessage()) ?></td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
        <?php
        return \ob_get_clean(); // @phpstan-ignore-line
    }

    protected function renderResponse() : string
    {
        if ( ! isset($this->response)) {
            return '<p>A Response instance has not been set on this collector.</p>';
        }
        \ob_start(); ?>
        <p><strong>Protocol:</strong> <?= \htmlentities($this->response->getProtocol()) ?></p>
        <p><strong>Status:</strong> <?= \htmlentities($this->response->getStatus()) ?></p>
        <p><strong>Sent:</strong> <?= $this->response->isSent() ? 'Yes' : 'No' ?></p>
        <?php
        if ($this->response->isSent()):
            $info = [];
            foreach ($this->getData() as $data) {
                if (
                    isset($data['message'], $data['type'])
                    && $data['message'] === 'response'
                    && $data['type'] === 'send'
                ) {
                    $info = $data;
                    break;
                }
            } ?>
            <p>
                <strong>Time Sending:</strong> <?= \round($info['end'] - $info['start'], 6) ?> seconds
            </p>
        <?php
        endif;
        echo $this->renderHeadersTable($this->response->getHeaderLines());
        echo $this->renderResponseCookies();
        echo $this->renderResponseBody();
        return \ob_get_clean(); // @phpstan-ignore-line
    }

    protected function renderResponseCookies() : string
    {
        if ( ! $this->response->getCookies()) {
            return '';
        }
        \ob_start(); ?>
        <h2>Cookies</h2>
        <table>
            <thead>
            <tr>
                <th>Name</th>
                <th>Value</th>
                <th>Expires</th>
                <th>Path</th>
                <th>Domain</th>
                <th>Is Secure</th>
                <th>Is HTTP Only</th>
                <th>SameSite</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($this->response->getCookies() as $cookie): ?>
                <tr>
                    <td><?= \htmlentities($cookie->getName()) ?></td>
                    <td><?= \htmlentities($cookie->getValue()) ?></td>
                    <td><?= $cookie->getExpires()?->format('D, d-M-Y H:i:s \G\M\T') ?></td>
                    <td><?= \htmlentities((string) $cookie->getPath()) ?></td>
                    <td><?= \htmlentities((string) $cookie->getDomain()) ?></td>
                    <td><?= $cookie->isSecure() ? 'Yes' : 'No' ?></td>
                    <td><?= $cookie->isHttpOnly() ? 'Yes' : 'No' ?></td>
                    <td><?= \htmlentities((string) $cookie->getSameSite()) ?></td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
        <?php
        return \ob_get_clean(); // @phpstan-ignore-line
    }

    protected function renderResponseBody() : string
    {
        \ob_start(); ?>
        <h2>Body Contents</h2>
        <?php
        if ( ! $this->response->isSent()) {
            echo '<p>Response has not been sent.</p>';
            return \ob_get_clean(); // @phpstan-ignore-line
        }
        if ($this->response->hasDownload()) {
            echo '<p>Body has downloadable content.</p>';
            return \ob_get_clean(); // @phpstan-ignore-line
        }
        $body = $this->response->getBody();
        if ($body === '') {
            echo '<p>Body is empty.</p>';
            return \ob_get_clean(); // @phpstan-ignore-line
        } ?>
        <pre><code class="<?= $this->getCodeLanguage(
            $this->response->getHeader('Content-Type')
        ) ?>"><?= \htmlentities($body) ?></code></pre>
        <?php
        return \ob_get_clean(); // @phpstan-ignore-line
    }

    /**
     * @param array<string> $headerLines
     *
     * @return string
     */
    protected function renderHeadersTable(array $headerLines) : string
    {
        \ob_start(); ?>
        <h2>Headers</h2>
        <?php
        if (empty($headerLines)) {
            echo '<p>No headers.</p>';
            return \ob_get_clean(); // @phpstan-ignore-line
        } ?>
        <table>
            <thead>
            <tr>
                <th>Name</th>
                <th>Value</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($headerLines as $line):
                [$name, $value] = \explode(': ', $line, 2); ?>
                <tr>
                    <td><?= \htmlentities($name) ?></td>
                    <td><?= \htmlentities($value) ?></td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
        <?php
        return \ob_get_clean(); // @phpstan-ignore-line
    }

    protected function getCodeLanguage(?string $contentType) : string
    {
        $language = 'none';
        if ($contentType) {
            $contentType = \explode(';', $contentType, 2);
            $language = \explode('/', $contentType[0], 2)[1] ?? $language;
        }
        return 'language-' . $language;
    }
}
