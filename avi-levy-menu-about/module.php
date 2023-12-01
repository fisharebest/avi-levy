<?php

namespace Fisharebest\AviLevy;

use Fisharebest\Webtrees\Http\ViewResponseTrait;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Menu;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Module\ModuleCustomInterface;
use Fisharebest\Webtrees\Module\ModuleCustomTrait;
use Fisharebest\Webtrees\Module\ModuleMenuInterface;
use Fisharebest\Webtrees\Module\ModuleMenuTrait;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Validator;
use Fisharebest\Webtrees\View;
use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function route;

return new class ()
    extends AbstractModule
    implements ModuleMenuInterface, ModuleCustomInterface, RequestHandlerInterface {
    use ModuleCustomTrait;
    use ModuleMenuTrait;
    use ViewResponseTrait;

    #[Override]
    public function title(): string
    {
        return 'Avi Levy - Menu - About';
    }

    #[Override]
    public function resourcesFolder(): string
    {
        return __DIR__ . '/resources/';
    }

    #[Override]
    public function boot(): void
    {
        View::registerNamespace(namespace: $this->name(), path: $this->resourcesFolder() . '/views/');

        Registry::routeFactory()->routeMap()->get(name: self::class, path: '/tree/{tree}/about', handler: $this);
    }

    #[Override]
    public function getMenu(Tree $tree): ?Menu
    {
        $label = I18N::translate('About');
        $link  = route(route_name: self::class, parameters: ['tree' => $tree->name()]);

        return new Menu(label: $label, link: $link);
    }

    #[Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $tree = Validator::attributes($request)->treeOptional();
        if ($tree instanceof Tree) {
            $yacov_url = Registry::individualFactory()->make(xref: 'I6', tree: $tree)->url();
        } else {
            $yacov_url = '#';
        }

        $view_data = [
            'title'     => I18N::translate('About'),
            'tree'      => $tree,
            'yacov_url' => $yacov_url,
        ];

        return $this->viewResponse(view_name: $this->name() . '::about', view_data: $view_data);
    }
};
