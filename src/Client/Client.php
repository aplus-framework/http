<?php namespace Framework\HTTP\Client;

class Client
{
	protected $defaultOptions = [
		\CURLOPT_CONNECTTIMEOUT => 10,
		\CURLOPT_TIMEOUT => 60,
		\CURLOPT_FOLLOWLOCATION => true,
		\CURLOPT_MAXREDIRS => 1,
		\CURLOPT_AUTOREFERER => true,
	];
	protected $options = [];
	protected $responseProtocol;
	protected $responseCode;
	protected $responseReason;
	protected $responseHeaders = [];

	public function setOption($option, $value)
	{
		$this->options[$option] = $value;
		return $this;
	}

	public function getOptions() : array
	{
		return \array_replace($this->defaultOptions, $this->options);
	}

	public function setBasicAuth(string $username, string $password)
	{
		$this->setOption(\CURLOPT_USERPWD, $username . ':' . $password);
		$this->setOption(\CURLOPT_HTTPAUTH, \CURLAUTH_BASIC);
		return $this;
	}

	public function setResponseTimeout(int $timeout)
	{
		$this->setOption(\CURLOPT_TIMEOUT, $timeout);
		return $this;
	}

	public function setRequestTimeout(int $timeout)
	{
		$this->setOption(\CURLOPT_CONNECTTIMEOUT, $timeout);
		return $this;
	}

	public function reset() : void
	{
		$this->options = [];
		$this->responseHeaders = [];
	}

	protected function setHTTPVersion(string $version)
	{
		if ($version === 'HTTP/1.0') {
			return $this->setOption(\CURLOPT_HTTP_VERSION, \CURL_HTTP_VERSION_1_0);
		}
		if ($version === 'HTTP/1.1') {
			return $this->setOption(\CURLOPT_HTTP_VERSION, \CURL_HTTP_VERSION_1_1);
		}
	}

	public function run(Request $request) : Response
	{
		$this->setHTTPVersion($request->getProtocol());
		switch ($request->getMethod()) {
			case 'POST':
				$this->setOption(\CURLOPT_POST, true);
				// TODO: Has files? So must be array
				$this->setOption(\CURLOPT_POSTFIELDS, $request->getBody());
				break;
			case 'PUT':
			case 'PATCH':
			case 'DELETE':
				$this->setOption(\CURLOPT_POSTFIELDS, $request->getBody());
				break;
		}
		$this->setOption(\CURLOPT_CUSTOMREQUEST, $request->getMethod());
		$this->setOption(\CURLOPT_RETURNTRANSFER, true);
		$this->setOption(\CURLOPT_HEADER, false);
		$this->setOption(\CURLOPT_URL, $request->getURL()->getURL());
		$headers = [];
		foreach ($request->getAllHeaders() as $name => $values) {
			foreach ($values as $value) {
				$headers[] = $name . ': ' . $value;
			}
		}
		$this->setOption(\CURLOPT_HTTPHEADER, $headers);
		$this->setOption(\CURLOPT_HEADERFUNCTION, [$this, 'parseHeaderLine']);
		$curl = \curl_init();
		\curl_setopt_array($curl, $this->getOptions());
		//curl_setopt($curl, CURLINFO_HEADER_OUT, true);
		$body = \curl_exec($curl);
		if ($body === false) {
			throw new \RuntimeException(\curl_error($curl), \curl_errno($curl));
		}
		//\var_dump(\curl_getinfo($curl, CURLINFO_HEADER_OUT));
		//$status_code = \curl_getinfo($curl, \CURLINFO_HTTP_CODE);
		\curl_close($curl);
		return new Response(
			$this->responseProtocol,
			$this->responseCode,
			$this->responseReason,
			$this->responseHeaders,
			$body
		);
	}

	/**
	 * Parses Header line.
	 *
	 * @param resource $curl
	 * @param string   $line
	 *
	 * @return int
	 */
	protected function parseHeaderLine($curl, string $line) : int
	{
		$trimmed_line = \trim($line);
		if ($trimmed_line === '') {
			return \strlen($line);
		}
		if (\strpos($trimmed_line, ':') === false) {
			if (\strpos($trimmed_line, 'HTTP/') === 0) {
				[
					$this->responseProtocol,
					$this->responseCode,
					$this->responseReason,
				] = \explode(' ', $trimmed_line, 3);
			}
			return \strlen($line);
		}
		[$name, $value] = \explode(':', $trimmed_line, 2);
		$name = \trim($name);
		$value = \trim($value);
		if ($name !== '' && $value !== '') {
			$this->responseHeaders[\strtolower($name)][] = $value;
		}
		return \strlen($line);
	}
}
