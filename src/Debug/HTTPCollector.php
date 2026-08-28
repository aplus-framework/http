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

use Closure;
use Framework\Debug\Collector;
use Framework\Debug\Debugger as D;
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
        if (!isset($this->request)) {
            return '<p>A Request instance has not been set on this collector.</p>';
        }
        \ob_start();
        $ipKey = $this->request->getIpKey();
        if ($ipKey instanceof Closure) {
            $ipKey = Closure::class;
        }
        ?>
        <p>
            <strong>IP:</strong> <?= $this->request->getIp() ?>
            <span class="text-opaque">(<?= $ipKey ?>)</span>
        </p>
        <p><strong>Is Secure:</strong> <?= $this->request->isSecure() ? 'Yes' : 'No' ?></p>
        <p><strong>Protocol:</strong> <?= D::esc($this->request->getProtocol()) ?></p>
        <p><strong>Method:</strong> <?= D::esc($this->request->getMethod()) ?></p>
        <p><strong>URL:</strong> <?= D::esc($this->request->getUrl()->toString()) ?></p>
        <p><strong>Server:</strong> <?= D::esc($this->request->getServer('SERVER_SOFTWARE')) ?></p>
        <p><strong>Hostname:</strong> <?= D::esc(\gethostname()) ?></p>
        <?php
        $allowedHosts = $this->request->getAllowedHosts();
        $allowedHosts = empty($allowedHosts) ? '*' : \implode(', ', $allowedHosts);
        ?>
        <p><strong>Allowed Hosts:</strong> <?= D::esc($allowedHosts) ?></p>
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
                <td><?= D::esc($userAgent->getType()) ?></td>
                <td><?= D::esc($userAgent->getName()) ?></td>
                <td><?= $userAgent->isBrowser()
                        ? D::esc($userAgent->getBrowserVersion())
                        : '' ?></td>
                <td><?= D::esc($userAgent->getPlatform()) ?></td>
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
        ) ?>"><?= D::esc($body) ?></code></pre>
        <?php
        return \ob_get_clean(); // @phpstan-ignore-line
    }

    protected function renderRequestForm() : string
    {
        if (!$this->request->isPost() && !$this->request->isFormUrlEncoded()) {
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
                    <td><?= D::esc($field) ?></td>
                    <td>
                        <pre><?= D::esc($value) ?></pre>
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
        if (!$this->request->hasFiles()) {
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
                    <td><?= D::esc($field) ?></td>
                    <td><?= D::esc($file->getName()) ?></td>
                    <td><?= D::esc($file->getFullPath()) ?></td>
                    <td><?= D::esc($file->getType()) ?></td>
                    <td><?= D::esc($file->getClientType()) ?></td>
                    <td><?= D::esc($file->getExtension()) ?></td>
                    <td><?= D::convertSize($file->getSize()) ?></td>
                    <td><?= $file->getDestination() ?></td>
                    <td><?= $file->getError() ?></td>
                    <td><?= D::esc($file->getErrorMessage()) ?></td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
        <?php
        return \ob_get_clean(); // @phpstan-ignore-line
    }

    protected function renderResponse() : string
    {
        if (!isset($this->response)) {
            return '<p>A Response instance has not been set on this collector.</p>';
        }
        \ob_start(); ?>
        <p><strong>Protocol:</strong> <?= D::esc($this->response->getProtocol()) ?></p>
        <p><strong>Status:</strong> <?= D::esc($this->response->getStatus()) ?></p>
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
                <strong>Time Sending:</strong> <?= D::roundSecondsToMilliseconds($info['end'] - $info['start']) ?> ms
            </p>
        <?php
        endif;
        echo $this->renderHeadersTable($this->response->getHeaderLines());
        if ($this->response->isReplacingHeaders()) {
            echo '<p><small>* Note that the Response is replacing headers.</small></p>';
        }
        echo '<p><small>* Note that some headers can be set outside the Response';
        echo ' class, for example by the session or the server.';
        echo ' So they don\'t appear here.</small></p>';
        echo $this->renderResponseCookies();
        echo $this->renderResponseBody();
        return \ob_get_clean(); // @phpstan-ignore-line
    }

    protected function renderResponseCookies() : string
    {
        if (!$this->response->getCookies()) {
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
                <th>Is Partitioned</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($this->response->getCookies() as $cookie): ?>
                <tr>
                    <td><?= D::esc($cookie->getName()) ?></td>
                    <td><?= D::esc($cookie->getValue()) ?></td>
                    <td><?= $cookie->getExpires()?->format('D, d M Y H:i:s \G\M\T') ?></td>
                    <td><?= D::esc($cookie->getPath()) ?></td>
                    <td><?= D::esc($cookie->getDomain()) ?></td>
                    <td><?= $cookie->isSecure() ? 'Yes' : 'No' ?></td>
                    <td><?= $cookie->isHttpOnly() ? 'Yes' : 'No' ?></td>
                    <td><?= D::esc($cookie->getSameSite()) ?></td>
                    <td><?= $cookie->isPartitioned() ? 'Yes' : 'No' ?></td>
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
        if (!$this->response->isSent()) {
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
        ) ?>"><?= D::esc($body) ?></code></pre>
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
            return \ob_get_clean();
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
                    <td><?= D::esc($name) ?></td>
                    <td><?= D::esc($value) ?></td>
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
