<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateModuleControllers extends Command
{
    protected $signature = 'make:module {module} {controllers*}';
    protected $description = 'Gera controllers automaticamente por módulo (padrão e-SUS)';

    public function handle()
    {
        $module = $this->argument('module');
        $controllers = $this->argument('controllers');

        $basePath = app_path("Http/Controllers/{$module}");

        // 🧱 cria pasta do módulo
        if (!File::exists($basePath)) {
            File::makeDirectory($basePath, 0755, true);
            $this->info("Módulo criado: {$module}");
        }

        foreach ($controllers as $controller) {

            $controllerName = ucfirst($controller) . 'Controller';
            $filePath = "{$basePath}/{$controllerName}.php";

            if (File::exists($filePath)) {
                $this->warn("Já existe: {$controllerName}");
                continue;
            }

            $content = $this->generateController($module, $controllerName);

            File::put($filePath, $content);

            $this->info("Criado: {$controllerName}");
        }

        return Command::SUCCESS;
    }

    private function generateController($module, $name)
    {
        return <<<PHP
<?php

namespace App\Http\Controllers\\{$module};

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class {$name} extends Controller
{
    public function index()
    {
        return response()->json(['module' => '{$module}', 'controller' => '{$name}', 'action' => 'index']);
    }

    public function store(Request \$request)
    {
        return response()->json(['message' => 'created']);
    }

    public function show(\$id)
    {
        return response()->json(['id' => \$id]);
    }

    public function update(Request \$request, \$id)
    {
        return response()->json(['message' => 'updated', 'id' => \$id]);
    }

    public function destroy(\$id)
    {
        return response()->json(['message' => 'deleted', 'id' => \$id]);
    }
}
PHP;
    }
}