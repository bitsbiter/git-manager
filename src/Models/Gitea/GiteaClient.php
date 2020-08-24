<?php

namespace ThisIsDevelopment\GitManager\Models\Gitea;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\RequestOptions;
use ThisIsDevelopment\GitManager\Exceptions\GitException;

class GiteaClient
{
    protected ClientInterface $httpClient;
    protected string $token = '';
    protected string $url = '';
    protected ?string $sudo = null;

    protected function call($method, $url, $body = null)
    {
        if (!$this->httpClient) {
            $this->httpClient = new Client([
                'base_uri' => $this->url,
                'headers'  => [
                    'Accept'        => 'application/json',
                    'Authorization' => "token {$this->token}"
                ]
            ]);
        }

        $options = [];

        if ($body) {
            $options[RequestOptions::JSON] = $body;
        }

        if ($this->sudo) {
            $options['headers']['Sudo'] = $this->sudo;
        }

        try {
            $res = $this->httpClient->request($method, '/api/v1/' . ltrim($url, '/'), $options);
            if ($res->getStatusCode() === 204) {
                return null;
            }

            return json_decode(
                $res->getBody()->getContents(),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (\Throwable $e) {
            throw new GitException(
                "Unable to complete {$method} request to {$url}: {$e->getMessage()}",
                $e->getCode(),
                $e
            );
        }
    }

    public function setUrl($url): self
    {
        $this->url = $url;
        return $this;
    }

    public function authenticate($token): self
    {
        $this->token = $token;
        return $this;
    }

    public function getAll($class, $url, $parent)
    {
        return array_map(fn($data) => new $class($this, $parent, $data), $this->call('GET', $url));
    }

    public function getFirst($class, $url, $parent)
    {
        return array_map(fn($data) => new $class($this, $parent, $data), $this->call('GET', $url)['data']);
    }

    public function get($class, $url, $parent)
    {
        $res = $this->call('GET', $url);
        return new $class($this, $parent, $res);
    }

    public function post($class, $url, $parent, $properties)
    {
        $res = $this->call('POST', $url, $properties);
        return new $class($this, $parent, $res);
    }

    public function delete($url): void
    {
        $this->call('DELETE', $url);
    }

    public function put($url): void
    {
        $this->call('PUT', $url);
    }

    public function patch($url, array $params = []): void
    {
        $this->call('PATCH', $url, $params);
    }

    public function sudo(?string $username): self
    {
        $this->sudo = $username;
        return $this;
    }
}
