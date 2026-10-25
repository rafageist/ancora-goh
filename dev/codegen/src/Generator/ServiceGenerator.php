<?php

declare(strict_types=1);

namespace Divengine\Codegen\Generator;

use Divengine\Codegen\Model\Service;
use Divengine\Codegen\Sql\ServiceSql;

/**
 * Service classes (the historical cogephpservice.php), one per service
 * model. Only prepares data; the code is in templates/services/Service.php.tpl.
 */
final class ServiceGenerator
{
    /**
     * @param list<Service> $services
     */
    public function __construct(private readonly Context $ctx, private readonly array $services)
    {
    }

    /**
     * @return list<GeneratedFile>
     */
    public function generate(): array
    {
        $files = [];
        $selector = (string) $this->ctx->profile->get('package_selectors.services', '');
        foreach ($this->services as $service) {
            $methods = [];
            foreach ($service->methods as $method) {
                $sql = ServiceSql::parameterize($method);
                $methods[] = [
                    'method' => $method->name,
                    'descrip' => $method->description,
                    'params' => $sql['params'],
                    // the SQL as the body of a PHP double-quoted string
                    'sqlLiteral' => addcslashes($sql['sql'], '"\\$'),
                ];
            }
            $class = $service->name . 'Service';
            $path = $this->ctx->naming->folder($class, $selector) . "/$class.php";
            $files[] = new GeneratedFile($path, $this->ctx->renderer->render('services/Service.php.tpl', $this->ctx->common() + [
                'name' => $service->name,
                'description' => $service->description,
                'methods' => $methods,
            ]));
        }

        return $files;
    }
}
