<?php
declare(strict_types=1);

namespace CompanyHub;

class Router
{
    /** @var array<int,array{method:string,pattern:string,handler:array{0:string,1:string}}> */
    private array $routes = [];

    /**
     * @param array{0:string,1:string} $handler [ControllerFqcn, method]
     */
    public function add(string $method, string $pattern, array $handler): void
    {
        $this->routes[] = [
            'method'  => strtoupper($method),
            'pattern' => $pattern,
            'handler' => $handler,
        ];
    }

    public function get(string $pattern, array $handler): void    { $this->add('GET',  $pattern, $handler); }
    public function post(string $pattern, array $handler): void   { $this->add('POST', $pattern, $handler); }

    public function dispatch(string $method, string $path): void
    {
        $method = strtoupper($method);
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            $regex = '#^' . preg_replace('#\{(\w+)\}#', '(?P<\1>[^/]+)', $route['pattern']) . '$#';
            if (preg_match($regex, $path, $m)) {
                $params = array_filter($m, 'is_string', ARRAY_FILTER_USE_KEY);
                [$class, $action] = $route['handler'];
                $instance = new $class();
                $instance->{$action}($params);
                return;
            }
        }
        http_response_code(404);
        echo '<h1>404 Not Found</h1>';
    }
}
