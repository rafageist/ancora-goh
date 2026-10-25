<?php

declare(strict_types=1);

namespace Divengine\Codegen\Generator;

use Divengine\Codegen\Model\Model;
use Divengine\Codegen\Naming\Naming;
use Divengine\Codegen\Profile\Profile;
use Divengine\Codegen\Sql\SqlTypes;
use Divengine\Codegen\Template\Renderer;

/** What every generator needs: the profile, the model, naming, types, the renderer and the timestamp. */
final readonly class Context
{
    public Naming $naming;
    public SqlTypes $types;

    public function __construct(
        public Profile $profile,
        public Model $model,
        public Renderer $renderer,
        public string $updated,
    ) {
        $this->naming = new Naming($profile, $model);
        $this->types = new SqlTypes($profile);
    }

    /**
     * Data shared by all templates.
     *
     * @return array<string, mixed>
     */
    public function common(): array
    {
        return [
            'updated' => $this->updated,
            'project' => $this->profile->name(),
            'rt' => $this->profile->list('runtime'),
        ];
    }
}
