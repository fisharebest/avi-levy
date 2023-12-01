<?php

namespace Fisharebest\AviLevy;

use Fig\Http\Message\StatusCodeInterface;
use Fisharebest\Webtrees\Module\ModuleCustomInterface;
use Fisharebest\Webtrees\Module\ModuleCustomTrait;
use Fisharebest\Webtrees\Module\ModuleThemeTrait;
use Fisharebest\Webtrees\Module\PedigreeChartModule;
use Fisharebest\Webtrees\Module\XeneaTheme;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Services\ChartService;
use Fisharebest\Webtrees\Validator;
use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function file_get_contents;

return new class (new PedigreeChartModule(new ChartService()))
    extends XeneaTheme
    implements ModuleCustomInterface, RequestHandlerInterface {
    use ModuleCustomTrait;
    use ModuleThemeTrait;

    public function __construct(private readonly PedigreeChartModule $pedigree_chart_module)
    {
    }

    #[Override]
    public function title(): string
    {
        return 'Avi Levy - Theme';
    }

    #[Override]
    public function resourcesFolder(): string
    {
        return __DIR__ . '/resources/';
    }

    #[Override]
    public function boot(): void
    {
        Registry::routeFactory()->routeMap()->get(name: 'avi-levy', path: '/avi-levy/{item}', handler: $this);
    }

    /**
     * A list of CSS files to include for this page.
     *
     * @return array<string>
     */
    #[Override]
    public function stylesheets(): array
    {
        $stylesheets   = parent::stylesheets();
        $stylesheets[] = $this->assetUrl(asset: 'css/theme.css');

        return $stylesheets;
    }

    #[Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $item = Validator::attributes(request: $request)->string(parameter: 'item');

        return match ($item) {
            'background'   => $this->fileResponse(path: '/images/background.jpeg')->withHeader(header: 'content-type', value: 'image/jpeg'),
            'silhouette-f' => $this->fileResponse(path: '/images/silhouette-f.png')->withHeader(header: 'content-type', value: 'image/png'),
            'silhouette-m' => $this->fileResponse(path: '/images/silhouette-m.png')->withHeader(header: 'content-type', value: 'image/png'),
            'silhouette-u' => $this->fileResponse(path: '/images/silhouette-u.png')->withHeader(header: 'content-type', value: 'image/png'),
            'favicon'      => $this->fileResponse(path: '/favicon.ico')->withHeader(header: 'content-type', value: 'image/x-icon'),
            default        => Registry::responseFactory()->response(code: StatusCodeInterface::STATUS_NOT_FOUND),
        };
    }

    private function fileResponse(string $path): ResponseInterface
    {
        $file = file_get_contents(filename: $this->resourcesFolder() . $path);

        if ($file === false) {
            Registry::responseFactory()->response(code: StatusCodeInterface::STATUS_NOT_FOUND);
        }

        return Registry::responseFactory()->response(
            content: $file,
        );
    }
};
