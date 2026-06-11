<?php

declare(strict_types=1);

namespace Tests;

use DI\Container;
use DI\ContainerBuilder;
use Integrations\Http\Request;
use Integrations\Http\Response;
use Integrations\Registry;
use Odan\Session\MemorySession;
use Odan\Session\SessionInterface;
use Odan\Session\SessionManagerInterface;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\App;
use Slim\Factory\AppFactory;

use function Rcalicdan\ConfigLoader\config;

abstract class TestCase extends BaseTestCase
{
    protected App $app;

    /**
     * Use DI\Container instead of ContainerInterface to allow set() calls.
     */
    protected Container $container;

    protected function setUp(): void
    {
        parent::setUp();

        $containerBuilder = new ContainerBuilder();

        $containerBuilder->useAutowiring(config('container.settings.autowire', true));
        $containerBuilder->useAttributes(config('container.settings.use_attributes', true));
        $containerBuilder->addDefinitions(config('container.dependency_map', []));

        $containerBuilder->addDefinitions([
            MemorySession::class => fn () => new MemorySession(),

            SessionManagerInterface::class => fn (ContainerInterface $c) => $c->get(MemorySession::class),

            SessionInterface::class => fn (ContainerInterface $c) => $c->get(MemorySession::class),
        ]);

        $this->container = $containerBuilder->build();
        Registry::set($this->container);
        AppFactory::setContainer($this->container);

        $responseFactory = $this->container->get(ResponseFactoryInterface::class);
        $this->app = AppFactory::create($responseFactory);

        $this->container->set(App::class, $this->app);

        $this->container->get(\Integrations\View\BladeRenderer::class);

        (require __DIR__ . '/../config/middleware.php')($this->app);
        (require __DIR__ . '/../config/routes.php')($this->app);
    }

    /**
     * Simulate an HTTP Request to the Slim application.
     */
    protected function request(string $method, string $path, array $data = []): Response
    {
        $request = Request::createTestRequest($method, $path);

        if (! empty($data)) {
            $request = $request->withParsedBody($data);
        }

        /** @var Response $response */
        $response = $this->app->handle($request);

        return $response;
    }

    /**
     * Helper for GET requests.
     */
    protected function get(string $path): Response
    {
        return $this->request('GET', $path);
    }

    /**
     * Helper for POST requests.
     */
    protected function post(string $path, array $data = []): Response
    {
        $session = $this->container->get(SessionInterface::class);
        $session->set('_token', 'test-token');
        $data['_token'] = 'test-token';

        return $this->request('POST', $path, $data);
    }

    /**
     * Helper for PUT requests.
     */
    protected function put(string $path, array $data = []): Response
    {
        $session = $this->container->get(SessionInterface::class);
        $session->set('_token', 'test-token');
        $data['_token'] = 'test-token';

        return $this->request('PUT', $path, $data);
    }

    /**
     * Helper for PATCH requests.
     */
    protected function patch(string $path, array $data = []): Response
    {
        $session = $this->container->get(SessionInterface::class);
        $session->set('_token', 'test-token');
        $data['_token'] = 'test-token';

        return $this->request('PATCH', $path, $data);
    }

    /**
     * Helper for DELETE requests.
     */
    protected function delete(string $path, array $data = []): Response
    {
        $session = $this->container->get(SessionInterface::class);
        $session->set('_token', 'test-token');
        $data['_token'] = 'test-token';

        return $this->request('DELETE', $path, $data);
    }

    /**
     * Helper for OPTIONS requests.
     */
    protected function options(string $path, array $data = []): Response
    {
        return $this->request('OPTIONS', $path, $data);
    }
}
