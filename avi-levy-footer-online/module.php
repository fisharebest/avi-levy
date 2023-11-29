<?php

namespace Fisharebest\AviLevy;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Module\ModuleCustomInterface;
use Fisharebest\Webtrees\Module\ModuleCustomTrait;
use Fisharebest\Webtrees\Module\ModuleFooterInterface;
use Fisharebest\Webtrees\Module\ModuleFooterTrait;
use Fisharebest\Webtrees\Services\UserService;
use Fisharebest\Webtrees\User;
use Override;
use Psr\Http\Message\ServerRequestInterface;

return new class (new UserService()) extends AbstractModule implements ModuleFooterInterface, ModuleCustomInterface {
    use ModuleFooterTrait;
    use ModuleCustomTrait;

    public function __construct(private readonly UserService $user_service) {}

    #[Override]
    public function title(): string
    {
        return 'Avi Levy - Footer - Who is online';
    }

    #[Override]
    public function description(): string
    {
        return 'Modifications for Avi Levy - show logged in users';
    }

    #[Override]
    public function getFooter(ServerRequestInterface $request): string
    {
        if (Auth::check()) {
            $users = $this->user_service->allLoggedIn();
            $label = I18N::translate(message: 'Who is online') . ':';
            $count = I18N::plural('%s signed-in user', '%s signed-in users', $users->count(), I18N::number(n: $users->count()));
            $list  = $users->map(callback: static fn(User $user): string => ' | ' . e(value: $user->realName()) . ' - ' . e(value: $user->userName()));

            return '<div class="wt-footer text-center my-2"><b>' . $label . '</b> ' . $count . ' ' . $list->implode(value: '') . '</div>';
        }

        return '';
    }
};
