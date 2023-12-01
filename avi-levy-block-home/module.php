<?php

namespace Fisharebest\AviLevy;

use Fig\Http\Message\StatusCodeInterface;
use Fisharebest\Webtrees\Http\Exceptions\HttpNotFoundException;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Module\ModuleBlockInterface;
use Fisharebest\Webtrees\Module\ModuleBlockTrait;
use Fisharebest\Webtrees\Module\ModuleCustomInterface;
use Fisharebest\Webtrees\Module\ModuleCustomTrait;
use Fisharebest\Webtrees\Module\ModuleThemeTrait;
use Fisharebest\Webtrees\Module\PedigreeChartModule;
use Fisharebest\Webtrees\Module\XeneaTheme;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Services\ChartService;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Validator;
use Fisharebest\Webtrees\View;
use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

use function file_get_contents;
use function view;

return new class (new PedigreeChartModule(new ChartService()))
    extends AbstractModule
    implements ModuleBlockInterface, ModuleCustomInterface, RequestHandlerInterface {
    use ModuleBlockTrait;
    use ModuleCustomTrait;
    use ModuleThemeTrait;

    public function __construct(private readonly PedigreeChartModule $pedigree_chart_module)
    {
    }

    #[Override]
    public function title(): string
    {
        return 'Avi Levy - Block - Home page';
    }

    #[Override]
    public function resourcesFolder(): string
    {
        return __DIR__ . '/resources/';
    }

    #[Override]
    public function boot(): void
    {
        View::registerNamespace(namespace: 'avi-levy', path: $this->resourcesFolder() . '/views/');

        Registry::routeFactory()->routeMap()->get(name: 'avi-levy-photo', path: '/avi-levy-photo', handler: $this);
        Registry::routeFactory()->routeMap()->get(name: 'avi-levy-text', path: '/avi-levy-text', handler: $this);
    }

    #[Override]
    public function getBlock(Tree $tree, int $block_id, string $context, array $config = []): string
    {
        $factory    = Registry::individualFactory();
        $parameters = ['generations' => '3', 'style' => 'up'];

        try {
            return view(name: 'avi-levy::avi-levy-block', data: [
                'avi_url'      => $this->pedigree_chart_module->chartUrl(individual: $factory->make(xref: 'I11', tree: $tree), parameters: $parameters),
                'yacov_url'    => $this->pedigree_chart_module->chartUrl(individual: $factory->make(xref: 'I6', tree: $tree), parameters: $parameters),
                'shoshana_url' => $this->pedigree_chart_module->chartUrl(individual: $factory->make(xref: 'I10', tree: $tree), parameters: $parameters),
            ]);
        } catch (Throwable $ex) {
            return 'This block can only be used on the avi-levy tree.' . $ex->getMessage();
        }
    }

    #[Override]
    public function isTreeBlock(): bool
    {
        return true;
    }

    #[Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $route = Validator::attributes($request)->route();

        return match ($route->name) {
            'avi-levy-photo' => $this->fileResponse(path: '/images/photo.png')->withHeader(header: 'content-type', value: 'image/png'),
            'avi-levy-text'  => $this->fileResponse(path: '/images/text.png')->withHeader(header: 'content-type', value: 'image/png'),
            default          => Registry::responseFactory()->response(code: StatusCodeInterface::STATUS_NOT_FOUND),
        };
    }

    private function fileResponse(string $path): ResponseInterface
    {
        $file = file_get_contents(filename: $this->resourcesFolder() . $path);

        if ($file === false) {
            Registry::responseFactory()->response(code: StatusCodeInterface::STATUS_NOT_FOUND);
        }

        return Registry::responseFactory()->response(content: $file);
    }
};
