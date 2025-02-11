<?php

/*
 * Copyright (c) 2020. The Nextcloud Bookmarks contributors.
 *
 * This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

namespace OCA\Bookmarks\AppInfo;

use Closure;
use OC\EventDispatcher\SymfonyAdapter;
use OCA\Bookmarks\Activity\ActivityPublisher;
use OCA\Bookmarks\Collaboration\Resources\FolderResourceProvider;
use OCA\Bookmarks\Collaboration\Resources\ResourceProvider;
use OCA\Bookmarks\Dashboard\Frequent;
use OCA\Bookmarks\Dashboard\Recent;
use OCA\Bookmarks\Events\BeforeDeleteEvent;
use OCA\Bookmarks\Events\CreateEvent;
use OCA\Bookmarks\Events\MoveEvent;
use OCA\Bookmarks\Events\UpdateEvent;
use OCA\Bookmarks\Flow\CreateBookmark;
use OCA\Bookmarks\Hooks\BeforeTemplateRenderedListener;
use OCA\Bookmarks\Hooks\UserGroupListener;
use OCA\Bookmarks\Middleware\ExceptionMiddleware;
use OCA\Bookmarks\Search\Provider;
use OCA\Bookmarks\Service\TreeCacheManager;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\Http\Events\BeforeTemplateRenderedEvent;
use OCP\Collaboration\Resources\IProviderManager;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\Group\Events\UserAddedEvent;
use OCP\Group\Events\UserRemovedEvent;
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserSession;
use OCP\User\Events\BeforeUserDeletedEvent;
use OCP\Util;

class Application extends App implements IBootstrap {
	public const APP_ID = 'bookmarks';

	public function __construct() {
		parent::__construct(self::APP_ID);
	}

	public function register(IRegistrationContext $context): void {
		@include_once __DIR__ . '/../../vendor/autoload.php';

		$context->registerService('UserId', static function ($c) {
			/** @var IUser|null $user */
			$user = $c->get(IUserSession::class)->getUser();
			return $user === null ? null : $user->getUID();
		});

		$context->registerService('request', static function ($c) {
			return $c->get(IRequest::class);
		});

		$context->registerSearchProvider(Provider::class);
		$context->registerDashboardWidget(Recent::class);
		$context->registerDashboardWidget(Frequent::class);

		$context->registerEventListener(CreateEvent::class, TreeCacheManager::class);
		$context->registerEventListener(UpdateEvent::class, TreeCacheManager::class);
		$context->registerEventListener(BeforeDeleteEvent::class, TreeCacheManager::class);
		$context->registerEventListener(MoveEvent::class, TreeCacheManager::class);

		$context->registerEventListener(CreateEvent::class, ActivityPublisher::class);
		$context->registerEventListener(UpdateEvent::class, ActivityPublisher::class);
		$context->registerEventListener(BeforeDeleteEvent::class, ActivityPublisher::class);
		$context->registerEventListener(MoveEvent::class, ActivityPublisher::class);

		$context->registerEventListener(BeforeUserDeletedEvent::class, UserGroupListener::class);
		$context->registerEventListener(UserAddedEvent::class, UserGroupListener::class);
		$context->registerEventListener(UserRemovedEvent::class, UserGroupListener::class);

		$context->registerEventListener(BeforeTemplateRenderedEvent::class, BeforeTemplateRenderedListener::class);

		$context->registerMiddleware(ExceptionMiddleware::class);
	}

	/**
	 * @throws \Psr\Container\ContainerExceptionInterface
	 * @throws \Psr\Container\NotFoundExceptionInterface
	 * @throws \Throwable
	 */
	public function boot(IBootContext $context): void {
		$context->injectFn(Closure::fromCallable([$this, 'registerCollaborationResources']));
		$container = $context->getServerContainer();
		CreateBookmark::register($container->get(IEventDispatcher::class));
	}

	protected function registerCollaborationResources(IProviderManager $resourceManager, SymfonyAdapter $symfonyAdapter): void {
		$resourceManager->registerResourceProvider(ResourceProvider::class);
		$resourceManager->registerResourceProvider(FolderResourceProvider::class);

		$symfonyAdapter->addListener('\OCP\Collaboration\Resources::loadAdditionalScripts', static function () {
			Util::addScript('bookmarks', 'bookmarks-collections');
		});
	}
}
